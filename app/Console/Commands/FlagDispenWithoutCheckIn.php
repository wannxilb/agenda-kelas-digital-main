<?php

namespace App\Console\Commands;

use App\Services\DispenCheckInEnforcer;
use Illuminate\Console\Command;

class FlagDispenWithoutCheckIn extends Command
{
    protected $signature = 'attendance:flag-dispen-without-checkin';

    protected $description = 'Menandai hari dispen (lomba/kegiatan) tanpa bukti absen masuk sebagai perlu verifikasi';

    public function handle(DispenCheckInEnforcer $service): int
    {
        $flagged = $service->flagWithoutCheckIn();

        $this->info("Penandaan dispen tanpa bukti absen selesai. {$flagged} tanggal ditandai perlu verifikasi.");

        return self::SUCCESS;
    }
}
