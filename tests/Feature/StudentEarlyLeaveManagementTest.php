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
use App\Services\AttendanceStatusResolver;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentEarlyLeaveManagementTest extends TestCase
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

        $setting = DailyAttendanceSetting::forInstitution($this->institutionId);
        $setting->update([
            'check_in_deadline' => '10:00:00',
            'require_check_in_photo' => false,
            'require_check_out_photo' => false,
        ]);
    }

    private function createRequest(array $overrides = []): StudentEarlyLeaveRequest
    {
        return StudentEarlyLeaveRequest::create(array_merge([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-04',
            'date_end' => '2026-08-05',
            'category' => 'izin',
            'reason' => 'Acara keluarga',
            'status' => 'pending',
            'evidence_path' => 'early-leave-evidence/'.$this->student->id.'/existing.enc',
            'evidence_mime' => 'application/pdf',
        ], $overrides));
    }

    public function test_student_sees_full_early_leave_history(): void
    {
        $pending = $this->createRequest(['reason' => 'Pengajuan menunggu']);
        $approved = $this->createRequest([
            'date' => '2026-08-01',
            'date_end' => null,
            'category' => 'sakit',
            'reason' => 'Sakit demam',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
            'reviewer_note' => 'Semoga cepat sembuh',
        ]);

        $response = $this->actingAs($this->student)->get(route('siswa.daily-attendance.early-leave.history'));

        $response->assertOk();
        $response->assertSee($pending->reason);
        $response->assertSee($approved->reason);
        $response->assertSee('Semoga cepat sembuh');
        $response->assertSee($this->waliKelas->name);
    }

    public function test_student_can_update_own_pending_request(): void
    {
        $request = $this->createRequest();

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.update', $request), [
            'category' => 'izin',
            'reason' => 'Alasan yang diperbarui',
            'date' => '2026-08-04',
            'date_end' => '2026-08-06',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', [
            'id' => $request->id,
            'reason' => 'Alasan yang diperbarui',
            'date' => '2026-08-04',
            'date_end' => '2026-08-06',
            'status' => 'pending',
        ]);
    }

    public function test_student_can_change_category_while_editing(): void
    {
        $request = $this->createRequest();

        $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.update', $request), [
            'category' => 'lomba',
            'reason' => 'Mewakili sekolah lomba robotik',
            'activity_name' => 'Lomba Robotik',
            'date' => '2026-08-05',
        ]);

        $this->assertDatabaseHas('student_early_leave_requests', [
            'id' => $request->id,
            'category' => 'lomba',
            'activity_name' => 'Lomba Robotik',
            'date' => '2026-08-05',
            'date_end' => null,
        ]);
    }

    public function test_student_cannot_update_processed_request(): void
    {
        $request = $this->createRequest(['status' => 'approved']);

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.update', $request), [
            'category' => 'izin',
            'reason' => 'Dicoba ubah',
            'date' => '2026-08-04',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('student_early_leave_requests', [
            'id' => $request->id,
            'reason' => 'Acara keluarga',
            'status' => 'approved',
        ]);
    }

    public function test_student_cannot_update_another_students_request(): void
    {
        $other = User::factory()->create([
            'institution_id' => $this->institutionId,
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);
        $other->assignRole('siswa');
        $request = $this->createRequest(['student_id' => $other->id]);

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.update', $request), [
            'category' => 'izin',
            'reason' => 'Dicoba ubah',
            'date' => '2026-08-04',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('student_early_leave_requests', [
            'id' => $request->id,
            'reason' => 'Acara keluarga',
        ]);
    }

    public function test_update_still_respects_single_day_rule(): void
    {
        $request = $this->createRequest([
            'category' => 'pulang_awal',
            'date' => '2026-08-03',
            'date_end' => null,
        ]);

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.update', $request), [
            'category' => 'pulang_awal',
            'reason' => 'Keperluan keluarga',
            'date' => '2026-08-04',
        ]);

        $response->assertSessionHasErrors('date');
        $this->assertDatabaseHas('student_early_leave_requests', [
            'id' => $request->id,
            'date' => '2026-08-03',
        ]);
    }

    public function test_update_rejects_overlap_with_another_active_request(): void
    {
        $request = $this->createRequest(['date' => '2026-08-04', 'date_end' => '2026-08-05']);
        $this->createRequest(['date' => '2026-08-06', 'date_end' => '2026-08-08', 'reason' => 'Pengajuan lain']);

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.update', $request), [
            'category' => 'izin',
            'reason' => 'Bentrok tanggal',
            'date' => '2026-08-07',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('student_early_leave_requests', [
            'id' => $request->id,
            'date' => '2026-08-04',
        ]);
    }

    public function test_update_keeps_existing_evidence_without_new_file(): void
    {
        $request = $this->createRequest();

        $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.update', $request), [
            'category' => 'izin',
            'reason' => 'Perbarui tanpa ganti bukti',
            'date' => '2026-08-04',
        ]);

        $this->assertDatabaseHas('student_early_leave_requests', [
            'id' => $request->id,
            'evidence_path' => 'early-leave-evidence/'.$this->student->id.'/existing.enc',
        ]);
    }

    public function test_update_replaces_evidence_and_deletes_old_file(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('early-leave-evidence/'.$this->student->id.'/existing.enc', 'encrypted-old');
        $request = $this->createRequest();

        $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.update', $request), [
            'category' => 'izin',
            'reason' => 'Perbarui dengan bukti baru',
            'date' => '2026-08-04',
            'evidence' => UploadedFile::fake()->create('bukti-baru.pdf', 100, 'application/pdf'),
        ]);

        $updated = $request->fresh();
        $this->assertNotEquals('early-leave-evidence/'.$this->student->id.'/existing.enc', $updated->evidence_path);
        $this->assertEquals('application/pdf', $updated->evidence_mime);
        Storage::disk('local')->assertMissing('early-leave-evidence/'.$this->student->id.'/existing.enc');
    }

    public function test_store_rejects_date_end_before_date_with_indonesian_message(): void
    {
        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.store'), [
            'category' => 'lomba',
            'reason' => 'Mewakili sekolah',
            'date' => '2026-08-07',
            'date_end' => '2026-08-05',
            'evidence' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors(['date_end' => 'tanggal selesai harus sama dengan atau setelah tanggal.']);
        $this->assertDatabaseCount('student_early_leave_requests', 0);
    }

    public function test_update_rejects_date_end_before_date_with_indonesian_message(): void
    {
        $request = $this->createRequest();

        $response = $this->actingAs($this->student)->post(route('siswa.daily-attendance.early-leave.update', $request), [
            'category' => 'izin',
            'reason' => 'Perbarui dengan rentang salah',
            'date' => '2026-08-04',
            'date_end' => '2026-08-03',
        ]);

        $response->assertSessionHasErrors(['date_end' => 'tanggal selesai harus sama dengan atau setelah tanggal.']);
        $this->assertDatabaseHas('student_early_leave_requests', [
            'id' => $request->id,
            'date' => '2026-08-04',
            'date_end' => '2026-08-05',
        ]);
    }

    public function test_wali_kelas_sees_all_early_leave_requests_and_filters(): void
    {
        $pending = $this->createRequest(['reason' => 'Alasan menunggu review']);
        $approved = $this->createRequest([
            'reason' => 'Alasan sudah disetujui',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($this->waliKelas)->get(route('wali-kelas.daily-attendance.early-leave.index'));
        $response->assertOk();
        $response->assertSee('Alasan menunggu review');
        $response->assertSee('Alasan sudah disetujui');

        $filtered = $this->actingAs($this->waliKelas)->get(route('wali-kelas.daily-attendance.early-leave.index', ['status' => 'pending']));
        $filtered->assertOk();
        $filtered->assertSee('Alasan menunggu review');
        $filtered->assertDontSee('Alasan sudah disetujui');
    }

    public function test_wali_kelas_without_class_cannot_open_request_list(): void
    {
        $stranger = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $stranger->assignRole('wali_kelas');

        $response = $this->actingAs($stranger)->get(route('wali-kelas.daily-attendance.early-leave.index'));

        $response->assertOk();
    }

    public function test_batch_approve_processes_multiple_pending_requests(): void
    {
        $first = $this->createRequest(['reason' => 'Pertama']);
        $second = $this->createRequest(['reason' => 'Kedua']);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.batch'), [
            'request_ids' => [$first->id, $second->id],
            'decision' => 'approve',
            'reviewer_note' => 'Disetujui semua',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', [
            'id' => $first->id,
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewer_note' => 'Disetujui semua',
        ]);
        $this->assertDatabaseHas('student_early_leave_requests', [
            'id' => $second->id,
            'status' => 'approved',
        ]);
    }

    public function test_batch_approve_accepts_csv_request_ids(): void
    {
        $first = $this->createRequest(['reason' => 'Csv satu']);
        $second = $this->createRequest(['reason' => 'Csv dua']);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.batch'), [
            'request_ids' => $first->id.','.$second->id,
            'decision' => 'approve',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', ['id' => $first->id, 'status' => 'approved']);
        $this->assertDatabaseHas('student_early_leave_requests', ['id' => $second->id, 'status' => 'approved']);
    }

    public function test_batch_reject_requires_note(): void
    {
        $request = $this->createRequest();

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.batch'), [
            'request_ids' => [$request->id],
            'decision' => 'reject',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('student_early_leave_requests', ['id' => $request->id, 'status' => 'pending']);
    }

    public function test_batch_skips_requests_not_owned_or_already_processed(): void
    {
        $owned = $this->createRequest(['reason' => 'Milik kelas saya']);

        $otherClass = Classes::create([
            'name' => 'XI TKJ 1',
            'major' => 'TKJ',
            'grade_level' => 'XI',
            'academic_year' => '2026/2027',
            'capacity' => 30,
            'is_active' => true,
            'institution_id' => $this->institutionId,
        ]);
        $otherStudent = User::factory()->create([
            'institution_id' => $this->institutionId,
            'class_id' => $otherClass->id,
            'status' => 'active',
        ]);
        $otherStudent->assignRole('siswa');
        $foreign = StudentEarlyLeaveRequest::create([
            'student_id' => $otherStudent->id,
            'class_id' => $otherClass->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-04',
            'date_end' => '2026-08-05',
            'category' => 'izin',
            'reason' => 'Kelas lain',
            'status' => 'pending',
        ]);

        $alreadyProcessed = $this->createRequest(['status' => 'approved', 'reason' => 'Sudah diproses']);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.batch'), [
            'request_ids' => [$owned->id, $foreign->id, $alreadyProcessed->id],
            'decision' => 'approve',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_early_leave_requests', ['id' => $owned->id, 'status' => 'approved']);
        $this->assertDatabaseHas('student_early_leave_requests', ['id' => $foreign->id, 'status' => 'pending']);
        $this->assertDatabaseHas('student_early_leave_requests', ['id' => $alreadyProcessed->id, 'status' => 'approved']);
    }

    public function test_batch_approve_writes_final_attendance_status(): void
    {
        $request = $this->createRequest(['date' => '2026-08-03', 'date_end' => null, 'category' => 'izin']);

        $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.early-leave.batch'), [
            'request_ids' => [$request->id],
            'decision' => 'approve',
        ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'status' => 'excused',
            'source' => 'manual',
        ]);
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_resolver_flags_dispen_without_check_in_after_operational_hours(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-03 17:00:00'));

        $request = $this->createRequest([
            'date' => '2026-08-03',
            'date_end' => '2026-08-03',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => Carbon::parse('2026-08-02 09:00:00'),
        ]);

        $this->assertSame('unknown', app(AttendanceStatusResolver::class)->deriveStatus(null, $request, false, '2026-08-03'));
    }

    public function test_resolver_keeps_dispen_present_when_checked_in(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-03 17:00:00'));

        $request = $this->createRequest([
            'date' => '2026-08-03',
            'date_end' => '2026-08-03',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => Carbon::parse('2026-08-02 09:00:00'),
        ]);

        $attendance = StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => now(),
            'check_in_status' => 'on_time',
        ]);

        $this->assertSame('present', app(AttendanceStatusResolver::class)->deriveStatus($attendance, $request, false, '2026-08-03'));
    }

    public function test_resolver_keeps_future_dispen_dates_present(): void
    {
        $request = $this->createRequest([
            'date' => '2026-08-03',
            'date_end' => '2026-08-05',
            'category' => 'lomba',
            'reason' => 'Lomba multi hari',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => Carbon::parse('2026-08-02 09:00:00'),
        ]);

        $resolver = app(AttendanceStatusResolver::class);

        $this->assertSame('present', $resolver->deriveStatus(null, $request, false, '2026-08-05'));
        $this->assertSame('present', $resolver->deriveStatus(null, $request, false, '2026-08-03'));
    }

    public function test_command_flags_dispen_without_check_in_as_unknown(): void
    {
        $this->createRequest([
            'date' => '2026-08-03',
            'date_end' => '2026-08-03',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => Carbon::parse('2026-08-02 09:00:00'),
        ]);

        Carbon::setTestNow(Carbon::parse('2026-08-03 18:00:00'));

        $this->artisan('attendance:flag-dispen-without-checkin')->assertSuccessful();

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'status' => 'unknown',
            'source' => 'manual',
        ]);
    }

    public function test_command_keeps_checked_in_dispen_present(): void
    {
        $this->createRequest([
            'date' => '2026-08-03',
            'date_end' => '2026-08-03',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => Carbon::parse('2026-08-02 09:00:00'),
        ]);

        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => Carbon::parse('2026-08-03 08:00:00'),
            'check_in_status' => 'on_time',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-08-03 18:00:00'));

        $this->artisan('attendance:flag-dispen-without-checkin')->assertSuccessful();

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'status' => 'present',
            'source' => 'manual',
        ]);
    }

    public function test_wali_kelas_confirms_dispen_without_check_in(): void
    {
        $request = $this->createRequest([
            'date' => '2026-08-03',
            'date_end' => '2026-08-03',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => Carbon::parse('2026-08-02 09:00:00'),
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.dispen.confirm', $request), [
            'date' => '2026-08-03',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'check_in_status' => 'additional_activity',
            'verification_method' => 'approved_dispen_confirmation',
            'verified_by' => $this->waliKelas->id,
        ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'status' => 'present',
            'source' => 'manual',
        ]);
    }

    public function test_wali_kelas_cannot_confirm_dispen_with_existing_check_in(): void
    {
        $request = $this->createRequest([
            'date' => '2026-08-03',
            'date_end' => '2026-08-03',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => Carbon::parse('2026-08-02 09:00:00'),
        ]);

        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => now(),
            'check_in_status' => 'on_time',
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.dispen.confirm', $request), [
            'date' => '2026-08-03',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('attendances', 0);
    }
}
