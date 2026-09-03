<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\DailyAttendanceSetting;
use App\Models\Institution;
use App\Models\TeacherStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherStatusWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;

    protected User $wakasek;

    protected User $otherTeacher;

    protected User $admin;

    protected int $institutionId;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-08-10 09:00:00'));

        $institution = Institution::create(['name' => 'SMK Test', 'status' => 'active']);
        $this->institutionId = $institution->id;

        AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
            'institution_id' => $this->institutionId,
        ]);

        foreach (['teacher', 'wakasek', 'admin'] as $roleName) {
            Role::create(['name' => $roleName]);
        }

        $this->teacher = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $this->teacher->assignRole('teacher');

        $this->wakasek = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $this->wakasek->assignRole('wakasek');

        $this->otherTeacher = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $this->otherTeacher->assignRole('teacher');

        $this->admin = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $this->admin->assignRole('admin');
    }

    // ------------------------------------------------------------------
    // Guru: store
    // ------------------------------------------------------------------

    public function test_guru_submits_izin_and_it_becomes_pending(): void
    {
        $response = $this->actingAs($this->teacher)->post(route('guru.teacher-status.store'), [
            'type' => 'izin',
            'date' => '2026-08-11',
            'note' => 'Rapat dinas',
            'attachment' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('teacher_statuses', [
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-11',
            'institution_id' => $this->institutionId,
        ]);
    }

    public function test_guru_submits_multi_day_tugas_luar(): void
    {
        $response = $this->actingAs($this->teacher)->post(route('guru.teacher-status.store'), [
            'type' => 'tugas_luar',
            'date' => '2026-08-11',
            'date_end' => '2026-08-13',
            'attachment' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('teacher_statuses', [
            'teacher_id' => $this->teacher->id,
            'type' => 'tugas_luar',
            'status' => 'pending',
            'date' => '2026-08-11',
            'date_end' => '2026-08-13',
        ]);
    }

    public function test_guru_submits_sakit_and_it_becomes_pending(): void
    {
        $response = $this->actingAs($this->teacher)->post(route('guru.teacher-status.store'), [
            'type' => 'sakit',
            'date' => '2026-08-11',
            'note' => 'Demam',
            'attachment' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('teacher_statuses', [
            'teacher_id' => $this->teacher->id,
            'type' => 'sakit',
            'status' => 'pending',
            'date' => '2026-08-11',
            'institution_id' => $this->institutionId,
        ]);
    }

    public function test_guru_cannot_submit_past_date(): void
    {
        $response = $this->actingAs($this->teacher)->post(route('guru.teacher-status.store'), [
            'type' => 'izin',
            'date' => '2026-08-09', // kemarin
        ]);

        $response->assertSessionHasErrors('date');
        $this->assertDatabaseCount('teacher_statuses', 0);
    }

    public function test_guru_cannot_submit_overlapping_request(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'approved',
            'date' => '2026-08-11',
            'date_end' => '2026-08-13',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->teacher)->post(route('guru.teacher-status.store'), [
            'type' => 'tugas_luar',
            'date' => '2026-08-12',
            'date_end' => '2026-08-14',
            'attachment' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('date');
        $this->assertDatabaseCount('teacher_statuses', 1);
    }

    public function test_guru_can_resubmit_after_rejected_or_cancelled(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'cancelled',
            'date' => '2026-08-11',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->teacher)->post(route('guru.teacher-status.store'), [
            'type' => 'izin',
            'date' => '2026-08-11',
            'attachment' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseCount('teacher_statuses', 2);
    }

    public function test_guru_can_upload_attachment(): void
    {
        $response = $this->actingAs($this->teacher)->post(route('guru.teacher-status.store'), [
            'type' => 'izin',
            'date' => '2026-08-11',
            'attachment' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('teacher_statuses', [
            'teacher_id' => $this->teacher->id,
            'status' => 'pending',
        ]);
        $this->assertNotNull(TeacherStatus::first()->attachment);
    }

    public function test_guru_cannot_upload_invalid_mime(): void
    {
        $response = $this->actingAs($this->teacher)->post(route('guru.teacher-status.store'), [
            'type' => 'izin',
            'date' => '2026-08-11',
            'attachment' => UploadedFile::fake()->create('surat.exe', 100, 'application/x-msdownload'),
        ]);

        $response->assertSessionHasErrors('attachment');
        $this->assertDatabaseCount('teacher_statuses', 0);
    }

    public function test_guru_cannot_submit_without_attachment(): void
    {
        $response = $this->actingAs($this->teacher)->post(route('guru.teacher-status.store'), [
            'type' => 'tugas_luar',
            'date' => '2026-08-11',
        ]);

        $response->assertSessionHasErrors('attachment');
        $this->assertDatabaseCount('teacher_statuses', 0);
    }

    public function test_guru_cannot_update_without_attachment_when_none_exists(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'sakit',
            'status' => 'pending',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->teacher)->put(route('guru.teacher-status.update', $status), [
            'type' => 'sakit',
            'date' => '2026-08-12',
        ]);

        $response->assertSessionHasErrors('attachment');
        $this->assertDatabaseHas('teacher_statuses', [
            'id' => $status->id,
            'attachment' => null,
        ]);
    }

    // ------------------------------------------------------------------
    // Guru: withdraw
    // ------------------------------------------------------------------

    public function test_guru_withdraws_pending_request(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->teacher)->post(route('guru.teacher-status.withdraw', $status));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('teacher_statuses', [
            'id' => $status->id,
            'status' => 'cancelled',
            'cancelled_by' => $this->teacher->id,
        ]);
    }

    public function test_guru_cannot_withdraw_approved_request(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'approved',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->teacher)->post(route('guru.teacher-status.withdraw', $status));

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('teacher_statuses', ['id' => $status->id, 'status' => 'approved']);
    }

    public function test_guru_cannot_withdraw_another_teachers_request(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->otherTeacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->teacher)->post(route('guru.teacher-status.withdraw', $status));

        $response->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Guru: edit
    // ------------------------------------------------------------------

    public function test_guru_can_open_edit_page_for_pending_request(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-12',
            'note' => 'Rapat dinas',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->teacher)->get(route('guru.teacher-status.edit', $status));

        $response->assertOk();
        $response->assertSee('Edit Pengajuan');
        $response->assertSee('Rapat dinas');
    }

    public function test_guru_can_update_pending_request(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->teacher)->put(route('guru.teacher-status.update', $status), [
            'type' => 'tugas_luar',
            'date' => '2026-08-13',
            'date_end' => '2026-08-14',
            'note' => 'Dinas luar kota',
            'attachment' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('teacher_statuses', [
            'id' => $status->id,
            'type' => 'tugas_luar',
            'status' => 'pending',
            'date' => '2026-08-13',
            'date_end' => '2026-08-14',
            'note' => 'Dinas luar kota',
        ]);
    }

    public function test_guru_cannot_update_approved_request(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'approved',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->teacher)->put(route('guru.teacher-status.update', $status), [
            'type' => 'izin',
            'date' => '2026-08-13',
            'attachment' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('teacher_statuses', [
            'id' => $status->id,
            'status' => 'approved',
            'date' => '2026-08-12',
        ]);
    }

    public function test_edit_page_redirects_for_non_pending_request(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'rejected',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->teacher)->get(route('guru.teacher-status.edit', $status));

        $response->assertRedirect(route('guru.teacher-status.show', $status));
        $response->assertSessionHas('error');
    }

    public function test_edit_page_redirects_when_date_has_passed(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-09', // kemarin (hari ini 10-08-2026)
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->teacher)->get(route('guru.teacher-status.edit', $status));

        $response->assertRedirect(route('guru.teacher-status.show', $status));
        $response->assertSessionHas('error');
    }

    public function test_guru_cannot_edit_another_teachers_request(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->otherTeacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->teacher)->get(route('guru.teacher-status.edit', $status));

        $response->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Wakasek: approve / reject / cancel
    // ------------------------------------------------------------------

    public function test_wakasek_approves_pending_request(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->wakasek)->post(route('wakasek.teacher-status.approve', $status));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('teacher_statuses', [
            'id' => $status->id,
            'status' => 'approved',
            'approver_id' => $this->wakasek->id,
        ]);
        $this->assertNotNull(TeacherStatus::find($status->id)->processed_at);
    }

    public function test_wakasek_approves_with_substitute_teacher(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'tugas_luar',
            'status' => 'pending',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->wakasek)->post(route('wakasek.teacher-status.approve', $status), [
            'substitute_teacher_id' => $this->otherTeacher->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('teacher_statuses', [
            'id' => $status->id,
            'status' => 'approved',
            'substitute_teacher_id' => $this->otherTeacher->id,
        ]);
    }

    public function test_wakasek_cannot_approve_with_invalid_substitute(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        // Guru pengganti = guru yang bersangkutan → tidak valid
        $response = $this->actingAs($this->wakasek)->post(route('wakasek.teacher-status.approve', $status), [
            'substitute_teacher_id' => $this->teacher->id,
        ]);

        $response->assertSessionHasErrors('substitute_teacher_id');
        $this->assertDatabaseHas('teacher_statuses', ['id' => $status->id, 'status' => 'pending']);
    }

    public function test_wakasek_rejects_request_with_reason(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->wakasek)->post(route('wakasek.teacher-status.reject', $status), [
            'rejection_reason' => 'Jadwal padat',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('teacher_statuses', [
            'id' => $status->id,
            'status' => 'rejected',
            'rejection_reason' => 'Jadwal padat',
        ]);
    }

    public function test_wakasek_cannot_reject_without_reason(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->wakasek)->post(route('wakasek.teacher-status.reject', $status));

        $response->assertSessionHasErrors('rejection_reason');
        $this->assertDatabaseHas('teacher_statuses', ['id' => $status->id, 'status' => 'pending']);
    }

    public function test_wakasek_cancels_approved_request(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'approved',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->wakasek)->post(route('wakasek.teacher-status.cancel', $status), [
            'cancellation_reason' => 'Guru batal izin',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('teacher_statuses', [
            'id' => $status->id,
            'status' => 'cancelled',
            'cancelled_by' => $this->wakasek->id,
        ]);
    }

    public function test_wakasek_cannot_cancel_pending_request(): void
    {
        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->wakasek)->post(route('wakasek.teacher-status.cancel', $status), [
            'cancellation_reason' => 'tes',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('teacher_statuses', ['id' => $status->id, 'status' => 'pending']);
    }

    // ------------------------------------------------------------------
    // Monitoring integration (approved only + multi-day)
    // ------------------------------------------------------------------

    public function test_monitoring_only_counts_approved_statuses(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-10',
            'institution_id' => $this->institutionId,
        ]);

        $teacherStatuses = TeacherStatus::query()
            ->where('status', 'approved')
            ->where('date', '<=', '2026-08-10')
            ->where(function ($q) {
                $q->whereNull('date_end')->orWhere('date_end', '>=', '2026-08-10');
            })
            ->get()
            ->keyBy('teacher_id');

        $this->assertArrayNotHasKey($this->teacher->id, $teacherStatuses->all());

        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'approved',
            'date' => '2026-08-10',
            'institution_id' => $this->institutionId,
        ]);

        $teacherStatuses = TeacherStatus::query()
            ->where('status', 'approved')
            ->where('date', '<=', '2026-08-10')
            ->where(function ($q) {
                $q->whereNull('date_end')->orWhere('date_end', '>=', '2026-08-10');
            })
            ->get()
            ->keyBy('teacher_id');

        $this->assertArrayHasKey($this->teacher->id, $teacherStatuses->all());
    }

    public function test_monitoring_multi_day_approved_covers_middle_days(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'tugas_luar',
            'status' => 'approved',
            'date' => '2026-08-10',
            'date_end' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        foreach (['2026-08-10', '2026-08-11', '2026-08-12'] as $date) {
            $found = TeacherStatus::query()
                ->where('status', 'approved')
                ->where('date', '<=', $date)
                ->where(function ($q) use ($date) {
                    $q->whereNull('date_end')->orWhere('date_end', '>=', $date);
                })
                ->exists();

            $this->assertTrue($found, "Harus ketemu pada {$date}");
        }

        // Di luar rentang → tidak ketemu
        $found = TeacherStatus::query()
            ->where('status', 'approved')
            ->where('date', '<=', '2026-08-13')
            ->where(function ($q) {
                $q->whereNull('date_end')->orWhere('date_end', '>=', '2026-08-13');
            })
            ->exists();

        $this->assertFalse($found);
    }

    // ------------------------------------------------------------------
    // Expire otomatis
    // ------------------------------------------------------------------

    public function test_expire_pending_marks_past_requests_as_cancelled(): void
    {
        $past = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-08',
            'institution_id' => $this->institutionId,
        ]);

        $future = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'tugas_luar',
            'status' => 'pending',
            'date' => '2026-08-15',
            'institution_id' => $this->institutionId,
        ]);

        $this->artisan('teacher-status:expire-pending')->assertSuccessful();

        $this->assertDatabaseHas('teacher_statuses', [
            'id' => $past->id,
            'status' => 'cancelled',
            'cancelled_by' => null,
        ]);
        $this->assertDatabaseHas('teacher_statuses', ['id' => $future->id, 'status' => 'pending']);
    }

    // ------------------------------------------------------------------
    // Notifikasi WhatsApp (multi-wakasek + dedupe)
    // ------------------------------------------------------------------

    public function test_submission_creates_one_log_per_wakasek(): void
    {
        $this->secondWakasek = User::factory()->create([
            'institution_id' => $this->institutionId,
            'status' => 'active',
            'phone' => '081200000002',
        ]);
        $this->secondWakasek->assignRole('wakasek');
        $this->wakasek->update(['phone' => '081200000001']);

        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
        ]);

        $response = $this->actingAs($this->teacher)->post(route('guru.teacher-status.store'), [
            'type' => 'izin',
            'date' => '2026-08-11',
            'attachment' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseCount('whatsapp_notification_logs', 2);
        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'event_type' => 'teacher_status_submitted',
            'recipient_phone' => '081200000001',
        ]);
        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'event_type' => 'teacher_status_submitted',
            'recipient_phone' => '081200000002',
        ]);
    }

    public function test_update_creates_updated_notification_per_wakasek_and_dedupes(): void
    {
        $this->secondWakasek = User::factory()->create([
            'institution_id' => $this->institutionId,
            'status' => 'active',
            'phone' => '081200000002',
        ]);
        $this->secondWakasek->assignRole('wakasek');
        $this->wakasek->update(['phone' => '081200000001']);

        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
        ]);

        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        // Edit pertama → log `teacher_status_updated` untuk semua wakasek
        $this->actingAs($this->teacher)->put(route('guru.teacher-status.update', $status), [
            'type' => 'izin',
            'date' => '2026-08-13',
            'note' => 'Perubahan pertama',
            'attachment' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ]);

        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'teacher_status_id' => $status->id,
            'event_type' => 'teacher_status_updated',
            'recipient_phone' => '081200000001',
        ]);
        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'teacher_status_id' => $status->id,
            'event_type' => 'teacher_status_updated',
            'recipient_phone' => '081200000002',
        ]);
        $this->assertDatabaseCount('whatsapp_notification_logs', 2);

        // Edit kedua → tidak duplikat (dedupe by teacher_status_id+event+phone)
        $this->actingAs($this->teacher)->put(route('guru.teacher-status.update', $status), [
            'type' => 'tugas_luar',
            'date' => '2026-08-14',
            'note' => 'Perubahan kedua',
            'attachment' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ]);

        $this->assertDatabaseCount('whatsapp_notification_logs', 2);
    }

    public function test_approve_creates_single_notification_for_teacher(): void
    {
        $this->teacher->update(['phone' => '081300000001']);

        DailyAttendanceSetting::forInstitution($this->institutionId)->update([
            'whatsapp_enabled' => true,
        ]);

        $status = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);

        $this->actingAs($this->wakasek)->post(route('wakasek.teacher-status.approve', $status));

        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'teacher_status_id' => $status->id,
            'event_type' => 'teacher_status_approved',
            'recipient_phone' => '081300000001',
        ]);
        $this->assertDatabaseCount('whatsapp_notification_logs', 1);
    }

    // ------------------------------------------------------------------
    // Akses & halaman
    // ------------------------------------------------------------------

    public function test_guru_index_renders_status_badges_and_actions(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'approved',
            'date' => '2026-08-11',
            'institution_id' => $this->institutionId,
        ]);
        $rejected = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'tugas_luar',
            'status' => 'rejected',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);
        $pending = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-15',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->teacher)->get(route('guru.teacher-status.index'));

        $response->assertOk();
        $response->assertSee('Disetujui', false);
        $response->assertSee('Ditolak', false);
        $response->assertSee('Menunggu', false);

        // Tombol Edit & Tarik hanya muncul untuk pending
        $response->assertSee(route('guru.teacher-status.edit', $pending), false);
        $response->assertDontSee(route('guru.teacher-status.edit', $rejected), false);
    }

    public function test_guru_show_renders_approver_and_rejection_reason(): void
    {
        $approved = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'approved',
            'date' => '2026-08-11',
            'approver_id' => $this->wakasek->id,
            'processed_at' => '2026-08-11 08:00:00',
            'institution_id' => $this->institutionId,
        ]);
        $rejected = TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'tugas_luar',
            'status' => 'rejected',
            'date' => '2026-08-12',
            'approver_id' => $this->wakasek->id,
            'rejection_reason' => 'Jadwal padat',
            'institution_id' => $this->institutionId,
        ]);

        $this->actingAs($this->teacher)->get(route('guru.teacher-status.show', $approved))
            ->assertOk()
            ->assertSee('Disetujui oleh', false)
            ->assertSee($this->wakasek->name);

        $this->actingAs($this->teacher)->get(route('guru.teacher-status.show', $rejected))
            ->assertOk()
            ->assertSee('Alasan penolakan', false)
            ->assertSee('Jadwal padat');
    }

    public function test_teacher_status_index_shows_only_own_records(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'approved',
            'date' => '2026-08-11',
            'institution_id' => $this->institutionId,
        ]);
        TeacherStatus::create([
            'teacher_id' => $this->otherTeacher->id,
            'type' => 'tugas_luar',
            'status' => 'approved',
            'date' => '2026-08-11',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->teacher)->get(route('guru.teacher-status.index'));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('statuses')->total());
    }

    public function test_wakasek_index_shows_pending_tab(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-11',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->wakasek)->get(route('wakasek.teacher-status.index'));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('statuses')->total());
    }

    public function test_report_counts_approved_days_including_multiday(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'approved',
            'date' => '2026-08-03',
            'date_end' => '2026-08-05',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->wakasek)->get(route('wakasek.teacher-status.report', ['month' => 8, 'year' => 2026]));

        $response->assertOk();

        $rows = $response->viewData('rows');
        $this->assertCount(1, $rows);
        $this->assertEquals(3, $rows->first()['izin_days']);
        $this->assertEquals(3, $rows->first()['total_days']);
    }

    // ------------------------------------------------------------------
    // Ekspor rekap CSV / PDF
    // ------------------------------------------------------------------

    public function test_report_exports_csv(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'approved',
            'date' => '2026-08-03',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->wakasek)->get(route('wakasek.teacher-status.report.csv', ['month' => 8, 'year' => 2026]));

        $response->assertOk();
        $this->assertStringContainsString('rekap-izin-tugas-luar-2026-08.csv', $response->headers->get('content-disposition'));

        // Isi file: harus memuat nama guru, header & baris TOTAL (BinaryFileResponse → streamedContent)
        $csv = $response->streamedContent();
        $this->assertStringContainsString($this->teacher->name, $csv);
        $this->assertStringContainsString('Nama Guru', $csv);
        $this->assertStringContainsString('TOTAL', $csv);
    }

    public function test_report_exports_pdf(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'tugas_luar',
            'status' => 'approved',
            'date' => '2026-08-04',
            'date_end' => '2026-08-06',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->wakasek)->get(route('wakasek.teacher-status.report.pdf', ['month' => 8, 'year' => 2026]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('rekap-izin-tugas-luar-2026-08.pdf', $response->headers->get('content-disposition'));

        // Konten biner PDF diawali magic bytes %PDF (teks terkompresi, tidak bisa dicek langsung)
        $this->assertStringStartsWith('%PDF', $response->getContent());

        // Verifikasi isi lewat render view: nama guru & angka tugas luar (3 hari) muncul
        $html = view('teacher-status.report-pdf', [
            'rows' => collect([[
                'teacher' => $this->teacher,
                'izin_days' => 0,
                'sakit_days' => 0,
                'tugas_days' => 3,
                'total_days' => 3,
            ]]),
            'month' => 8,
            'year' => 2026,
            'monthName' => 'Agustus',
            'totalIzin' => 0,
            'totalSakit' => 0,
            'totalTugas' => 3,
            'totalDays' => 3,
        ])->render();

        $this->assertStringContainsString($this->teacher->name, $html);
        $this->assertStringContainsString('Tugas Luar (hari)', $html);
        $this->assertStringContainsString('REKAP IZIN / SAKIT / TUGAS LUAR GURU', $html);
    }

    public function test_report_exports_require_wakasek_role(): void
    {
        $response = $this->actingAs($this->teacher)->get(route('wakasek.teacher-status.report.csv', ['month' => 8, 'year' => 2026]));

        $response->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Report admin (desain §8)
    // ------------------------------------------------------------------

    public function test_admin_can_access_monthly_report(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'approved',
            'date' => '2026-08-03',
            'date_end' => '2026-08-05',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.teacher-status.report', ['month' => 8, 'year' => 2026]));

        $response->assertOk();
        $rows = $response->viewData('rows');
        $this->assertCount(1, $rows);
        $this->assertEquals(3, $rows->first()['izin_days']);
        $this->assertEquals(3, $rows->first()['total_days']);
        $response->assertSee($this->teacher->name, false);
    }

    public function test_admin_report_exports_csv(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'tugas_luar',
            'status' => 'approved',
            'date' => '2026-08-04',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.teacher-status.report.csv', ['month' => 8, 'year' => 2026]));

        $response->assertOk();
        $this->assertStringContainsString('rekap-izin-tugas-luar-2026-08.csv', $response->headers->get('content-disposition'));
        $this->assertStringContainsString($this->teacher->name, $response->streamedContent());
    }

    public function test_admin_report_requires_admin_role(): void
    {
        $response = $this->actingAs($this->wakasek)->get(route('admin.teacher-status.report'));
        $response->assertForbidden();

        $response = $this->actingAs($this->teacher)->get(route('admin.teacher-status.report'));
        $response->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Kompresi lampiran gambar (desain §7.6 → ImageCompressor)
    // ------------------------------------------------------------------

    public function test_image_attachment_is_compressed_when_stored(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD tidak tersedia di environment.');
        }

        // Buat gambar JPEG asli berukuran besar (noise → sulit dikompres lossless)
        $img = imagecreatetruecolor(800, 600);
        for ($x = 0; $x < 800; $x++) {
            for ($y = 0; $y < 600; $y++) {
                imagesetpixel($img, $x, $y, imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255)));
            }
        }
        $tmp = tempnam(sys_get_temp_dir(), 'surat').'.jpg';
        imagejpeg($img, $tmp, 100);
        imagedestroy($img);
        $originalSize = filesize($tmp);

        $upload = new UploadedFile($tmp, 'surat.jpg', 'image/jpeg', null, true);

        $response = $this->actingAs($this->teacher)->post(route('guru.teacher-status.store'), [
            'type' => 'izin',
            'date' => '2026-08-11',
            'attachment' => $upload,
        ]);

        $response->assertSessionHas('success');

        $status = TeacherStatus::first();
        $this->assertNotNull($status->attachment);

        $storedPath = storage_path('app/public/'.$status->attachment);
        $this->assertFileExists($storedPath);
        // quality 60 → ukuran harus lebih kecil dari asli (quality 100)
        $this->assertLessThan($originalSize, filesize($storedPath));

        @unlink($tmp);
    }
}
