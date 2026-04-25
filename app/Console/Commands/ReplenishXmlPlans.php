<?php

namespace App\Console\Commands;

use App\Models\Shop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReplenishXmlPlans extends Command
{
    protected $signature = 'billing:replenish-xml-plans
                            {--dry-run : 実際には残高を増やさない}';

    protected $description = 'www.up-stage.info XML連携プランの月次予算を全対象店舗にチャージする（毎月1日実行）';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('【DRY-RUN モード】残高の更新は行いません');
        }

        // xml_bid_price > 0 かつ xml_monthly_budget > 0 の店舗が対象
        $shops = Shop::where('xml_source', 'upstage')
            ->where('xml_bid_price', '>', 0)
            ->where('xml_monthly_budget', '>', 0)
            ->get();

        if ($shops->isEmpty()) {
            $this->info('チャージ対象の店舗がありません');
            return self::SUCCESS;
        }

        $total = 0;
        $count = 0;

        foreach ($shops as $shop) {
            $amount = $shop->xml_monthly_budget;

            $this->line("  {$shop->name}: +{$amount}円 (残高 {$shop->budget_balance} → " . ($shop->budget_balance + $amount) . "円)");

            if (!$dryRun) {
                $shop->increment('budget_balance', $amount);
            }

            $total += $amount;
            $count++;
        }

        if (!$dryRun) {
            Log::info('billing:replenish-xml-plans 完了', [
                'count'        => $count,
                'total_amount' => $total,
            ]);
        }

        $this->info("完了 — {$count} 店舗 / 合計 " . number_format($total) . "円チャージ");

        return self::SUCCESS;
    }
}
