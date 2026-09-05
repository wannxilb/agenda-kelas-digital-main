<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\DailyAttendanceSetting;
use App\Models\Institution;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\StudentDailyAttendance;
use App\Models\StudentEarlyLeaveRequest;
use App\Models\Subject;
use App\Models\User;
use App\Models\WhatsappNotificationLog;
use App\Services\AttendanceSummaryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentDispensationTest extends TestCase
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

    public function test_student_can_request_future_dispensation_without_check_in_today(): void
    {
        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'izin_lainnya',
            'reason' => 'Urusan keluarga mendadak',
            'date' => '2026-08-04',
            'date_end' => '2026-08-06',
            'evidence' => UploadedFile::fake()->create('bukti-izin.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', [
            'student_id' => $this->student->id,
            'date' => '2026-08-04',
            'date_end' => '2026-08-06',
            'category' => 'izin_lainnya',
            'status' => 'pending',
        ]);
    }

    public function test_student_can_request_same_day_absence_permission_without_check_in(): void
    {
        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'izin_lainnya',
            'reason' => 'Meninggalkan sekolah',
            'date' => '2026-08-03',
            'evidence' => UploadedFile::fake()->create('bukti-izin.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'category' => 'izin_lainnya',
            'status' => 'pending',
        ]);
    }

    public function test_check_in_outside_school_area_is_rejected_without_dispensation(): void
    {
        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload($this->farLocationPayload()));

        $response->assertSessionHas('error', 'Absensi hanya dapat dilakukan di dalam area sekolah.');
        $this->assertDatabaseMissing('student_daily_attendances', ['student_id' => $this->student->id]);
    }

    public function test_approved_dispensation_allows_check_in_outside_school_area(): void
    {
        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'lomba',
            'reason' => 'Lomba keluar kota',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload($this->farLocationPayload()));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'check_in_status' => 'on_time',
            'verification_method' => 'approved_dispen',
        ]);
    }

    public function test_multi_day_approved_dispensation_covers_today_for_remote_check_in(): void
    {
        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'date_end' => '2026-08-05',
            'category' => 'kegiatan_sekolah',
            'reason' => 'Perjalanan dinas sekolah',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $this->assertTrue(StudentEarlyLeaveRequest::hasRemoteApprovedForDate($this->student->id, '2026-08-04'));
        $this->assertFalse(StudentEarlyLeaveRequest::hasRemoteApprovedForDate($this->student->id, '2026-08-06'));

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload($this->farLocationPayload()));

        $response->assertSessionHas('success');
        $attendance = StudentDailyAttendance::where('student_id', $this->student->id)
            ->whereDate('date', '2026-08-03')
            ->first();
        $this->assertEquals('approved_dispen', $attendance->verification_method);
        $this->assertTrue($attendance->check_in_distance_meters > 100);
    }

    public function test_pulang_kegiatan_requires_activity_name(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'pulang_kegiatan',
            'reason' => 'Ada acara keluarga',
            'date' => '2026-08-03',
        ]);

        $response->assertSessionHasErrors('activity_name');
        $this->assertDatabaseCount('student_early_leave_requests', 0);
    }

    public function test_pulang_kegiatan_stores_activity_name(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'pulang_kegiatan',
            'reason' => 'Ada acara keluarga',
            'date' => '2026-08-03',
            'activity_name' => 'Lomba OSN',
            'evidence' => UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'category' => 'pulang_kegiatan',
            'activity_name' => 'Lomba OSN',
            'status' => 'pending',
        ]);
    }

    public function test_remote_dispensation_does_not_require_activity_name(): void
    {
        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'lomba',
            'reason' => 'Lomba LKS tingkat provinsi',
            'date' => '2026-08-04',
            'date_end' => '2026-08-06',
            'evidence' => UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', [
            'student_id' => $this->student->id,
            'date' => '2026-08-04',
            'activity_name' => null,
        ]);
    }

    public function test_single_day_category_ignores_date_end(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'pulang_dijemput',
            'reason' => 'Dijemput orang tua',
            'date' => '2026-08-03',
            'date_end' => '2026-08-06',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'date_end' => null,
            'status' => 'pending',
        ]);
    }

    public function test_single_day_category_cannot_be_requested_for_another_day(): void
    {
        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'pulang_dijemput',
            'reason' => 'Urusan keluarga besok',
            'date' => '2026-08-04',
        ]);

        $response->assertSessionHasErrors('date');
        $this->assertDatabaseCount('student_early_leave_requests', 0);
    }

    public function test_remote_dispensation_stays_pending_for_wali_kelas_review(): void
    {
        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'izin_lainnya',
            'reason' => 'Urusan keluarga',
            'date' => '2026-08-04',
            'date_end' => '2026-08-06',
            'evidence' => UploadedFile::fake()->create('bukti-izin.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', [
            'student_id' => $this->student->id,
            'date' => '2026-08-04',
            'category' => 'izin_lainnya',
            'status' => 'pending',
        ]);
    }

    public function test_approved_pulang_cepat_allows_early_checkout_with_permission(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'pulang_dijemput',
            'reason' => 'Dijemput orang tua',
            'date' => '2026-08-03',
        ]);

        $earlyLeave = StudentEarlyLeaveRequest::where('student_id', $this->student->id)->first();

        $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.review', $earlyLeave), [
            'decision' => 'approve',
            'notes' => 'Disetujui',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-08-03 14:00:00'));

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-out'), [
            'device_fingerprint' => 'device-test-123',
            'latitude' => '-6.200100',
            'longitude' => '106.816666',
            'accuracy' => 12,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_daily_attendances', [
            'student_id' => $this->student->id,
            'check_out_status' => 'early_with_permission',
        ]);
    }

    public function test_check_in_after_operational_end_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-03 17:00:00'));

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $response->assertSessionHas('error', 'Batas absensi masuk sudah ditutup pukul 16:00.');
        $this->assertDatabaseMissing('student_daily_attendances', ['student_id' => $this->student->id]);
    }

    public function test_late_check_in_after_verification_deadline_becomes_alpha(): void
    {
        $setting = DailyAttendanceSetting::forInstitution($this->institutionId);
        $setting->update(['check_in_verification_deadline' => '08:00:00']);

        Carbon::setTestNow(Carbon::parse('2026-08-03 11:00:00'));

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'check_in_status' => 'alpha',
        ]);
    }

    public function test_checkout_uses_absolute_admin_time_ignoring_schedule(): void
    {
        $setting = DailyAttendanceSetting::forInstitution($this->institutionId);
        $setting->update(['check_out_start' => '23:45:00']);

        $subject = Subject::create(['name' => 'Matematika', 'code' => 'MATH']);
        Schedule::create([
            'class_id' => $this->class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $this->waliKelas->id,
            'day' => 'Monday',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
        ]);

        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        Carbon::setTestNow(Carbon::parse('2026-08-03 16:00:00'));

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-out'), [
            'device_fingerprint' => 'device-test-123',
            'latitude' => '-6.200100',
            'longitude' => '106.816666',
            'accuracy' => 12,
        ]);

        $response->assertSessionHas('error', 'Absensi pulang terlalu awal. Ajukan izin dan tunggu persetujuan wali kelas.');
        $this->assertDatabaseMissing('student_daily_attendances', ['student_id' => $this->student->id, 'check_out_at' => '2026-08-03 16:00:00']);
    }

    public function test_approved_pulang_cepat_allows_checkout_after_operational_end(): void
    {
        $setting = DailyAttendanceSetting::forInstitution($this->institutionId);
        $setting->update(['check_out_start' => '23:45:00']);

        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'pulang_dijemput',
            'reason' => 'Dijemput orang tua',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        Carbon::setTestNow(Carbon::parse('2026-08-03 18:00:00'));

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-out'), [
            'device_fingerprint' => 'device-test-123',
            'latitude' => '-6.200100',
            'longitude' => '106.816666',
            'accuracy' => 12,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_daily_attendances', [
            'student_id' => $this->student->id,
            'check_out_status' => 'early_with_permission',
        ]);
    }

    public function test_izin_lainnya_requires_evidence(): void
    {
        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'izin_lainnya',
            'reason' => 'Urusan keluarga',
            'date' => '2026-08-04',
        ]);

        $response->assertSessionHasErrors('evidence');
        $this->assertDatabaseCount('student_early_leave_requests', 0);
    }

    public function test_approved_pulang_cepat_does_not_allow_remote_check_in(): void
    {
        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'pulang_dijemput',
            'reason' => 'Dijemput orang tua',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload($this->farLocationPayload()));

        $response->assertSessionHas('error', 'Absensi hanya dapat dilakukan di dalam area sekolah.');
        $this->assertDatabaseMissing('student_daily_attendances', ['student_id' => $this->student->id]);
    }

    public function test_pulang_cepat_checkout_at_normal_time_is_plain_checked_out(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'pulang_lainnya',
            'reason' => 'Urusan mendadak',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        Carbon::setTestNow(Carbon::parse('2026-08-03 16:00:00'));

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-out'), [
            'device_fingerprint' => 'device-test-123',
            'latitude' => '-6.200100',
            'longitude' => '106.816666',
            'accuracy' => 12,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_daily_attendances', [
            'student_id' => $this->student->id,
            'check_out_status' => 'checked_out',
        ]);
    }

    public function test_approving_pulang_cepat_does_not_auto_record_checkout(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $request = StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'pulang_dijemput',
            'reason' => 'Dijemput orang tua',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.review', $request), [
            'decision' => 'approve',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', ['id' => $request->id, 'status' => 'approved']);
        $this->assertDatabaseMissing('student_daily_attendances', ['student_id' => $this->student->id, 'check_out_at' => now()]);
    }

    public function test_approving_dispen_auto_records_checkout(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $request = StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.review', $request), [
            'decision' => 'approve',
        ]);

        $response->assertSessionHas('success');
        $attendance = StudentDailyAttendance::where('student_id', $this->student->id)
            ->whereDate('date', '2026-08-03')
            ->first();
        $this->assertNotNull($attendance->check_out_at);
        $this->assertEquals('additional_activity', $attendance->check_out_status);
    }

    public function test_kegiatan_tambahan_requires_check_in_before_request(): void
    {
        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'kegiatan_tambahan',
            'reason' => 'Latihan futsal persiapan tanding',
            'date' => '2026-08-03',
            'activity_name' => 'Latihan futsal',
        ]);

        $response->assertSessionHas('error', 'Anda harus melakukan absensi masuk terlebih dahulu sebelum mengajukan izin / dispensasi untuk hari ini.');
        $this->assertDatabaseCount('student_early_leave_requests', 0);
    }

    public function test_kegiatan_tambahan_requires_activity_name(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'kegiatan_tambahan',
            'reason' => 'Latihan ekstrakurikuler',
            'date' => '2026-08-03',
        ]);

        $response->assertSessionHasErrors('activity_name');
        $this->assertDatabaseCount('student_early_leave_requests', 0);
    }

    public function test_kegiatan_tambahan_stores_activity_name_and_times_without_evidence(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'kegiatan_tambahan',
            'reason' => 'Latihan futsal persiapan tanding',
            'date' => '2026-08-03',
            'activity_name' => 'Latihan futsal',
            'activity_start_time' => '14:00',
            'activity_end_time' => '15:30',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'date_end' => null,
            'category' => 'kegiatan_tambahan',
            'activity_name' => 'Latihan futsal',
            'activity_start_time' => '14:00',
            'activity_end_time' => '15:30',
            'status' => 'pending',
        ]);
    }

    public function test_kegiatan_tambahan_is_single_day_today_only(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'kegiatan_tambahan',
            'reason' => 'Latihan ekstrakurikuler',
            'date' => '2026-08-04',
            'activity_name' => 'Latihan futsal',
        ]);

        $response->assertSessionHasErrors('date');
        $response->assertSessionHasErrors([
            'date' => 'Kategori ini hanya dapat diajukan untuk hari ini.',
        ]);
    }

    public function test_kegiatan_tambahan_rejects_time_range_where_end_not_after_start(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'kegiatan_tambahan',
            'reason' => 'Latihan ekstrakurikuler',
            'date' => '2026-08-03',
            'activity_name' => 'Latihan futsal',
            'activity_start_time' => '15:30',
            'activity_end_time' => '14:00',
        ]);

        $response->assertSessionHasErrors([
            'activity_end_time' => 'Jam selesai kegiatan harus setelah jam mulai.',
        ]);
        $this->assertDatabaseCount('student_early_leave_requests', 0);
    }

    public function test_approving_kegiatan_tambahan_does_not_auto_record_checkout(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $request = StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'kegiatan_tambahan',
            'reason' => 'Latihan futsal',
            'activity_name' => 'Latihan futsal',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.review', $request), [
            'decision' => 'approve',
        ]);

        $response->assertSessionHas('success');
        $attendance = StudentDailyAttendance::where('student_id', $this->student->id)
            ->whereDate('date', '2026-08-03')
            ->first();
        $this->assertNotNull($attendance->check_in_at);
        $this->assertNull($attendance->check_out_at);
    }

    public function test_approved_kegiatan_tambahan_does_not_allow_remote_check_in(): void
    {
        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'kegiatan_tambahan',
            'reason' => 'Latihan futsal',
            'activity_name' => 'Latihan futsal',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload($this->farLocationPayload()));

        $response->assertSessionHas('error', 'Absensi hanya dapat dilakukan di dalam area sekolah.');
        $this->assertDatabaseMissing('student_daily_attendances', ['student_id' => $this->student->id]);
    }

    public function test_approved_kegiatan_tambahan_presensi_comes_from_actual_check_in(): void
    {
        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'kegiatan_tambahan',
            'reason' => 'Latihan futsal',
            'activity_name' => 'Latihan futsal',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'status' => 'present',
            'source' => 'digital',
        ]);
    }

    public function test_wali_kelas_approving_multi_day_dispensation_does_not_fabricate_future_attendance(): void
    {
        $request = StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-04',
            'date_end' => '2026-08-06',
            'category' => 'lomba',
            'reason' => 'Lomba provinsi',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.review', $request), [
            'decision' => 'approve',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', ['id' => $request->id, 'status' => 'approved']);
        $this->assertDatabaseCount('student_daily_attendances', 0);
    }

    public function test_sick_student_can_request_same_day_dispensation_without_check_in(): void
    {
        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'sakit',
            'reason' => 'Demam tinggi',
            'date' => '2026-08-03',
            'evidence' => UploadedFile::fake()->create('surat-sakit.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'category' => 'sakit',
            'status' => 'pending',
        ]);
    }

    public function test_sick_dispensation_requires_evidence(): void
    {
        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'sakit',
            'reason' => 'Demam tinggi',
            'date' => '2026-08-03',
        ]);

        $response->assertSessionHasErrors('evidence');
        $this->assertDatabaseCount('student_early_leave_requests', 0);
    }

    public function test_sick_student_can_request_retroactive_dispensation_with_sick_letter(): void
    {
        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'sakit',
            'reason' => 'Sakit dan membawa surat keterangan dokter',
            'date' => '2026-08-02',
            'evidence' => UploadedFile::fake()->create('surat-dokter.pdf', 200, 'application/pdf'),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', [
            'student_id' => $this->student->id,
            'date' => '2026-08-02',
            'category' => 'sakit',
            'status' => 'pending',
        ]);
    }

    public function test_sick_dispensation_cannot_be_older_than_seven_days(): void
    {
        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'sakit',
            'reason' => 'Sakit minggu lalu',
            'date' => '2026-07-26',
            'evidence' => UploadedFile::fake()->create('surat-sakit.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertSessionHasErrors('date');
        $this->assertDatabaseCount('student_early_leave_requests', 0);
    }

    public function test_approving_sick_dispensation_does_not_auto_record_checkout(): void
    {
        $request = StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'sakit',
            'reason' => 'Demam tinggi',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.review', $request), [
            'decision' => 'approve',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', ['id' => $request->id, 'status' => 'approved']);
        $this->assertDatabaseCount('student_daily_attendances', 0);
    }

    public function test_approved_full_day_absence_blocks_checkout(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-03 15:00:00'));

        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'sakit',
            'reason' => 'Demam tinggi',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-out'), [
            'device_fingerprint' => 'device-test-123',
            'latitude' => '-6.200100',
            'longitude' => '106.816666',
            'accuracy' => 12,
        ]);

        $response->assertSessionHas('error', 'Anda memiliki pengajuan ketidakhadiran yang disetujui untuk hari ini, sehingga tidak dapat melakukan absensi pulang.');
        $this->assertDatabaseMissing('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
        ]);
    }

    public function test_approved_absence_is_not_shown_as_alpha_on_student_daily_attendance(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-03 20:03:00'));

        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'izin',
            'reason' => 'Keperluan keluarga',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($this->student)->get(route('siswa.daily-attendance.index'));

        $response->assertOk();
        $response->assertSeeText('Izin');
        $response->assertSeeText('Izin ketidakhadiran aktif');
        $this->assertStringNotContainsString('Alpha', $response->getContent());
    }

    public function test_student_daily_attendance_defaults_on_non_operational_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-08 20:00:00'));

        $response = $this->actingAs($this->student)->get(route('siswa.daily-attendance.index'));

        $response->assertOk();
        $response->assertSeeText('Hari non-operasional');
        $response->assertSeeText('Hari ini bukan hari operasional sekolah.');
        $response->assertSee('Hari tutup', false);
        $this->assertStringNotContainsString('Alpha', $response->getContent());
    }

    public function test_student_presensi_page_defaults_on_non_operational_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-08 20:00:00'));

        $response = $this->actingAs($this->student)->get(route('siswa.attendance.index', ['month' => '2026-08']));

        $response->assertOk();
        $response->assertSeeText('Belum Ada Presensi');
        $response->assertSeeText('Belum ada riwayat');
        $this->assertStringNotContainsString('Status Hari Ini', $response->getContent());
    }

    public function test_sick_student_shown_as_sakit_not_tidak_masuk_on_monitor(): void
    {
        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'sakit',
            'reason' => 'Demam tinggi',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($this->waliKelas)->get(route('wali-kelas.daily-attendance.index', ['date' => '2026-08-03']));

        $response->assertOk();
        $response->assertSee('Sakit', false);
        $this->assertStringNotContainsString('Tidak masuk', $response->getContent());
    }

    public function test_late_approved_multi_day_absence_overrides_existing_alpha_final_status(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-04 12:00:00'));

        Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'status' => 'absent',
            'note' => 'Alpha karena presensi sudah ditutup.',
        ]);

        $request = StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'date_end' => '2026-08-05',
            'category' => 'izin_lainnya',
            'reason' => 'Surat izin keluarga terlambat dikirim',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.review', $request), [
            'decision' => 'approve',
        ]);

        $response->assertSessionHas('success');

        foreach (['2026-08-03', '2026-08-04', '2026-08-05'] as $date) {
            $this->assertDatabaseHas('attendances', [
                'student_id' => $this->student->id,
                'date' => $date,
                'status' => 'excused',
            ]);
        }

        $resolved = app(AttendanceSummaryService::class)
            ->resolveForRange([$this->student->id], '2026-08-03', '2026-08-05');

        $this->assertEquals([
            '2026-08-03' => 'excused',
            '2026-08-04' => 'excused',
            '2026-08-05' => 'excused',
        ], $resolved[$this->student->id]);
    }

    // ------------------------------------------------------------------
    // Batas tanggal pengajuan ke depan
    // ------------------------------------------------------------------

    public function test_student_cannot_request_dispensation_beyond_max_future_days(): void
    {
        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'lomba',
            'reason' => 'Lomba provinsi jauh hari',
            'date' => '2026-10-01',
            'evidence' => UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertSessionHasErrors('date');
        $this->assertDatabaseCount('student_early_leave_requests', 0);
    }

    // ------------------------------------------------------------------
    // Konflik antar pengajuan (pending / approved)
    // ------------------------------------------------------------------

    public function test_student_cannot_submit_request_overlapping_existing_request(): void
    {
        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-04',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'sakit',
            'reason' => 'Demam',
            'date' => '2026-08-04',
            'evidence' => UploadedFile::fake()->create('surat.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('student_early_leave_requests', 1);
    }

    // ------------------------------------------------------------------
    // Pembatalan pengajuan oleh siswa
    // ------------------------------------------------------------------

    public function test_student_can_cancel_pending_request(): void
    {
        $request = StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-04',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.cancel', $request));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', ['id' => $request->id, 'status' => 'cancelled']);
    }

    public function test_student_cannot_cancel_processed_request(): void
    {
        $request = StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-04',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.cancel', $request));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('student_early_leave_requests', ['id' => $request->id, 'status' => 'approved']);
    }

    public function test_student_cannot_cancel_another_students_request(): void
    {
        $other = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $other->assignRole('siswa');

        $request = StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-04',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($other)->post(route('siswa.daily-attendance.early-leave.cancel', $request));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('student_early_leave_requests', ['id' => $request->id, 'status' => 'pending']);
    }

    // ------------------------------------------------------------------
    // Notifikasi WhatsApp hasil review
    // ------------------------------------------------------------------

    public function test_wali_kelas_approval_creates_whatsapp_notification_to_parent(): void
    {
        $this->student->update(['parent_phone' => '081300000001']);

        DailyAttendanceSetting::forInstitution($this->institutionId)->update(['whatsapp_enabled' => true]);

        $request = StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-04',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.review', $request), [
            'decision' => 'approve',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'early_leave_request_id' => $request->id,
            'event_type' => 'early_leave_approved',
            'recipient_phone' => '081300000001',
        ]);
    }

    public function test_wali_kelas_rejection_creates_whatsapp_notification_with_reason(): void
    {
        $this->student->update(['parent_phone' => '081300000001']);

        DailyAttendanceSetting::forInstitution($this->institutionId)->update(['whatsapp_enabled' => true]);

        $request = StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-04',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.review', $request), [
            'decision' => 'reject',
            'reviewer_note' => 'Bukti tidak jelas',
        ]);

        $response->assertSessionHas('success');

        $log = WhatsappNotificationLog::where('early_leave_request_id', $request->id)
            ->where('event_type', 'early_leave_rejected')
            ->first();
        $this->assertNotNull($log);
        $this->assertEquals('081300000001', $log->recipient_phone);
        $this->assertStringContainsString('DITOLAK', $log->message);
        $this->assertStringContainsString('Bukti tidak jelas', $log->message);
    }

    public function test_approval_does_not_send_whatsapp_when_disabled(): void
    {
        $this->student->update(['parent_phone' => '081300000001']);

        $request = StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-04',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'pending',
        ]);

        $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.review', $request), [
            'decision' => 'approve',
        ]);

        $this->assertDatabaseCount('whatsapp_notification_logs', 0);
    }

    // ------------------------------------------------------------------
    // Waktu auto pulang memakai jadwal pulang, bukan waktu approval
    // ------------------------------------------------------------------

    public function test_auto_checkout_uses_scheduled_time_when_approved_before_school_end(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload());

        $request = StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'pending',
        ]);

        $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.review', $request), [
            'decision' => 'approve',
        ]);

        $attendance = StudentDailyAttendance::where('student_id', $this->student->id)
            ->whereDate('date', '2026-08-03')
            ->first();
        $this->assertNotNull($attendance->check_out_at);
        $this->assertEquals('2026-08-03 14:30:00', $attendance->check_out_at->toDateTimeString());
    }
}
