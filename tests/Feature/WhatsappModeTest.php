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
use App\Models\WhatsappNotificationLog;
use App\Support\DailyAttendanceWhatsappNotifier;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WhatsappModeTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected User $waliKelas;

    protected Classes $class;

    protected int $institutionId;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-08-03 09:00:00'));

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
            'parent_phone' => '081300000001',
        ]);
        $this->student->assignRole('siswa');

        Setting::set('school_location_enabled', '1', 'general', $this->institutionId);
        Setting::set('school_location_latitude', '-6.200000', 'general', $this->institutionId);
        Setting::set('school_location_longitude', '106.816666', 'general', $this->institutionId);
        Setting::set('school_location_radius_meters', '100', 'general', $this->institutionId);
    }

    private function createStudent(string $phone = '081300000002'): User
    {
        $student = User::factory()->create([
            'institution_id' => $this->institutionId,
            'class_id' => $this->class->id,
            'status' => 'active',
            'parent_phone' => $phone,
        ]);
        $student->assignRole('siswa');

        return $student;
    }

    private function createAttendance(User $student, string $status, ?string $checkOutStatus = null): StudentDailyAttendance
    {
        return StudentDailyAttendance::create([
            'student_id' => $student->id,
            'class_id' => $student->class_id,
            'institution_id' => $student->institution_id,
            'date' => '2026-08-03',
            'check_in_at' => Carbon::parse('2026-08-03 07:30:00'),
            'check_in_status' => $status,
            'check_out_at' => $checkOutStatus ? Carbon::parse('2026-08-03 15:00:00') : null,
            'check_out_status' => $checkOutStatus,
        ]);
    }

    private function send(User $student, StudentDailyAttendance $attendance, string $event): void
    {
        $setting = DailyAttendanceSetting::forInstitution($this->institutionId);
        app(DailyAttendanceWhatsappNotifier::class)->sendIfEligible($student, $attendance->fresh(), $setting, $event);
    }

    public function test_hemat_mode_skips_on_time_check_in(): void
    {
        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
            'whatsapp_mode' => 'hemat',
        ]);

        $attendance = $this->createAttendance($this->student, 'on_time');
        $this->send($this->student, $attendance, 'check_in');

        $this->assertDatabaseCount('whatsapp_notification_logs', 0);
    }

    public function test_hemat_mode_sends_late_check_in(): void
    {
        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
            'whatsapp_mode' => 'hemat',
        ]);

        $attendance = $this->createAttendance($this->student, 'late');
        $this->send($this->student, $attendance, 'check_in');

        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'student_daily_attendance_id' => $attendance->id,
            'event_type' => 'check_in',
            'recipient_phone' => '081300000001',
        ]);
    }

    public function test_hemat_mode_skips_normal_checkout(): void
    {
        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
            'whatsapp_mode' => 'hemat',
        ]);

        $attendance = $this->createAttendance($this->student, 'on_time', 'checked_out');
        $this->send($this->student, $attendance, 'check_out');

        $this->assertDatabaseCount('whatsapp_notification_logs', 0);
    }

    public function test_hemat_mode_sends_early_with_permission_checkout(): void
    {
        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
            'whatsapp_mode' => 'hemat',
        ]);

        $attendance = $this->createAttendance($this->student, 'on_time', 'early_with_permission');
        $this->send($this->student, $attendance, 'check_out');

        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'student_daily_attendance_id' => $attendance->id,
            'event_type' => 'check_out',
            'recipient_phone' => '081300000001',
        ]);
    }

    public function test_lengkap_mode_sends_on_time_check_in_and_normal_checkout(): void
    {
        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
            'whatsapp_mode' => 'lengkap',
        ]);

        $attendance = $this->createAttendance($this->student, 'on_time', 'checked_out');
        $this->send($this->student, $attendance, 'check_in');
        $this->send($this->student, $attendance, 'check_out');

        $this->assertDatabaseCount('whatsapp_notification_logs', 2);
    }

    public function test_absent_command_notifies_absent_and_skips_present_and_excused(): void
    {
        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
            'whatsapp_mode' => 'hemat',
        ]);

        $present = $this->createStudent('081300000002');
        $this->createAttendance($present, 'on_time');

        $excused = $this->createStudent('081300000003');
        StudentEarlyLeaveRequest::create([
            'student_id' => $excused->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'sakit',
            'reason' => 'Demam',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $absent = $this->createStudent('081300000004');

        $this->artisan('attendance:notify-absent')->assertExitCode(0);

        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'student_id' => $absent->id,
            'event_type' => 'absent',
            'recipient_phone' => '081300000004',
        ]);
        $this->assertDatabaseMissing('whatsapp_notification_logs', [
            'student_id' => $present->id,
            'event_type' => 'absent',
        ]);
        $this->assertDatabaseMissing('whatsapp_notification_logs', [
            'student_id' => $excused->id,
            'event_type' => 'absent',
        ]);
    }

    public function test_absent_command_skips_student_with_pending_request(): void
    {
        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
            'whatsapp_mode' => 'hemat',
        ]);

        $pending = $this->createStudent('081300000009');
        StudentEarlyLeaveRequest::create([
            'student_id' => $pending->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'izin',
            'reason' => 'Ada urusan keluarga',
            'status' => 'pending',
        ]);

        $absent = $this->createStudent('081300000010');

        $this->artisan('attendance:notify-absent')->assertExitCode(0);

        $this->assertDatabaseMissing('whatsapp_notification_logs', [
            'student_id' => $pending->id,
            'event_type' => 'absent',
        ]);
        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'student_id' => $absent->id,
            'event_type' => 'absent',
            'recipient_phone' => '081300000010',
        ]);
    }

    public function test_hanya_absen_mode_skips_check_in_and_check_out_notifications(): void
    {
        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
            'whatsapp_mode' => 'hanya_absen',
        ]);

        $attendance = $this->createAttendance($this->student, 'late', 'checked_out');
        $this->send($this->student, $attendance, 'check_in');
        $this->send($this->student, $attendance, 'check_out');

        $this->assertDatabaseCount('whatsapp_notification_logs', 0);
    }

    public function test_hanya_absen_mode_skips_early_leave_decision_notification(): void
    {
        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
            'whatsapp_mode' => 'hanya_absen',
        ]);

        $request = StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'izin',
            'reason' => 'Ada urusan keluarga',
            'status' => 'pending',
        ]);

        app(DailyAttendanceWhatsappNotifier::class)->sendEarlyLeaveDecision($request, 'approved');

        $this->assertDatabaseCount('whatsapp_notification_logs', 0);
    }

    public function test_hanya_absen_mode_still_notifies_absent(): void
    {
        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
            'whatsapp_mode' => 'hanya_absen',
        ]);

        $absent = $this->createStudent('081300000011');

        $this->artisan('attendance:notify-absent')->assertExitCode(0);

        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'student_id' => $absent->id,
            'event_type' => 'absent',
            'recipient_phone' => '081300000011',
        ]);
    }

    public function test_absent_command_notifies_alpha_student(): void
    {
        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
            'whatsapp_mode' => 'hanya_absen',
        ]);

        $alpha = $this->createStudent('081300000012');
        $this->createAttendance($alpha, 'alpha');

        $this->artisan('attendance:notify-absent')->assertExitCode(0);

        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'student_id' => $alpha->id,
            'event_type' => 'absent',
            'recipient_phone' => '081300000012',
        ]);
    }

    public function test_absent_command_skips_approved_dispen_without_checkin(): void
    {
        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
            'whatsapp_mode' => 'hanya_absen',
        ]);

        $dispen = $this->createStudent('081300000013');
        StudentEarlyLeaveRequest::create([
            'student_id' => $dispen->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'lomba',
            'reason' => 'Lomba renang',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $this->artisan('attendance:notify-absent')->assertExitCode(0);

        $this->assertDatabaseMissing('whatsapp_notification_logs', [
            'student_id' => $dispen->id,
            'event_type' => 'absent',
        ]);
    }

    public function test_absent_command_skips_before_verification_deadline(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-03 07:30:00'));

        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
            'whatsapp_mode' => 'hanya_absen',
            'check_in_verification_deadline' => '08:00:00',
        ]);

        $this->createStudent('081300000014');

        $this->artisan('attendance:notify-absent')->assertExitCode(0);

        $this->assertDatabaseCount('whatsapp_notification_logs', 0);
    }

    public function test_absent_command_notifies_right_after_verification_deadline(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-03 08:01:00'));

        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
            'whatsapp_mode' => 'hanya_absen',
            'check_in_verification_deadline' => '08:00:00',
        ]);

        $absent = $this->createStudent('081300000015');

        $this->artisan('attendance:notify-absent')->assertExitCode(0);

        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'student_id' => $absent->id,
            'event_type' => 'absent',
            'recipient_phone' => '081300000015',
        ]);
    }

    public function test_absent_command_is_idempotent(): void
    {
        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
        ]);

        $absent = $this->createStudent('081300000005');

        $this->artisan('attendance:notify-absent')->assertExitCode(0);
        $this->artisan('attendance:notify-absent')->assertExitCode(0);

        $this->assertSame(1, WhatsappNotificationLog::query()
            ->where('student_id', $absent->id)
            ->where('event_type', 'absent')
            ->count());
    }

    public function test_absent_command_skips_when_whatsapp_disabled(): void
    {
        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => false,
        ]);

        $this->createStudent('081300000006');

        $this->artisan('attendance:notify-absent')->assertExitCode(0);

        $this->assertDatabaseCount('whatsapp_notification_logs', 0);
    }

    public function test_absent_command_skips_non_operational_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-02 10:00:00'));

        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
        ]);

        $this->createStudent('081300000007');

        $this->artisan('attendance:notify-absent')->assertExitCode(0);

        $this->assertDatabaseCount('whatsapp_notification_logs', 0);
    }
}
