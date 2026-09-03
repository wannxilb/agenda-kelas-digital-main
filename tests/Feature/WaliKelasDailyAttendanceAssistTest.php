<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\DailyAttendanceSetting;
use App\Models\Institution;
use App\Models\Setting;
use App\Models\StudentDailyAttendance;
use App\Models\StudentEarlyLeaveRequest;
use App\Models\User;
use App\Services\DailyAttendancePresensiSync;
use App\Traits\ResolvesWaliKelasContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WaliKelasDailyAttendanceAssistTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private User $waliKelas;

    private Classes $class;

    private int $institutionId;

    protected function setUp(): void
    {
        parent::setUp();

        $reflection = new \ReflectionClass(ResolvesWaliKelasContext::class);
        $property = $reflection->getProperty('cachedActiveAcademicYear');
        $property->setAccessible(true);
        $property->setValue(null, null);

        Carbon::setTestNow(Carbon::parse('2026-08-03 07:30:00'));

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
            'check_in_start' => '08:00:00',
            'check_in_deadline' => '10:00:00',
            'check_out_start' => '15:00:00',
            'check_out_tolerance_minutes' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_wali_kelas_gets_notification_when_assisting_check_in_before_opening_time(): void
    {
        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.assist'), $this->assistPayload());

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Absensi masuk baru dibuka pukul 08:00.');
        $this->assertDatabaseMissing('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
        ]);
    }

    public function test_wali_kelas_gets_notification_when_assisting_check_out_before_opening_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-03 14:30:00'));

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.assist-checkout'), $this->assistPayload());

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Absensi pulang baru dibuka pukul 15:00.');
        $this->assertDatabaseMissing('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
        ]);
    }

    public function test_wali_kelas_cannot_assist_check_in_when_full_day_absence_is_approved(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-03 08:30:00'));

        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'sakit',
            'reason' => 'Sakit dan izin tidak hadir.',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.assist'), $this->assistPayload());

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Siswa memiliki pengajuan ketidakhadiran yang disetujui untuk hari ini, sehingga wali kelas tidak dapat membantu absensi masuk.');
        $this->assertDatabaseMissing('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
        ]);
    }

    public function test_wali_kelas_cannot_assist_check_out_when_full_day_absence_is_approved(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-03 15:00:00'));

        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'date_end' => '2026-08-04',
            'category' => 'izin_lainnya',
            'reason' => 'Izin keluarga.',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.assist-checkout'), $this->assistPayload());

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Siswa memiliki pengajuan ketidakhadiran yang disetujui untuk hari ini, sehingga wali kelas tidak dapat membantu absensi pulang.');
        $this->assertDatabaseMissing('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
        ]);
    }

    public function test_rejected_check_in_verification_updates_final_attendance_as_alpha(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-03 11:15:00'));

        $attendance = StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => Carbon::parse('2026-08-03 11:10:00'),
            'check_in_status' => 'needs_verification',
            'late_minutes' => 70,
        ]);

        app(DailyAttendancePresensiSync::class)->sync($attendance);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'status' => 'late',
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.verify', $attendance), [
            'decision' => 'reject',
            'verification_note' => 'Tidak valid.',
        ]);

        $response->assertSessionHas('success', 'Absensi masuk ditolak.');
        $this->assertDatabaseHas('student_daily_attendances', [
            'id' => $attendance->id,
            'check_in_status' => 'teacher_rejected',
        ]);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'status' => 'absent',
            'check_in_time' => null,
            'note' => 'Tidak valid.',
        ]);
    }

    public function test_wali_kelas_can_override_alpha_to_teacher_verified(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-03 11:15:00'));

        $attendance = StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => Carbon::parse('2026-08-03 11:10:00'),
            'check_in_status' => 'alpha',
            'late_minutes' => 70,
        ]);

        app(DailyAttendancePresensiSync::class)->sync($attendance);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'status' => 'absent',
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.verify', $attendance), [
            'decision' => 'approve',
            'verification_note' => 'Siswa kesulitan transportasi.',
        ]);

        $response->assertSessionHas('success', 'Absensi masuk disetujui.');
        $this->assertDatabaseHas('student_daily_attendances', [
            'id' => $attendance->id,
            'check_in_status' => 'teacher_verified',
            'overridden_by' => $this->waliKelas->id,
        ]);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'status' => 'late',
            'check_in_time' => '11:10:00',
            'note' => 'Siswa kesulitan transportasi.',
        ]);
    }

    public function test_wali_kelas_cannot_reject_alpha_student(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-03 11:15:00'));

        $attendance = StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => Carbon::parse('2026-08-03 11:10:00'),
            'check_in_status' => 'alpha',
            'late_minutes' => 70,
        ]);

        $response = $this->actingAs($this->waliKelas)->post(route('wali-kelas.daily-attendance.verify', $attendance), [
            'decision' => 'reject',
        ]);

        $response->assertSessionHas('error', 'Siswa sudah berstatus alpha. Penolakan tidak diperlukan.');
        $this->assertDatabaseHas('student_daily_attendances', [
            'id' => $attendance->id,
            'check_in_status' => 'alpha',
        ]);
    }

    public function test_wali_kelas_attendance_today_shows_approved_full_day_absence(): void
    {
        Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'date' => '2026-08-03',
            'status' => 'absent',
            'academic_year_id' => AcademicYear::first()->id,
            'institution_id' => $this->institutionId,
        ]);

        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'sakit',
            'reason' => 'Demam tinggi dan istirahat di rumah.',
            'status' => 'approved',
            'reviewed_by' => $this->waliKelas->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($this->waliKelas)->get(route('wali-kelas.attendance.index', [
            'date' => '2026-08-03',
        ]));

        $response->assertOk();
        $response->assertViewHas('resolvedStatuses', fn (array $statuses) => ($statuses[$this->student->id] ?? null) === 'sick');
        $response->assertSeeText('Sakit');
        $response->assertSeeText('Ketidakhadiran disetujui');
        $response->assertSeeText('Demam tinggi dan istirahat di rumah.');
        $response->assertDontSeeText('Bantu Absen');
        $response->assertDontSeeText('Bantu pulang');
    }

    public function test_wali_kelas_attendance_search_filters_students_case_insensitively(): void
    {
        $this->student->update([
            'name' => 'Budi Wali Unik',
            'nis' => 'WK-SEARCH-001',
        ]);

        $otherStudent = User::factory()->create([
            'name' => 'Andi Pembanding Wali',
            'nis' => 'WK-SEARCH-002',
            'institution_id' => $this->institutionId,
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);
        $otherStudent->assignRole('siswa');

        $this->actingAs($this->waliKelas)
            ->get(route('wali-kelas.attendance.index', [
                'date' => '2026-08-03',
                'search' => 'BUDI',
            ]))
            ->assertOk()
            ->assertSeeText('Budi Wali Unik')
            ->assertDontSeeText('Andi Pembanding Wali');
    }

    public function test_wali_kelas_attendance_status_chip_filters_students(): void
    {
        $this->student->update([
            'name' => 'Budi Hadir Wali',
            'nis' => 'WK-STATUS-001',
        ]);

        $otherStudent = User::factory()->create([
            'name' => 'Andi Alpha Wali',
            'nis' => 'WK-STATUS-002',
            'institution_id' => $this->institutionId,
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);
        $otherStudent->assignRole('siswa');

        Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'date' => '2026-08-03',
            'status' => 'present',
            'check_in_time' => '08:05:00',
            'academic_year_id' => AcademicYear::first()->id,
            'institution_id' => $this->institutionId,
        ]);

        Attendance::create([
            'student_id' => $otherStudent->id,
            'class_id' => $this->class->id,
            'date' => '2026-08-03',
            'status' => 'absent',
            'academic_year_id' => AcademicYear::first()->id,
            'institution_id' => $this->institutionId,
        ]);

        $this->actingAs($this->waliKelas)
            ->get(route('wali-kelas.attendance.index', [
                'date' => '2026-08-03',
                'status' => 'present',
            ]))
            ->assertOk()
            ->assertSeeText('Budi Hadir Wali')
            ->assertDontSeeText('Andi Alpha Wali');
    }

    public function test_wali_kelas_attendance_report_search_filters_students_case_insensitively(): void
    {
        $this->student->update([
            'name' => 'Budi Laporan Wali',
            'nis' => 'WK-REPORT-001',
        ]);

        $otherStudent = User::factory()->create([
            'name' => 'Andi Laporan Pembanding',
            'nis' => 'WK-REPORT-002',
            'institution_id' => $this->institutionId,
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);
        $otherStudent->assignRole('siswa');

        $this->actingAs($this->waliKelas)
            ->get(route('wali-kelas.attendance.report', ['search' => 'BUDI']))
            ->assertOk()
            ->assertSeeText('Budi Laporan Wali')
            ->assertDontSeeText('Andi Laporan Pembanding');
    }

    public function test_wali_kelas_attendance_report_includes_late_presensi_without_academic_year_id(): void
    {
        $this->student->update([
            'name' => 'Budi Telat Wali',
            'nis' => 'WK-LATE-001',
        ]);

        $attendance = Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'date' => '2026-08-03',
            'status' => 'late',
            'check_in_time' => '08:20:00',
            'academic_year_id' => AcademicYear::first()->id,
            'institution_id' => $this->institutionId,
        ]);
        $attendance->forceFill(['academic_year_id' => null])->save();

        $response = $this->actingAs($this->waliKelas)->get(route('wali-kelas.attendance.report', [
            'start_date' => '2026-08-03',
            'end_date' => '2026-08-03',
        ]));

        $response->assertOk();
        $response->assertViewHas('summary', fn (array $summary) => $summary['late'] === 1);
        $response->assertViewHas('studentCounts', fn (array $counts) => ($counts[$this->student->id]['late'] ?? 0) === 1);
    }

    public function test_wali_kelas_attendance_report_default_range_includes_current_presensi_even_if_academic_year_dates_are_old(): void
    {
        AcademicYear::query()->update([
            'start_date' => '2023-07-01',
            'end_date' => '2023-12-31',
        ]);

        Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'date' => '2026-08-03',
            'status' => 'late',
            'check_in_time' => '08:20:00',
            'academic_year_id' => AcademicYear::first()->id,
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->waliKelas)->get(route('wali-kelas.attendance.report'));

        $response->assertOk();
        $response->assertViewHas('summary', fn (array $summary) => $summary['late'] === 1);
    }

    public function test_wali_kelas_attendance_report_counts_missing_students_as_alpha_on_active_dates_after_deadline(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-03 09:00:00'));

        $otherStudent = User::factory()->create([
            'name' => 'Andi Tanpa Presensi',
            'nis' => 'WK-ALPHA-002',
            'institution_id' => $this->institutionId,
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);
        $otherStudent->assignRole('siswa');

        Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'date' => '2026-08-03',
            'status' => 'late',
            'check_in_time' => '08:20:00',
            'academic_year_id' => AcademicYear::first()->id,
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->waliKelas)->get(route('wali-kelas.attendance.report', [
            'start_date' => '2026-08-03',
            'end_date' => '2026-08-03',
        ]));

        $response->assertOk();
        $response->assertViewHas('summary', fn (array $summary) => $summary['late'] === 1 && $summary['absent'] === 1);
        $response->assertViewHas('studentCounts', fn (array $counts) => ($counts[$otherStudent->id]['absent'] ?? 0) === 1);
    }

    private function assistPayload(): array
    {
        return [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'verification_note' => 'Siswa didampingi wali kelas.',
            'latitude' => '-6.200100',
            'longitude' => '106.816666',
            'accuracy' => 12,
        ];
    }
}
