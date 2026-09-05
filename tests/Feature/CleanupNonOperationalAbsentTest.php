<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Institution;
use App\Models\StudentDailyAttendance;
use App\Models\StudentEarlyLeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanupNonOperationalAbsentTest extends TestCase
{
    use RefreshDatabase;

    private int $institutionId;
    private Classes $class;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_converts_stale_absent_to_not_yet_on_non_operational_day(): void
    {
        Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'date' => '2026-08-08',
            'status' => 'absent',
            'institution_id' => $this->institutionId,
            'source' => 'digital',
        ]);

        $this->artisan('attendance:cleanup-non-operational-absent')->assertSuccessful();

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-08',
            'status' => 'not_yet',
        ]);
    }

    public function test_keeps_absent_on_operational_day(): void
    {
        Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'date' => '2026-08-03',
            'status' => 'absent',
            'institution_id' => $this->institutionId,
            'source' => 'digital',
        ]);

        $this->artisan('attendance:cleanup-non-operational-absent')->assertSuccessful();

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'status' => 'absent',
        ]);
    }

    public function test_keeps_absent_when_operational_override_active(): void
    {
        \App\Models\Setting::set('operational_override_until', '2026-12-31', 'general', $this->institutionId);

        Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'date' => '2026-08-08',
            'status' => 'absent',
            'institution_id' => $this->institutionId,
            'source' => 'digital',
        ]);

        $this->artisan('attendance:cleanup-non-operational-absent')->assertSuccessful();

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-08',
            'status' => 'absent',
        ]);
    }

    public function test_keeps_absent_when_student_has_check_in(): void
    {
        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-08',
            'check_in_at' => '2026-08-08 07:15:00',
            'check_in_status' => 'on_time',
        ]);

        Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'date' => '2026-08-08',
            'status' => 'absent',
            'institution_id' => $this->institutionId,
            'source' => 'digital',
        ]);

        $this->artisan('attendance:cleanup-non-operational-absent')->assertSuccessful();

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-08',
            'status' => 'absent',
        ]);
    }

    public function test_keeps_absent_when_approved_request_covers_date(): void
    {
        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-08',
            'category' => 'sakit',
            'reason' => 'Sakit',
            'status' => 'approved',
        ]);

        Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'date' => '2026-08-08',
            'status' => 'absent',
            'institution_id' => $this->institutionId,
            'source' => 'digital',
        ]);

        $this->artisan('attendance:cleanup-non-operational-absent')->assertSuccessful();

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-08',
            'status' => 'absent',
        ]);
    }

    public function test_keeps_manual_absent(): void
    {
        Attendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'date' => '2026-08-08',
            'status' => 'absent',
            'institution_id' => $this->institutionId,
            'source' => 'manual',
        ]);

        $this->artisan('attendance:cleanup-non-operational-absent')->assertSuccessful();

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-08',
            'status' => 'absent',
            'source' => 'manual',
        ]);
    }
}