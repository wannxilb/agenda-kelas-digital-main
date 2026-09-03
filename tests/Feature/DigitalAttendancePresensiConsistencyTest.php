<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\DailyAttendanceSetting;
use App\Models\Institution;
use App\Models\Setting;
use App\Models\StudentDailyAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DigitalAttendancePresensiConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private User $waliKelas;
    private Classes $class;
    private int $institutionId;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-08-03 07:15:00'));

        $institution = Institution::create(['name' => 'SMK Test', 'status' => 'active']);
        $this->institutionId = $institution->id;

        AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
            'institution_id' => $this->institutionId,
        ]);

        Role::create(['name' => 'siswa']);
        Role::create(['name' => 'wali_kelas']);

        $this->waliKelas = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $this->waliKelas->assignRole('wali_kelas');

        $this->class = Classes::create([
            'name' => 'X RPL 1',
            'major' => 'RPL',
            'grade_level' => 'X',
            'academic_year' => '2026/2027',
            'homeroom_teacher_id' => $this->waliKelas->id,
            'capacity' => 30,
            'is_active' => true,
            'institution_id' => $this->institutionId,
        ]);

        $this->student = User::factory()->create([
            'institution_id' => $this->institutionId,
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);
        $this->student->assignRole('siswa');

        Setting::set('school_location_enabled', '1', 'general', $this->institutionId);
        Setting::set('school_location_latitude', '-6.200000', 'general', $this->institutionId);
        Setting::set('school_location_longitude', '106.816666', 'general', $this->institutionId);
        Setting::set('school_location_radius_meters', '100', 'general', $this->institutionId);

        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'check_in_start' => '05:00:00',
            'check_in_deadline' => '06:30:00',
            'check_in_verification_deadline' => '08:00:00',
            'require_check_in_photo' => false,
            'require_check_out_photo' => false,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_digital_late_check_in_syncs_to_presensi_late(): void
    {
        $this->actingAs($this->student)
            ->post(route('siswa.daily-attendance.check-in'), $this->locationPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'check_in_status' => 'late',
        ]);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'status' => 'late',
            'check_in_time' => '07:15:00',
        ]);
    }

    public function test_student_presensi_page_uses_digital_attendance_when_manual_presensi_is_missing(): void
    {
        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => '2026-08-03 07:15:00',
            'check_in_status' => 'late',
        ]);

        $this->assertDatabaseMissing('attendances', ['student_id' => $this->student->id]);

        $response = $this->actingAs($this->student)
            ->get(route('siswa.attendance.index', ['month' => '2026-08']));

        $response->assertOk();
        $response->assertViewHas('today_attendance', fn ($attendance) => $attendance?->status === 'late');
        $response->assertViewHas('stats', fn (array $stats) => $stats['late'] === 1 && $stats['present'] === 0);
    }

    public function test_wali_kelas_dashboard_uses_digital_attendance_when_final_presensi_is_missing(): void
    {
        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => '2026-08-03 07:15:00',
            'check_in_status' => 'late',
        ]);

        $this->assertDatabaseMissing('attendances', ['student_id' => $this->student->id]);

        $response = $this->actingAs($this->waliKelas)->get(route('wali-kelas.dashboard'));

        $response->assertOk();
        $response->assertViewHas('lateCount', 1);
        $response->assertViewHas('attendanceRate', 100.0);
    }

    public function test_final_presensi_wins_when_manual_presensi_has_different_status(): void
    {
        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => '2026-08-03 07:15:00',
            'check_in_status' => 'late',
        ]);

        Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'status' => 'present',
            'check_in_time' => '07:15:00',
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('siswa.attendance.index', ['month' => '2026-08']));

        $response->assertOk();
        $response->assertViewHas('today_attendance', fn ($attendance) => $attendance?->status === 'present');
        $response->assertViewHas('stats', fn (array $stats) => $stats['late'] === 0 && $stats['present'] === 1);
    }

    public function test_digital_sync_does_not_override_existing_final_presensi(): void
    {
        Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'status' => 'present',
            'check_in_time' => '06:45:00',
        ]);

        $this->actingAs($this->student)
            ->post(route('siswa.daily-attendance.check-in'), $this->locationPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'check_in_status' => 'late',
        ]);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'status' => 'present',
            'check_in_time' => '06:45:00',
        ]);
    }

    private function locationPayload(): array
    {
        return [
            'latitude' => '-6.200100',
            'longitude' => '106.816666',
            'accuracy' => 12,
            'device_fingerprint' => 'device-test-123',
        ];
    }
}
