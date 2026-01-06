<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class PruneAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:prune {--days=365 : The number of days to retain audit logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune audit logs older than a specified number of days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $date = now()->subDays($days);

        $count = AuditLog::where('created_at', '<', $date)->delete();

        $this->info("Deleted {$count} audit logs older than {$days} days.");
    }
}
