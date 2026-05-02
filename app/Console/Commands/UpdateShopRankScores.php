<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateShopRankScores extends Command
{
    protected $signature   = 'shops:update-rank-scores';
    protected $description = '検索順位スコアを一括更新（5分毎バッチ）';

    public function handle(): int
    {
        DB::statement("
            UPDATE shops
            SET rank_score = CASE
                WHEN budget_balance >= bid_price THEN bid_price
                WHEN xml_source = 'upstage' AND bid_price > 0 THEN bid_price
                WHEN main_image IS NOT NULL THEN 15
                ELSE 5
            END
        ");

        $this->info('rank_score updated.');
        return 0;
    }
}
