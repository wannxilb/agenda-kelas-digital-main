<?php

namespace Tests\Unit;

use App\Models\StudentDailyAttendance;
use Carbon\Carbon;
use Tests\TestCase;

class StudentDailyAttendanceStatusPresentationTest extends TestCase
{
    public function test_late_check_in_shows_late_minutes(): void
    {
        $attendance = new StudentDailyAttendance([
            'check_in_status' => 'late',
            'late_minutes' => 12,
        ]);

        $presentation = $attendance->checkInPresentation();

        $this->assertSame('Terlambat 12 menit', $presentation[0]['label']);
    }

    public function test_suspicious_check_in_shows_verification_and_title(): void
    {
        $attendance = new StudentDailyAttendance([
            'check_in_status' => 'alpha',
            'late_minutes' => 8,
            'check_in_suspicious' => true,
            'check_in_suspicious_reason' => 'Fingerprint perangkat tidak terkirim.',
        ]);

        $presentation = $attendance->checkInPresentation();
        $labels = array_column($presentation, 'label');

        $this->assertContains('Alpha (melebati batas)', $labels);
        $this->assertContains('Terlambat 8 menit', $labels);
        $this->assertContains('Fingerprint perangkat tidak terkirim.', $labels);
    }

    public function test_teacher_assisted_checkout_has_distinct_label(): void
    {
        $attendance = new StudentDailyAttendance([
            'check_out_status' => 'teacher_assisted',
        ]);

        $presentation = $attendance->checkOutPresentation();

        $this->assertSame('Dibantu wali kelas', $presentation[0]['label']);
    }

    public function test_recorded_check_in_without_status_does_not_show_not_entered(): void
    {
        $attendance = new StudentDailyAttendance([
            'check_in_at' => Carbon::parse('2026-08-03 21:46:00'),
            'check_in_status' => null,
        ]);

        $presentation = $attendance->checkInPresentation();

        $this->assertSame('Masuk', $presentation[0]['label']);
    }
}
