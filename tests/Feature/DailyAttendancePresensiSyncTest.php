<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Institution;
use App\Models\StudentDailyAttendance;
use App\Models\User;
use App\Services\DailyAttendancePresensiSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyAttendancePresensiSyncTest extends TestCase
{
    use RefreshDatabase;

    private DailyAttendancePresensiSync $service;
    private int $institutionId;
    private Classes $class;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(DailyAttendancePresensiSync::class);

        $institution = Institution::create(['name' => 'SMK Test', 'status' => 'active']);
        $this->institutionId = $institution->id;

        AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
            'institution_id' => $this->institutionId,
        ]);

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
    }

    public function test_sync_does_not_record_attendance_on_non_operational_day(): void
    {
        $daily = StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-08',
        ]);

        $result = $this->service->sync($daily);

        $this->assertNull($result);
        $this->assertDatabaseMissing('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-08',
        ]);
    }

    public function test_sync_records_absent_on_operational_day_when_overdue(): void
    {
        $daily = StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
        ]);

        $attendance = $this->service->sync($daily);

        $this->assertNotNull($attendance);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'status' => 'absent',
        ]);
    }

    public function test_sync_cleans_stale_auto_absent_on_non_operational_day(): void
    {
        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-08',
        ]);

        Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'date' => '2026-08-08',
            'status' => 'absent',
            'institution_id' => $this->institutionId,
            'source' => 'digital',
        ]);

        $daily = StudentDailyAttendance::where('date', '2026-08-08')->first();

        $result = $this->service->sync($daily);

        $this->assertNull($result);
        $this->assertDatabaseMissing('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-08',
        ]);
    }

    public function test_sync_keeps_manual_attendance_on_non_operational_day(): void
    {
        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-08',
        ]);

        Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'date' => '2026-08-08',
            'status' => 'absent',
            'institution_id' => $this->institutionId,
            'source' => 'manual',
        ]);

        $daily = StudentDailyAttendance::where('date', '2026-08-08')->first();

        $result = $this->service->sync($daily);

        $this->assertNotNull($result);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-08',
            'status' => 'absent',
            'source' => 'manual',
        ]);
    }
}