<?php

namespace App\Console\Commands;

use App\Models\ApiRequestLog;
use Illuminate\Console\Command;

class PruneApiRequestLogsCommand extends Command
{
    protected $signature = 'arcane:prune-api-logs {--days=30 : Delete logs older than this many days}';

    protected $description = 'Delete old partner API request logs so the table doesn\'t grow unbounded';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = ApiRequestLog::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Deleted {$deleted} API request log(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
