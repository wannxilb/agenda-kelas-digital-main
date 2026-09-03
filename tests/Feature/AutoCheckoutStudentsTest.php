<?php

namespace Tests\Feature;

use App\Console\Commands\AutoCheckoutStudents;
use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\DailyAttendanceSetting;
use App\Models\Institution;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\StudentDailyAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AutoCheckoutStudentsTest extends TestCase
{
    use RefreshDatabase;

    private int $institutionId;
    private Classes $class;
    private User $student;

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

    public function test_auto_checkout_processes_students_without_checkout(): void
    {
        DailyAttendanceSetting::create([
            'institution_id' => $this->institutionId,
            'check_out_start' => '15:00:00',
        ]);

        Setting::create([
            'key' => 'operational_end_time',
            'value' => '15:00:00',
            'group' => 'general',
            'institution_id' => $this->institutionId,
        ]);

        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => '2026-08-03 06:55:00',
            'check_in_status' => 'on_time',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-08-03 15:05:00'));
        Artisan::call('attendance:auto-checkout', ['--date' => '2026-08-03']);

        $this->assertDatabaseHas('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'check_out_status' => 'auto_checkout',
        ]);
    }

    public function test_auto_checkout_skips_already_checked_out_students(): void
    {
        DailyAttendanceSetting::create([
            'institution_id' => $this->institutionId,
            'check_out_start' => '15:00:00',
        ]);

        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => '2026-08-03 06:55:00',
            'check_in_status' => 'on_time',
            'check_out_at' => '2026-08-03 14:30:00',
            'check_out_status' => 'checked_out',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-08-03 15:05:00'));
        Artisan::call('attendance:auto-checkout', ['--date' => '2026-08-03']);

        $this->assertDatabaseHas('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'check_out_status' => 'checked_out',
        ]);
    }

    public function test_auto_checkout_skips_before_scheduled_time(): void
    {
        DailyAttendanceSetting::create([
            'institution_id' => $this->institutionId,
            'check_out_start' => '15:00:00',
        ]);

        Setting::create([
            'key' => 'operational_end_time',
            'value' => '15:00:00',
            'group' => 'general',
            'institution_id' => $this->institutionId,
        ]);

        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => '2026-08-03 06:55:00',
            'check_in_status' => 'on_time',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-08-03 14:55:00'));
        Artisan::call('attendance:auto-checkout', ['--date' => '2026-08-03']);

        $this->assertDatabaseHas('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'check_out_status' => null,
        ]);
    }

    public function test_auto_checkout_respects_override_time(): void
    {
        DailyAttendanceSetting::create([
            'institution_id' => $this->institutionId,
            'check_out_start' => '15:00:00',
            'check_out_override_date' => '2026-08-03',
            'check_out_override_time' => '12:00:00',
        ]);

        Setting::create([
            'key' => 'operational_end_time',
            'value' => '15:00:00',
            'group' => 'general',
            'institution_id' => $this->institutionId,
        ]);

        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => '2026-08-03 06:55:00',
            'check_in_status' => 'on_time',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-08-03 12:05:00'));
        Artisan::call('attendance:auto-checkout', ['--date' => '2026-08-03']);

        $this->assertDatabaseHas('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
            'check_out_status' => 'auto_checkout',
        ]);
    }

    public function test_auto_checkout_skips_students_without_check_in(): void
    {
        DailyAttendanceSetting::create([
            'institution_id' => $this->institutionId,
            'check_out_start' => '15:00:00',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-08-03 15:05:00'));
        Artisan::call('attendance:auto-checkout', ['--date' => '2026-08-03']);

        $this->assertDatabaseMissing('student_daily_attendances', [
            'student_id' => $this->student->id,
            'date' => '2026-08-03',
        ]);
    }
}
