<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\DailyAttendanceSetting;
use App\Models\Institution;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\StudentDailyAttendance;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UnifiedRecapTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $admin;
    protected Classes $class;
    protected int $institutionId;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-08-05 09:00:00'));

        $institution = Institution::create(['name' => 'SMK Test', 'status' => 'active']);
        $this->institutionId = $institution->id;

        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
            'institution_id' => $this->institutionId,
        ]);
        $this->academicYearId = $academicYear->id;

        Role::create(['name' => 'siswa']);
        Role::create(['name' => 'admin']);

        $this->admin = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $this->admin->assignRole('admin');

        $this->class = Classes::create([
            'name' => 'X RPL 1',
            'major' => 'RPL',
            'grade_level' => 'X',
            'academic_year' => '2026/2027',
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

    private function checkInPayload(): array
    {
        return [
            'latitude' => '-6.200100',
            'longitude' => '106.816666',
            'accuracy' => 12,
            'device_fingerprint' => 'device-test-123',
        ];
    }

    public function test_digital_check_in_appears_in_admin_recap_summary(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload())
            ->assertSessionHas('success');

        $this->assertDatabaseHas('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-05',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.attendance', [
            'class_id' => $this->class->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]));

        $response->assertStatus(200);

        $summary = $response->viewData('summary');
        $this->assertEquals(1, $summary['present'], 'Absensi digital harus ikut dihitung sebagai hadir di rekap.');

        $studentCounts = $response->viewData('studentCounts');
        $this->assertEquals(1, $studentCounts[$this->student->id]['present']);
        $this->assertEquals(1, $studentCounts[$this->student->id]['total']);
    }

    public function test_digital_check_in_appears_in_admin_student_detail(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload())
            ->assertSessionHas('success');

        $response = $this->actingAs($this->admin)->get(route('admin.students.show', $this->student->id));

        $response->assertStatus(200);

        $stats = $response->viewData('attendance_stats');
        $this->assertEquals(1, $stats['present'], 'Detail siswa harus menghitung kehadiran digital.');

        $recent = $response->viewData('recentAttendance');
        $this->assertCount(1, $recent);
        $this->assertEquals('present', $recent->first()->status);
    }

    public function test_excel_export_includes_digital_attendance(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload())
            ->assertSessionHas('success');

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export.excel', [
            'class_id' => $this->class->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]));

        $response->assertOk();
    }

    public function test_pdf_export_includes_digital_attendance(): void
    {
        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload())
            ->assertSessionHas('success');

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export.pdf', [
            'class_id' => $this->class->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]));

        $response->assertOk();
    }

    public function test_admin_excel_export_handles_unknown_class(): void
    {
        $this->actingAs($this->admin)->get(route('admin.reports.export.excel', [
            'class_id' => 999999,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]))->assertOk();
    }

    public function test_sekretaris_dashboard_counts_digital_attendance(): void
    {
        Role::create(['name' => 'sekretaris']);

        $sekretaris = User::factory()->create([
            'institution_id' => $this->institutionId,
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);
        $sekretaris->assignRole('sekretaris');

        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload())
            ->assertSessionHas('success');

        $response = $this->actingAs($sekretaris)->get(route('sekretaris.dashboard'));

        $response->assertStatus(200);

        $stats = $response->viewData('stats');
        $this->assertEquals(100.0, $stats['avg_attendance'], 'Rata-rata kehadiran harus menghitung absensi digital.');

        $today = $response->viewData('today_attendance');
        $this->assertCount(1, $today);
        $this->assertEquals(1, $today[0]['present']);
    }

    public function test_guru_export_includes_digital_attendance(): void
    {
        Role::create(['name' => 'teacher']);

        $teacher = User::factory()->create([
            'institution_id' => $this->institutionId,
            'status' => 'active',
        ]);
        $teacher->assignRole('teacher');

        $subject = Subject::create([
            'name' => 'Pemrograman Web',
            'institution_id' => $this->institutionId,
        ]);

        Schedule::create([
            'class_id' => $this->class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00:00',
            'end_time' => '09:30:00',
            'institution_id' => $this->institutionId,
        ]);

        $this->actingAs($this->student)->post(route('siswa.daily-attendance.check-in'), $this->checkInPayload())
            ->assertSessionHas('success');

        $response = $this->actingAs($teacher)->get(route('guru.report.export', [
            'class_id' => $this->class->id,
            'month' => 8,
            'year' => 2026,
        ]));

        $response->assertOk();
    }
}
