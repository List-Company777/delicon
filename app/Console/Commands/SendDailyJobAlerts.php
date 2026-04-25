<?php

namespace App\Console\Commands;

use App\Jobs\SendJobAlerts;
use App\Models\Job;
use Illuminate\Console\Command;

class SendDailyJobAlerts extends Command
{
    protected $signature   = 'line:send-daily-job-alerts {--dry-run : 送信せず対象件数だけ表示}';
    protected $description = '未送信の公開求人に対してLINE求人アラートを一括送信する（毎日15時）';

    public function handle(): int
    {
        // active かつ未送信の求人（72時間以内）— 有料店舗を先頭に
        $jobs = Job::join('shops', 'shops.id', '=', 'jobs.shop_id')
            ->where('jobs.status', 'active')
            ->whereNull('jobs.alert_sent_at')
            ->where('jobs.updated_at', '>=', now()->subHours(72))
            ->orderByRaw('(shops.budget_balance >= shops.bid_price) DESC')
            ->get(['jobs.id', 'jobs.title']);

        if ($jobs->isEmpty()) {
            $this->info('送信対象の求人はありません。');
            return self::SUCCESS;
        }

        $this->info("送信対象: {$jobs->count()} 件");

        if ($this->option('dry-run')) {
            $jobs->each(fn($j) => $this->line("  [{$j->id}] {$j->title}"));
            return self::SUCCESS;
        }

        foreach ($jobs as $job) {
            // 先に送信済みフラグを立てて二重送信を防ぐ
            $job->update(['alert_sent_at' => now()]);
            SendJobAlerts::dispatch($job->id)->onQueue('default');
            $this->line("  dispatched job_id={$job->id}");
        }

        $this->info('完了');
        return self::SUCCESS;
    }
}
