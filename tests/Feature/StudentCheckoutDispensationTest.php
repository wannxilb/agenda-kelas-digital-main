<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\DailyAttendanceSetting;
use App\Models\Institution;
use App\Models\Setting;
use App\Models\StudentDailyAttendance;
use App\Models\StudentEarlyLeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentCheckoutDispensationTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $waliKelas;
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

    private function farLocationPayload(): array
    {
        return ['latitude' => '-6.400000', 'longitude' => '106.816666'];
    }

    public function test_approving_multi_day_dispen_writes_checkout_on_today_not_start_date(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $request = StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'date_end' => '2026-08-06',
            'category' => 'lomba',
            'reason' => 'Lomba provinsi',
            'status' => 'pending',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-08-05 15:00:00'));

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.review', $request), [
            'decision' => 'approve',
        ]);

        $response->assertSessionHas('success');

        $todayAttendance = StudentDailyAttendance::where('student_id', $this->student->id)
            ->whereDate('date', '2026-08-05')
            ->first();
        $this->assertNotNull($todayAttendance, 'Checkout harus tercatat pada absensi tanggal hari ini.');
        $this->assertNotNull($todayAttendance->check_out_at);
        $this->assertEquals('additional_activity', $todayAttendance->check_out_status);
    }

    public function test_checkout_from_outside_area_allowed_with_approved_remote_dispen(): void
    {
        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'date_end' => '2026-08-06',
            'category' => 'lomba',
            'reason' => 'Lomba provinsi',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        Carbon::setTestNow(Carbon::parse('2026-08-05 15:00:00'));

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-out'), $this->checkInPayload($this->farLocationPayload()));

        $response->assertSessionHas('success');
        $attendance = StudentDailyAttendance::where('student_id', $this->student->id)
            ->whereDate('date', '2026-08-05')
            ->first();
        $this->assertNotNull($attendance->check_out_at);
        $this->assertGreaterThan(100, $attendance->check_out_distance_meters);
    }
}
