<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\StudentDailyAttendance;
use App\Models\StudentEarlyLeaveRequest;
use App\Services\AttendanceStatusResolver;
use Illuminate\Console\Command;

class CleanupNonOperationalAbsent extends Command
{
    protected $signature = 'attendance:cleanup-non-operational-absent';

    protected $description = 'Ubah presensi final menjadi "belum masuk" (not_yet) pada hari non-operasional yang tidak punya kehadiran/izin';

    public function handle(): int
    {
        $resolver = app(AttendanceStatusResolver::class);
        $processed = 0;

        Attendance::where('source', 'digital')
            ->where('status', 'absent')
            ->orderBy('id')
            ->chunkById(100, function ($attendances) use ($resolver, &$processed) {
                foreach ($attendances as $attendance) {
                    if (! $attendance->institution_id) {
                        continue;
                    }

                    $date = $attendance->date->toDateString();

                    if ($resolver->isOperationalAttendanceDate($attendance->institution_id, $date)) {
                        continue;
                    }

                    if (StudentDailyAttendance::where('student_id', $attendance->student_id)
                        ->whereDate('date', $date)
                        ->whereNotNull('check_in_at')
                        ->exists()) {
                        continue;
                    }

                    if (StudentEarlyLeaveRequest::where('student_id', $attendance->student_id)
                        ->where('status', 'approved')
                        ->whereDate('date', '<=', $date)
                        ->whereRaw('COALESCE(date_end, date) >= ?', [$date])
                        ->exists()) {
                        continue;
                    }

                    $attendance->update(['status' => 'not_yet']);
                    $processed++;
                }
            });

        $this->info("Selesai. {$processed} presensi di hari non-operasional diubah menjadi 'belum masuk'.");

        return self::SUCCESS;
    }
}