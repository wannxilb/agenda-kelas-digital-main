<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\DailyAttendanceSetting;
use App\Models\Institution;
use App\Models\Setting;
use App\Models\StudentDailyAttendance;
use App\Models\StudentDailyAttendanceCorrection;
use App\Models\User;
use App\Services\AttendanceStatusResolver;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $waliKelas;
    protected User $admin;
    protected Classes $class;
    protected int $institutionId;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-08-05 09:00:00'));

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
        Role::create(['name' => 'admin']);

        $this->waliKelas = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $this->waliKelas->assignRole('wali_kelas');

        $this->admin = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $this->admin->assignRole('admin');

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

        $setting = DailyAttendanceSetting::forInstitution($this->institutionId);
        $setting->update([
            'check_in_deadline' => '10:00:00',
            'require_check_in_photo' => false,
            'require_check_out_photo' => false,
        ]);
    }

    private function checkInPayload(array $overrides = []): array
    {
        return array_merge([
            'latitude' => '-6.200100',
            'longitude' => '106.816666',
            'accuracy' => 12,
            'device_fingerprint' => 'device-test-123',
        ], $overrides);
    }

    private function checkIn(): StudentDailyAttendance
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        return StudentDailyAttendance::where('student_id', $this->student->id)
            ->whereDate('date', '2026-08-05')
            ->firstOrFail();
    }

    private function requestCorrection(StudentDailyAttendance $attendance, string $event, string $requestedStatus): StudentDailyAttendanceCorrection
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.corrections.store', $attendance), [
            'target_event' => $event,
            'requested_status' => $requestedStatus,
            'reason' => 'Bukti dari guru / izin.',
        ])->assertSessionHas('success');

        return StudentDailyAttendanceCorrection::where('student_daily_attendance_id', $attendance->id)
            ->where('target_event', $event)
            ->firstOrFail();
    }

    public function test_approved_check_in_correction_changes_check_in_status(): void
    {
        $attendance = $this->checkIn();
        $this->assertEquals('on_time', $attendance->check_in_status);

        $correction = $this->requestCorrection($attendance, 'check_in', 'sakit');

        $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.corrections.review', $correction), [
            'decision' => 'approve',
        ])->assertSessionHas('success');

        $attendance->refresh();
        $this->assertEquals('sick', $attendance->check_in_status);
        $this->assertDatabaseHas('student_daily_attendance_corrections', [
            'id' => $correction->id,
            'status' => 'approved',
        ]);
    }

    public function test_rejected_correction_preserves_original_status(): void
    {
        $attendance = $this->checkIn();
        $correction = $this->requestCorrection($attendance, 'check_in', 'izin_lainnya');

        $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.corrections.review', $correction), [
            'decision' => 'reject',
            'reviewer_note' => 'Tidak didukung bukti.',
        ])->assertSessionHas('success');

        $attendance->refresh();
        $this->assertEquals('on_time', $attendance->check_in_status);
        $this->assertDatabaseHas('student_daily_attendance_corrections', [
            'id' => $correction->id,
            'status' => 'rejected',
        ]);
    }

    public function test_approved_correction_is_reflected_in_resolver_summary(): void
    {
        $attendance = $this->checkIn();
        $correction = $this->requestCorrection($attendance, 'check_in', 'sakit');

        $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.corrections.review', $correction), [
            'decision' => 'approve',
        ])->assertSessionHas('success');

        $resolved = app(AttendanceStatusResolver::class)->resolve($this->student->id, '2026-08-05');
        $this->assertEquals('sick', $resolved);
    }

    public function test_approved_check_out_correction_by_admin_changes_check_out_status(): void
    {
        $attendance = $this->checkIn();

        Carbon::setTestNow(Carbon::parse('2026-08-05 15:00:00'));

        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-out'), $this->checkInPayload())
            ->assertSessionHas('success');

        $attendance->refresh();
        $this->assertEquals('checked_out', $attendance->check_out_status);

        $correction = $this->requestCorrection($attendance, 'check_out', 'izin_kegiatan');

        $this->actingAs($this->admin)->post(route('admin.daily-attendance.corrections.review', $correction), [
            'decision' => 'approve',
        ])->assertSessionHas('success');

        $attendance->refresh();
        $this->assertEquals('additional_activity', $attendance->check_out_status);
    }
}
