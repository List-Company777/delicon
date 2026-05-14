<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportOldCasts extends Command
{
    protected $signature = 'import:old-casts
                            {--dry-run : DBへの書き込みを行わない}
                            {--limit=0 : 取得件数上限（0=全件）}
                            {--shop-id= : 特定の新DB shop_idのみ処理}';

    protected $description = '旧CakePHPサイト(delicon_old)からcastsデータをインポートする';

    private bool $dryRun   = false;
    private int  $imported = 0;
    private int  $skipped  = 0;
    private int  $errors   = 0;

    public function handle(): int
    {
        $this->dryRun  = (bool) $this->option('dry-run');
        $limit         = (int) $this->option('limit');
        $filterShopId  = $this->option('shop-id');

        if ($this->dryRun) {
            $this->warn('[DRY-RUN] DBへの書き込みは行いません');
        }

        // 新DB shop の old_id -> new_id マップ
        $shopMap = DB::table('shops')
            ->whereNotNull('old_id')
            ->pluck('id', 'old_id')
            ->all();

        // 既存の cast old_id をキャッシュ
        $existingOldIds = DB::table('casts')
            ->whereNotNull('old_id')
            ->pluck('old_id')
            ->flip()
            ->all();

        $query = DB::connection('mysql_old')->table('girls')->orderBy('id');

        // --shop-id 指定時: 旧DB の shop_id を逆引き
        if ($filterShopId !== null) {
            $newShopId  = (int) $filterShopId;
            $oldShopIds = array_keys($shopMap, $newShopId);
            if (empty($oldShopIds)) {
                $this->error("shop_id={$newShopId} に対応する旧DB shop_id が見つかりません");
                return self::FAILURE;
            }
            $query->whereIn('shop_id', $oldShopIds);
        }

        $total = $query->count();
        if ($limit > 0) {
            $total = min($total, $limit);
        }
        $this->info("旧DB girls件数: {$total}件（上限: " . ($limit > 0 ? $limit : '全件') . ')');

        $processed = 0;
        $stopLoop  = false;
        $chunkSize = 500;

        $query->chunk($chunkSize, function ($rows) use (
            &$processed, $total, $limit, $shopMap, $existingOldIds, &$stopLoop, $chunkSize
        ) {
            $insertBatch = [];

            foreach ($rows as $row) {
                if ($limit > 0 && $processed >= $limit) {
                    $stopLoop = true;
                    break;
                }

                $processed++;

                // shop_id 解決
                if (!isset($shopMap[$row->shop_id])) {
                    $this->errors++;
                    continue;
                }
                $newShopId = $shopMap[$row->shop_id];

                // 重複スキップ
                if (array_key_exists($row->id, $existingOldIds)) {
                    $this->skipped++;
                    continue;
                }

                // cup: 大文字1文字のみ
                $cup = null;
                if (!empty($row->cup) && preg_match('/^[A-Z]$/u', strtoupper($row->cup))) {
                    $cup = strtoupper($row->cup);
                }

                // type_id: 1-21 のみ
                $typeId = (isset($row->type) && $row->type >= 1 && $row->type <= 21) ? $row->type : null;

                // body_id: 1-16 のみ
                $bodyId = (isset($row->body) && $row->body >= 1 && $row->body <= 16) ? $row->body : null;

                // img_file_name
                $imgFileName = null;
                if (!empty($row->img_file_name) && !str_starts_with($row->img_file_name, '/img/common/')) {
                    $imgFileName = $row->img_file_name;
                }

                // price_on: 正数のみ
                $priceOn = (!empty($row->price_on) && $row->price_on > 0) ? $row->price_on : null;

                $insertBatch[] = [
                    'old_id'         => $row->id,
                    'shop_id'        => $newShopId,
                    'name'           => $row->name ?? '不明',
                    'age'            => $row->age  ?: null,
                    'tall'           => $row->tall ?: null,
                    'bust'           => $row->bust ?: null,
                    'cup'            => $cup,
                    'west'           => $row->west ?: null,
                    'hip'            => $row->hip  ?: null,
                    'img_file_name'  => $imgFileName,
                    'type_id'        => $typeId,
                    'body_id'        => $bodyId,
                    'comment'        => $row->comment,
                    'message'        => $row->message,
                    'blood'          => $row->blood     ?: null,
                    'country'        => $row->country   ?: null,
                    'hatsutaiken'    => $row->hatsutaiken ?: null,
                    'seikantai'      => $row->seikantai ?: null,
                    'tokuiwaza'      => $row->tokuiwaza ?: null,
                    'sukinatype'     => $row->sukinatype ?: null,
                    'shumi'          => $row->shumi     ?: null,
                    'zenshoku'       => $row->zenshoku  ?: null,
                    'tabacco'        => ($row->tabacco === 'true') ? 1 : 0,
                    'seiza'          => $row->seiza     ?: null,
                    'likeeat'        => $row->likeeat   ?: null,
                    'osake'          => ($row->osake === 'true') ? 1 : (($row->osake === 'false') ? 0 : 0),
                    'yuumeijin'      => $row->yuumeijin ?: null,
                    'shiofuki'       => $row->shiofuki  ?: null,
                    'zitaku'         => $row->zitaku    ?: null,
                    'twitter_account'=> $row->twitter_acount ? mb_substr($row->twitter_acount, 0, 100) : null,
                    'official_url'   => $row->official_url   ? mb_substr($row->official_url,   0, 255) : null,
                    'price_on'       => $priceOn,
                    'is_recommended' => ($row->osusume === 'true') ? 1 : 0,
                    'is_new'         => 0,
                    'sort_order'     => $row->row ?? 9999,
                    'ranking_count'  => $row->ranking_count ?? 0,
                    'status'         => ($row->search_view == 0) ? 'inactive' : 'active',
                    'created_at'     => $row->created,
                    'updated_at'     => $row->modified,
                ];

                $this->imported++;
            }

            // バッチインサート
            if (!$this->dryRun && !empty($insertBatch)) {
                DB::table('casts')->insert($insertBatch);
            }

            if ($processed % $chunkSize === 0 || $stopLoop) {
                $this->line("  処理中: {$processed}件...");
            }

            if ($stopLoop) {
                return false;
            }
        });

        $this->info('');
        $this->info("完了: {$this->imported}件インポート / {$this->skipped}件スキップ / {$this->errors}件エラー（shop不明）");

        return self::SUCCESS;
    }
}
