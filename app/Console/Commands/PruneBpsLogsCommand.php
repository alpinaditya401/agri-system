<?php

namespace App\Console\Commands;

use App\Models\BpsFetchLog;
use Illuminate\Console\Command;

class PruneBpsLogsCommand extends Command
{
    protected $signature = 'bps:prune-logs {--days=90 : Number of days to retain logs}';
    protected $description = 'Prune old BPS fetch logs';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = BpsFetchLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$deleted} BPS fetch logs older than {$days} days.");

        return self::SUCCESS;
    }
}
