<?php

namespace App\Console\Commands;

use App\Models\Shop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShuffleShopDisplaySort extends Command
{
    protected $signature   = 'shops:shuffle-display-sort';
    protected $description = '同一入札スコアグループ内の display_sort をシャッフルし検索結果の表示順をローテーションする';

    public function handle(): int
    {
        // 入札スコアの計算式は SearchController の ORDER BY CASE と同一に保つ
        $shops = Shop::select('id', 'bid_price', 'budget_balance', 'main_image', 'xml_source')->get();

        $grouped = $shops->groupBy(function (Shop $shop): int {
            if ((int) $shop->budget_balance >= (int) $shop->bid_price && (int) $shop->bid_price > 0) {
                return (int) $shop->bid_price;
            }
            if ($shop->xml_source === 'upstage' && (int) $shop->bid_price > 0) {
                return (int) $shop->bid_price;
            }
            return $shop->main_image ? 15 : 5;
        });

        $updates = [];
        foreach ($grouped as $group) {
            foreach ($group->shuffle()->values() as $idx => $shop) {
                $updates[] = ['id' => $shop->id, 'display_sort' => $idx + 1];
            }
        }

        // CASE WHEN による bulk UPDATE（INSERT不使用のためNOT NULL制約を回避）
        foreach (array_chunk($updates, 500) as $chunk) {
            $whenClauses = implode(' ', array_map(
                fn($u) => "WHEN {$u['id']} THEN {$u['display_sort']}",
                $chunk
            ));
            $ids = implode(',', array_column($chunk, 'id'));
            DB::statement("UPDATE shops SET display_sort = CASE id {$whenClauses} END WHERE id IN ({$ids})");
        }

        // 検索 ID キャッシュを全クリアして新しい並び順を即時反映
        \Illuminate\Support\Facades\Cache::flush();

        $this->info('display_sort をシャッフルしました（' . count($updates) . '件）');
        return 0;
    }
}
