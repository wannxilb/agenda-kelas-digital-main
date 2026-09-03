<?php

namespace App\Console\Commands;

use App\Services\TeacherStatusService;
use Illuminate\Console\Command;

class ExpirePendingTeacherStatuses extends Command
{
    protected $signature = 'teacher-status:expire-pending';

    protected $description = 'Menandai pengajuan izin/tugas luar pending yang sudah melewati tanggal sebagai dibatalkan otomatis';

    public function handle(TeacherStatusService $service): int
    {
        $count = $service->expirePending();

        $this->info("Auto-expire selesai. {$count} pengajuan pending kadaluarsa dibatalkan.");

        return self::SUCCESS;
    }
}
