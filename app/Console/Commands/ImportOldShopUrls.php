<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ShopExternalUrl;

class ImportOldShopUrls extends Command
{
    protected $signature   = 'import:old-shop-urls {--dry-run}';
    protected $description = '旧DBのshops.url / url_smpをshop_external_urlsへインポートする';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        if ($dryRun) $this->warn('[DRY-RUN] DBへの書き込みは行いません');

        $rows = DB::connection('mysql_old')
            ->table('shops')
            ->whereNotNull('id')
            ->select('id', 'url', 'url_smp')
            ->get();

        $shopIdMap = DB::table('shops')
            ->whereNotNull('old_id')
            ->pluck('id', 'old_id')
            ->all();

        $inserted = 0;
        $skipped  = 0;

        foreach ($rows as $row) {
            $newId = $shopIdMap[$row->id] ?? null;
            if (!$newId) { $skipped++; continue; }

            $inserts = [];

            // url → website
            if (!empty($row->url) && filter_var($row->url, FILTER_VALIDATE_URL)) {
                $inserts[] = ['shop_id' => $newId, 'url_type' => 'website', 'url' => $row->url, 'sort_order' => 0];
            }

            // url_smp → line / other / skip
            if (!empty($row->url_smp)) {
                $smp = trim($row->url_smp);
                if (preg_match('#(line\.me|lin\.ee|line\.naver\.jp|page\.line\.me)#i', $smp)) {
                    $type = 'line';
                } elseif (filter_var($smp, FILTER_VALIDATE_URL)) {
                    $type = 'other';
                } else {
                    $type = null; // LINE ID など生テキスト → スキップ
                }
                if ($type) {
                    $inserts[] = ['shop_id' => $newId, 'url_type' => $type, 'url' => $smp, 'sort_order' => count($inserts)];
                }
            }

            if (empty($inserts)) { $skipped++; continue; }

            if (!$dryRun) {
                foreach ($inserts as $data) {
                    ShopExternalUrl::create($data);
                }
            } else {
                foreach ($inserts as $data) {
                    $this->line("  shop_id={$data['shop_id']} type={$data['url_type']} url={$data['url']}");
                }
            }
            $inserted += count($inserts);
        }

        $this->info("インポート完了: {$inserted}件挿入, {$skipped}件スキップ");
        return self::SUCCESS;
    }
}
