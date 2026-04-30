<?php

namespace App\Console\Commands;

use App\Models\JobAccessLog;
use App\Models\ShopAccessLog;
use Illuminate\Console\Command;

class PruneAccessLogs extends Command
{
    protected $signature   = 'logs:prune {--days=400 : 保持する日数}';
    protected $description = 'job_access_logs / shop_access_logs の古いレコードを削除する';

    public function handle(): int
    {
        $days      = (int) $this->option('days');
        $threshold = now()->subDays($days);

        $job  = JobAccessLog::where('created_at', '<', $threshold)->delete();
        $shop = ShopAccessLog::where('created_at', '<', $threshold)->delete();

        $this->info("Pruned: job_access_logs={$job}, shop_access_logs={$shop} (older than {$days} days)");

        return self::SUCCESS;
    }
}
