<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Institution;
use App\Models\TeacherStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GuruDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;

    protected User $otherTeacher;

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

        Role::create(['name' => 'teacher']);

        $this->teacher = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $this->teacher->assignRole('teacher');

        $this->otherTeacher = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $this->otherTeacher->assignRole('teacher');
    }

    public function test_dashboard_shows_monthly_izin_tugas_luar_summary(): void
    {
        // 3 hari izin approved (multi-hari) bulan ini
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'approved',
            'date' => '2026-08-03',
            'date_end' => '2026-08-05',
            'institution_id' => $this->institutionId,
        ]);
        // 1 hari tugas luar approved bulan ini
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'tugas_luar',
            'status' => 'approved',
            'date' => '2026-08-12',
            'institution_id' => $this->institutionId,
        ]);
        // 1 pending (tidak dihitung sebagai hari, hanya badge pending)
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-15',
            'institution_id' => $this->institutionId,
        ]);
        // Record guru lain — TIDAK boleh masuk rekap guru ini
        TeacherStatus::create([
            'teacher_id' => $this->otherTeacher->id,
            'type' => 'tugas_luar',
            'status' => 'approved',
            'date' => '2026-08-04',
            'date_end' => '2026-08-06',
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->teacher)->get(route('guru.dashboard'));

        $response->assertOk();

        $summary = $response->viewData('teacherStatusSummary');
        $this->assertEquals(3, $summary['izin_days']);
        $this->assertEquals(1, $summary['tugas_days']);
        $this->assertEquals(4, $summary['total_days']);
        $this->assertEquals(2, $summary['approved']);
        $this->assertEquals(1, $summary['pending']);

        // Kartu rekap dirender di halaman
        $response->assertSee('Rekap Izin / Sakit / Tugas Luar', false);
        $response->assertSee('Tugas Luar (hari)', false);
        $response->assertSee('1 pengajuan menunggu persetujuan wakasek', false);
    }

    public function test_dashboard_summary_is_zero_when_no_records(): void
    {
        $response = $this->actingAs($this->teacher)->get(route('guru.dashboard'));

        $response->assertOk();

        $summary = $response->viewData('teacherStatusSummary');
        $this->assertEquals(0, $summary['izin_days']);
        $this->assertEquals(0, $summary['tugas_days']);
        $this->assertEquals(0, $summary['total_days']);
        $this->assertEquals(0, $summary['approved']);
        $this->assertEquals(0, $summary['pending']);
    }
}
