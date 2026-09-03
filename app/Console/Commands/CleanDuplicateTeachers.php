<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CleanDuplicateTeachers extends Command
{
    protected $signature = 'teachers:clean-duplicates';
    protected $description = 'Remove duplicate teacher records, keep the oldest one';

    public function handle()
    {
        // Ambil semua user dengan role teacher, group by name
        $duplicates = User::role('teacher')
            ->select('name', DB::raw('COUNT(*) as total_count'), DB::raw('MIN(id) as keep_id'))
            ->groupBy('name')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        $totalDeleted = 0;

        foreach ($duplicates as $dup) {
            // Hapus semua kecuali yang pertama (keep_id)
            $deleted = User::role('teacher')
                ->where('name', $dup->name)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
            $totalDeleted += $deleted;
            $this->line("  Hapus {$deleted} duplikat: {$dup->name}");
        }

        $this->info("Total duplikat dihapus: {$totalDeleted}");
        $this->info("Total guru sekarang: " . User::role('teacher')->count());

        return Command::SUCCESS;
    }
}
