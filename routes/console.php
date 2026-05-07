<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ディレクトリURLサイトマップを毎日午前3時に再生成
Schedule::command('sitemap:generate')->dailyAt('03:00');

// 詳細ページサイトマップを毎日午前4時30分に再生成（負荷分散）
Schedule::command('sitemap:generate-detail')->dailyAt('04:30');

// 固定ページ・記事・店舗サイトマップを毎日午前5時に再生成
Schedule::command('sitemap:generate-pages')->dailyAt('05:30');

// 生存確認メール送信・期限切れ非公開処理（毎日午前10時）
Schedule::command('shops:alive-check')->dailyAt('10:00');

// 登録済みXMLフィード全件同期（毎日午前5時）
Schedule::command('import:xml-feed')->dailyAt('05:00');

// www.up-stage.info XMLプランの月次予算チャージ（毎月1日 午前6時）
Schedule::command('billing:replenish-xml-plans')->monthlyOn(1, '06:00');

// LINE送信数チェック・上限アラート（毎日午前9時）
Schedule::command('line:check-quota')->dailyAt('09:00');

// 求人アラート送信（3日ごと15時・未送信求人を最大3件）
Schedule::command('line:send-daily-job-alerts')->cron('0 15 */3 * *');

// AI記事自動生成（毎週火・金 午前2時 ─ 下書きとして保存し管理者がレビュー後公開）
Schedule::command('articles:generate')->weeklyOn(2, '02:00');  // 火曜
Schedule::command('articles:generate')->weeklyOn(5, '02:00');  // 金曜

// 検索順位スコアをバッチ更新（5分ごと）
Schedule::command('shops:update-rank-scores')->everyFiveMinutes();

// 同一入札スコア内の表示順をシャッフル（30分ごと・検索IDキャッシュTTLと同期）
Schedule::command('shops:shuffle-display-sort')->everyThirtyMinutes();

// アクセスログのパージ（400日超のレコードを毎週月曜 午前3時30分に削除）
Schedule::command('logs:prune')->weeklyOn(1, '03:30');

// 月次レポートメール（毎月1日 00:05 に前月分を集計して送信）
Schedule::command('report:monthly')->monthlyOn(1, '00:05');

// 月次レポートメール 第2送信（01:05 に再送）
Schedule::command('report:monthly')->monthlyOn(1, '01:05');

// フッター都道府県キャッシュ更新（毎日 02:00）
Schedule::command('cache:warm-footer')->dailyAt('02:00');

// girl-listフィルターサイトマップ（毎日 06:00）
Schedule::command('sitemap:generate-girl-list')->dailyAt('06:00');
