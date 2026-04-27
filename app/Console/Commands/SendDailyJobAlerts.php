<?php

namespace App\Console\Commands;

use App\Jobs\SendJobAlerts;
use App\Models\Job;
use Illuminate\Console\Command;

class SendDailyJobAlerts extends Command
{
    protected $signature   = 'line:send-daily-job-alerts {--dry-run : 送信せず対象件数だけ表示}';
    protected $description = '未送信の公開求人（最大3件）を1通にまとめてLINE求人アラートを送信する（3日ごと15時）';

    public function handle(): int
    {
        $jobs = Job::join('shops', 'shops.id', '=', 'jobs.shop_id')
            ->where('jobs.status', 'active')
            ->whereIn('jobs.search_group', ['female', 'male'])
            ->whereNull('jobs.alert_sent_at')
            ->orderByRaw('(shops.budget_balance >= shops.bid_price) DESC')
            ->orderByDesc('jobs.updated_at')
            ->limit(3)
            ->get(['jobs.id', 'jobs.title']);

        if ($jobs->isEmpty()) {
            $this->info('送信対象の求人はありません。');
            return self::SUCCESS;
        }

        $this->info("送信対象: {$jobs->count()} 件");
        $jobs->each(fn($j) => $this->line("  [{$j->id}] {$j->title}"));

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        // 先に送信済みフラグを立てて二重送信を防ぐ
        Job::whereIn('id', $jobs->pluck('id'))->update(['alert_sent_at' => now()]);

        // 3件まとめて1回だけdispatch
        SendJobAlerts::dispatch($jobs->pluck('id')->all())->onQueue('default');
        $this->info('dispatched');

        return self::SUCCESS;
    }
}
