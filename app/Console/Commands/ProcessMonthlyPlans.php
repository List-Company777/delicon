<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Models\ShopPlanApplication;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessMonthlyPlans extends Command
{
    protected $signature   = 'plans:monthly-process {--date= : 処理基準日 (Y-m-d)。省略時は今日}';
    protected $description = '月次プラン処理：継続申し込みを反映し、期限切れを無料に戻す（毎月1日実行）';

    public function handle(): int
    {
        $today = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now()->startOfDay();

        $this->info("処理日: {$today->toDateString()}");

        // 1. 承認済み継続申し込みでeffective_date = 今日のものを適用
        $renewals = ShopPlanApplication::with('shop')
            ->where('status', 'approved')
            ->where('application_type', 'renewal')
            ->where('effective_date', $today->toDateString())
            ->get();

        foreach ($renewals as $app) {
            $shop    = $app->shop;
            $newPlan = (int) $app->plan;
            $oldPlan = (int) $shop->plan;
            $date    = $today->toDateString();

            $planSinceKey    = "plan{$newPlan}_since";
            $oldPlanSinceKey = "plan{$oldPlan}_since";
            $isUpgrade       = $newPlan < $oldPlan;
            $isDowngrade     = $newPlan > $oldPlan;

            $newPlanSince = $isUpgrade
                ? $date
                : ($shop->$planSinceKey ?? $shop->$oldPlanSinceKey ?? $date);

            $updates = [
                'plan'           => $newPlan,
                'is_banner_plan' => false,
                'paid_since'     => $shop->paid_since ?? $date,
                $planSinceKey    => $newPlanSince,
                'plan_expires_on' => $app->expires_on,
            ];
            if ($isDowngrade && $oldPlan <= 4) {
                $updates[$oldPlanSinceKey] = null;
            }

            $shop->update($updates);
            $this->line("  継続適用: {$shop->name} plan{$oldPlan}→plan{$newPlan} 期限:{$app->expires_on}");
        }

        $this->info("継続申し込み適用: {$renewals->count()}件");

        // 2. plan_expires_on が今日より前の有料店舗を無料に降格
        $expired = Shop::whereIn('plan', [1, 2, 3])
            ->whereNotNull('plan_expires_on')
            ->where('plan_expires_on', '<', $today->toDateString())
            ->get();

        foreach ($expired as $shop) {
            $shop->update([
                'plan'            => 5,
                'is_banner_plan'  => false,
                'paid_since'      => null,
                'plan_expires_on' => null,
                "plan{$shop->plan}_since" => null,
            ]);
            $this->line("  期限切れ無料化: {$shop->name} (期限:{$shop->plan_expires_on})");
        }

        $this->info("期限切れ無料化: {$expired->count()}件");
        return 0;
    }
}
