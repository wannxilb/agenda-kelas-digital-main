<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Institution;
use App\Models\StudentDailyAttendance;
use App\Models\User;
use App\Services\AttendanceSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceSummaryServiceTest extends TestCase
{
    use RefreshDatabase;

    private AttendanceSummaryService $service;
    private int $institutionId;
    private Classes $class;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AttendanceSummaryService::class);

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
        $waliKelas = User::factory()->create([
            'institution_id' => $this->institutionId,
            'status' => 'active',
        ]);
        $waliKelas->assignRole('wali_kelas');

        $this->class = Classes::create([
            'name' => 'X RPL 1',
            'major' => 'RPL',
            'grade_level' => 'X',
            'academic_year' => '2026/2027',
            'homeroom_teacher_id' => $waliKelas->id,
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
    }

    public function test_resolve_for_date_returns_absent_for_student_with_no_records(): void
    {
        $result = $this->service->resolveForDate([$this->student->id], '2026-08-03');

        $this->assertEquals('absent', $result[$this->student->id]);
    }

    public function test_resolve_for_date_manual_record_wins_over_digital(): void
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
            'date' => '2026-08-03',
            'status' => 'present',
            'institution_id' => $this->institutionId,
        ]);

        $result = $this->service->resolveForDate([$this->student->id], '2026-08-03');

        $this->assertEquals('present', $result[$this->student->id]);
    }

    public function test_resolve_for_date_digital_fallback_when_no_manual(): void
    {
        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => '2026-08-03 07:15:00',
            'check_in_status' => 'late',
        ]);

        $result = $this->service->resolveForDate([$this->student->id], '2026-08-03');

        $this->assertEquals('late', $result[$this->student->id]);
    }
}
