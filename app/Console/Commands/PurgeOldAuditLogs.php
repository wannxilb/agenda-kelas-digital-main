<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Console\Command;

class PurgeOldAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'audit:purge';

    /**
     * The console command description.
     */
    protected $description = 'Delete audit logs older than the configured retention period';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $retentionDays = (int) Setting::get('audit_retention_days', 365);

        if ($retentionDays < 1) {
            $this->info('Retention days is set to 0 or less. Skipping purge.');
            return 0;
        }

        $cutoffDate = now()->subDays($retentionDays);

        $deleted = AuditLog::where('created_at', '<', $cutoffDate)->delete();

        $this->info("Purged {$deleted} audit log(s) older than {$retentionDays} days (before {$cutoffDate->toDateString()}).");

        return 0;
    }
}
