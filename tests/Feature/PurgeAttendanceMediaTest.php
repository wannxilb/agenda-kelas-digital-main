<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Setting;
use App\Models\StudentDailyAttendance;
use App\Models\StudentDailyAttendanceCorrection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurgeAttendanceMediaTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');

        $this->student = User::factory()->create([
            'institution_id' => 1,
            'class_id'       => Classes::first()->id,
        ]);
        $this->student->assignRole('siswa');
    }

    private function fakeFile(string $path): void
    {
        Storage::disk('local')->put($path, 'fake-binary');
        $this->assertTrue(Storage::disk('local')->exists($path));
    }

    private function createAttendanceWithPhoto(string $photoPath, string $date): StudentDailyAttendance
    {
        return StudentDailyAttendance::create([
            'student_id'      => $this->student->id,
            'class_id'        => $this->student->class_id,
            'institution_id'  => $this->student->institution_id,
            'academic_year_id'=> AcademicYear::where('is_active', true)->first()->id,
            'date'            => $date,
            'check_in_at'     => Carbon::parse($date)->setTime(8, 0),
            'check_in_photo'  => $photoPath,
            'check_in_status' => 'needs_verification',
        ]);
    }

    public function test_purge_uses_overridden_days_option_when_provided(): void
    {
        // retention setting = 14, but we pass --days=1 → only today survives
        Setting::where('key', 'attendance_photo_retention_days')->update(['value' => '14']);

        $oldPath   = 'student-attendances/check-in/' . Carbon::now()->subDays(2)->format('Y-m-d') . '/aabbccdd.enc';
        $todayPath = 'student-attendances/check-in/' . Carbon::today()->format('Y-m-d') . '/11223344.enc';
        $this->fakeFile($oldPath);
        $this->fakeFile($todayPath);

        $oldRec   = $this->createAttendanceWithPhoto($oldPath, Carbon::now()->subDays(2)->toDateString());
        $todayRec = $this->createAttendanceWithPhoto($todayPath, Carbon::today()->toDateString());

        $this->artisan('attendance:purge-media', ['--days' => 1])->assertSuccessful();

        // Old gone, DB cleared
        $this->assertNull($oldRec->fresh()->check_in_photo);
        $this->assertFalse(Storage::disk('local')->exists($oldPath));

        // Today kept
        $this->assertEquals($todayPath, $todayRec->fresh()->check_in_photo);
        $this->assertTrue(Storage::disk('local')->exists($todayPath));
    }

    public function test_purge_uses_setting_fallback_when_days_not_passed(): void
    {
        Setting::where('key', 'attendance_photo_retention_days')->update(['value' => '5']);

        $olderPath = 'student-attendances/check-in/' . Carbon::now()->subDays(6)->format('Y-m-d') . '/deadbeef.enc';
        $newPath   = 'student-attendances/check-in/' . Carbon::now()->subDays(3)->format('Y-m-d') . '/cabbages.enc';
        $this->fakeFile($olderPath);
        $this->fakeFile($newPath);

        $olderRec = $this->createAttendanceWithPhoto($olderPath, Carbon::now()->subDays(6)->toDateString());
        $newRec   = $this->createAttendanceWithPhoto($newPath, Carbon::now()->subDays(3)->toDateString());

        $this->artisan('attendance:purge-media')->assertSuccessful(); // no --days

        $this->assertNull($olderRec->fresh()->check_in_photo);
        $this->assertFalse(Storage::disk('local')->exists($olderPath));

        $this->assertEquals($newPath, $newRec->fresh()->check_in_photo);
        $this->assertTrue(Storage::disk('local')->exists($newPath));
    }

    public function test_purge_removes_correction_evidence(): void
    {
        Setting::where('key', 'attendance_photo_retention_days')->update(['value' => '5']);

        $oldPath = 'correction-evidence/' . $this->student->id . '/old-evidence.enc';
        $this->fakeFile($oldPath);

        $correction = StudentDailyAttendanceCorrection::create([
            'student_daily_attendance_id' => $this->createAttendanceWithPhoto('x', Carbon::now()->subDays(10)->toDateString())->id,
            'student_id'                  => $this->student->id,
            'institution_id'              => 1,
            'target_event'                => 'check_in',
            'requested_status'             => 'present',
            'reason'                      => 'Koreksi uji',
            'status'                      => 'pending',
            'evidence_path'               => $oldPath,
        ]);
        DB::table('student_daily_attendance_corrections')
            ->where('id', $correction->id)
            ->update(['created_at' => Carbon::now()->subDays(6)->toDateTimeString()]);

        $this->artisan('attendance:purge-media')->assertSuccessful();

        $this->assertNull($correction->fresh()->evidence_path);
        $this->assertFalse(Storage::disk('local')->exists($oldPath));
    }
}