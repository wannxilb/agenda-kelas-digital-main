<?php

namespace App\Console\Commands;

use App\Models\StudentDailyAttendance;
use App\Models\StudentDailyAttendanceCorrection;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeAttendanceMedia extends Command
{
    protected $signature = 'attendance:purge-media {--days= : Masa simpan media dalam hari (default: setting attendance_photo_retention_days)}';
    protected $description = 'Delete old attendance photos and correction evidence while preserving attendance records';

    public function handle(): int
    {
        $rawDays = $this->option('days');
        $days = $rawDays !== null && $rawDays !== '' ? (int) $rawDays : (int) Setting::get('attendance_photo_retention_days', 30);
        $days = max(1, $days);

        $cutoff = Carbon::now()->subDays($days);
        $deleted = 0;

        StudentDailyAttendance::withoutGlobalScopes()
            ->whereDate('date', '<', $cutoff->toDateString())
            ->chunkById(100, function ($attendances) use (&$deleted) {
                foreach ($attendances as $attendance) {
                    foreach (['check_in_photo', 'check_out_photo'] as $column) {
                        if ($attendance->{$column}) {
                            Storage::disk('local')->delete($attendance->{$column});
                            $attendance->update([$column => null]);
                            $deleted++;
                        }
                    }
                }
            });

        StudentDailyAttendanceCorrection::withoutGlobalScopes()
            ->where('created_at', '<', $cutoff)
            ->chunkById(100, function ($corrections) use (&$deleted) {
                foreach ($corrections as $correction) {
                    if ($correction->evidence_path) {
                        Storage::disk('local')->delete($correction->evidence_path);
                        $correction->update(['evidence_path' => null, 'evidence_mime' => null]);
                        $deleted++;
                    }
                }
            });

        $this->info("{$deleted} media lama dihapus. Data absensi dan audit tetap dipertahankan. (retensi {$days} hari)");
        return self::SUCCESS;
    }
}
