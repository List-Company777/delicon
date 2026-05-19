<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateCastScores extends Command
{
    protected $signature   = 'casts:update-scores';
    protected $description = 'Recalculate cast_score for all active casts';

    public function handle(): int
    {
        $this->info('Updating cast scores...');

        DB::statement("
            UPDATE casts c
            JOIN shops s ON s.id = c.shop_id
            SET c.cast_score = (
                /* 店舗プランボーナス: plan1=30/plan2=20/plan3=10/plan4以上=0 */
                CASE WHEN s.plan = 1 THEN 30 WHEN s.plan = 2 THEN 20 WHEN s.plan = 3 THEN 10 ELSE 0 END

                /* プロフィール文字数（100文字以上でプラス） */
                + CASE WHEN CHAR_LENGTH(COALESCE(c.comment, '')) >= 100 THEN 10 ELSE -10 END

                /* 直近7日以内に出勤 */
                + CASE WHEN EXISTS(
                    SELECT 1 FROM cast_schedules cs
                    WHERE cs.cast_id = c.id
                      AND cs.work_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                ) THEN 5 ELSE 0 END

                /* 直近7日以内に写メ日記投稿 */
                + CASE WHEN EXISTS(
                    SELECT 1 FROM cast_diaries cd
                    WHERE cd.cast_id = c.id
                      AND cd.status = 'published'
                      AND cd.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ) THEN 5 ELSE 0 END

                /* お気に入り数 × 3 */
                + (SELECT COUNT(*) FROM cast_favorites cf WHERE cf.cast_id = c.id) * 3

                /* 口コミ数（承認済）× 5 */
                + (SELECT COUNT(*) FROM cast_reviews cr WHERE cr.cast_id = c.id AND cr.is_approved = 1) * 5

                /* スペック全項目入力 */
                + CASE WHEN c.age IS NOT NULL AND c.tall IS NOT NULL
                            AND c.bust IS NOT NULL AND c.cup IS NOT NULL
                            AND c.west IS NOT NULL AND c.hip IS NOT NULL
                       THEN 5 ELSE 0 END

                /* 画像2枚以上 */
                + CASE WHEN (SELECT COUNT(*) FROM cast_images ci WHERE ci.cast_id = c.id) >= 2
                       THEN 3 ELSE 0 END

                /* ランダム ±10 */
                + FLOOR(RAND() * 21) - 10
            )
            WHERE c.status = 'active'
        ");

        $count = DB::table('casts')->where('status', 'active')->count();
        $this->info("Updated {$count} cast scores. Rebuilding sort_rank...");

        // ラウンドロビン sort_rank:
        // 各店の中でスコア順に within_shop_rank を付与し、
        // within_shop_rank → cast_score DESC → plan ASC の順で全体順位を決定する。
        // 結果: 各店の1番手が全店分並んでから、各店の2番手が並ぶ形になり店独占を防ぐ。
        DB::statement("
            UPDATE casts c
            JOIN (
                SELECT id, @r := @r + 1 AS rk
                FROM (
                    SELECT c2.id,
                           ROW_NUMBER() OVER (
                               PARTITION BY c2.shop_id
                               ORDER BY c2.cast_score DESC, c2.id ASC
                           ) AS within_shop_rank,
                           c2.cast_score,
                           s2.plan
                    FROM casts c2
                    JOIN shops s2 ON s2.id = c2.shop_id
                    WHERE c2.status = 'active' AND s2.status = 'active'
                ) base
                JOIN (SELECT @r := 0) init
                ORDER BY within_shop_rank ASC, cast_score DESC, plan ASC, id ASC
            ) ranked ON ranked.id = c.id
            SET c.sort_rank = ranked.rk
            WHERE c.status = 'active'
        ");

        $this->info("Done. sort_rank assigned to {$count} casts.");
        return self::SUCCESS;
    }
}
