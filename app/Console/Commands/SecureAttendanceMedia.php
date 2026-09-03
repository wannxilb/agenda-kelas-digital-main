<?php

namespace App\Console\Commands;

use App\Models\StudentDailyAttendance;
use App\Models\StudentDailyAttendanceCorrection;
use App\Services\PrivateAttendanceMedia;
use Illuminate\Console\Command;

class SecureAttendanceMedia extends Command
{
    protected $signature = 'attendance:secure-media';
    protected $description = 'Encrypt and move attendance photos and correction evidence to private storage';

    public function handle(PrivateAttendanceMedia $media): int
    {
        StudentDailyAttendance::withoutGlobalScopes()->whereNotNull('check_in_photo')->orWhereNotNull('check_out_photo')->each(function ($attendance) use ($media) {
            foreach (['check_in_photo' => 'check-in', 'check_out_photo' => 'check-out'] as $column => $directory) {
                if ($attendance->{$column} && str_starts_with($attendance->{$column}, 'student-attendances/')) {
                    $path = $media->importPublic($attendance->{$column}, $directory . '/' . $attendance->date->format('Y-m-d'), 'enc');
                    if ($path) $attendance->update([$column => $path]);
                }
            }
        });

        StudentDailyAttendanceCorrection::withoutGlobalScopes()->whereNotNull('evidence_path')->each(function ($correction) use ($media) {
            if (str_starts_with($correction->evidence_path, 'attendance-corrections/')) {
                $path = $media->importPublic($correction->evidence_path, 'correction-evidence/' . $correction->student_id, 'enc');
                if ($path) $correction->update(['evidence_path' => $path]);
            }
        });

        $this->info('Media absensi berhasil diamankan.');
        return self::SUCCESS;
    }
}
