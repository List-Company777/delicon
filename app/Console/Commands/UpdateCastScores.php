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
                /* 店舗プランボーナス: (plan-1)*10 → 0/10/20/30/40 */
                (s.plan - 1) * 10

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
        $this->info("Done. Updated {$count} casts.");
        return self::SUCCESS;
    }
}
