<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportOldShops extends Command
{
    protected $signature = 'import:old-shops
                            {--dry-run : DBへの書き込みを行わない}
                            {--limit=0 : 取得件数上限（0=全件）}';

    protected $description = '旧CakePHPサイト(delicon_old)からshopsデータをインポートする';

    // 旧prefecture_id -> 新prefecture_id
    private const PREFECTURE_MAP = [
        1  => 13, 2  => 13, 3  => 13, 4  => 13, 5  => 13, 6  => 13,
        7  => 7,  8  => 8,  9  => 9,  10 => 10, 11 => 11, 12 => 12, 13 => 13,
        14 => 66, 15 => 66, 16 => 66, 17 => 66, 18 => 66, 19 => 66, 20 => 66, 21 => 66,
        22 => 22, 23 => 23, 24 => 24,
        25 => 67, 26 => 67, 27 => 67,
        28 => 28,
        29 => 68, 30 => 68, 31 => 68,
        32 => 32, 33 => 69, 34 => 69, 35 => 35,
    ];

    // 旧type1 -> 新genre_id
    private const GENRE_MAP = [
        1  => 1,   // 人妻
        2  => 9,   // アジアン
        3  => 5,   // SM
        8  => 6,   // ニューハーフ
        10 => 2,   // 素人
        13 => 8,   // AV・モデル
        16 => 4,   // ぽっちゃり
        17 => 10,  // 金髪
        18 => 7,   // コスプレ
        19 => 3,   // 熟女
    ];

    private bool $dryRun   = false;
    private int  $imported = 0;
    private int  $skipped  = 0;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        $limit        = (int) $this->option('limit');

        if ($this->dryRun) {
            $this->warn('[DRY-RUN] DBへの書き込みは行いません');
        }

        $total = DB::connection('mysql_old')->table('shops')->count();
        if ($limit > 0) {
            $total = min($total, $limit);
        }
        $this->info("旧DB shop件数: {$total}件（上限: " . ($limit > 0 ? $limit : '全件') . ')');

        // 既存 old_id をキャッシュ
        $existingOldIds = DB::table('shops')
            ->whereNotNull('old_id')
            ->pluck('old_id')
            ->flip()
            ->all();

        // area_id キャッシュ (prefecture_id -> area.id)
        $areaCache  = [];
        $processed  = 0;
        $stopLoop   = false;

        DB::connection('mysql_old')->table('shops')->orderBy('id')->chunk(100, function ($rows) use (
            &$processed, $total, $limit, $existingOldIds, &$areaCache, &$stopLoop
        ) {
            foreach ($rows as $row) {
                if ($limit > 0 && $processed >= $limit) {
                    $stopLoop = true;
                    return false;
                }

                $processed++;

                if (array_key_exists($row->id, $existingOldIds)) {
                    $this->line("  スキップ [{$processed}/{$total}] {$row->shop_name} (old_id={$row->id})");
                    $this->skipped++;
                    continue;
                }

                $prefId = self::PREFECTURE_MAP[$row->area] ?? 13;

                if (!isset($areaCache[$prefId])) {
                    $areaCache[$prefId] = DB::table('areas')
                        ->where('prefecture_id', $prefId)
                        ->value('id');
                }
                $areaId = $areaCache[$prefId];

                $shopFileName = null;
                if (!empty($row->shop_file_name) && !str_starts_with($row->shop_file_name, '/img/common/')) {
                    $shopFileName = $row->shop_file_name;
                }

                $genreId   = self::GENRE_MAP[$row->type1] ?? null;
                $price60   = ($row->price_60 > 0)  ? $row->price_60  : null;
                $priceHigh = ($row->price_high > 0) ? $row->price_high : null;
                $status    = ($row->flag === '0') ? 'active' : 'inactive';

                $data = [
                    'old_id'         => $row->id,
                    'name'           => $row->shop_name ?? '不明',
                    'kana'           => $row->kana,
                    'genre_id'       => $genreId,
                    'prefecture_id'  => $prefId,
                    'area_id'        => $areaId,
                    'tel'            => $row->tel,
                    'address'        => $row->address,
                    'base'           => $row->base,
                    'catche'         => $row->catche,
                    'system_text'    => $row->comment,
                    'coupon'         => $row->coupon,
                    'open_time'      => $row->open   ? mb_substr($row->open,   0, 50) : null,
                    'close_time'     => $row->closed ? mb_substr($row->closed, 0, 50) : null,
                    'all_time'       => ($row->all_time === 'true') ? 1 : 0,
                    'rest_day'       => $row->rest_day,
                    'price_60'       => $price60,
                    'price_high'     => $priceHigh,
                    'eigyo_area'     => $row->eigyo_area,
                    'eigyo_space'    => $row->eigyo_space ? mb_substr($row->eigyo_space, 0, 200) : null,
                    'shop_file_name' => $shopFileName,
                    'ranking_count'  => $row->ranking_count ?? 0,
                    'status'         => $status,
                    'plan'           => 3,
                    'created_at'     => $row->created,
                    'updated_at'     => $row->modified,
                ];

                if ($this->dryRun) {
                    $this->line("  [DRY] [{$processed}/{$total}] {$row->shop_name}");
                } else {
                    DB::table('shops')->insert($data);
                    $this->line("  [{$processed}/{$total}] {$row->shop_name}");
                }

                $this->imported++;
            }

            if ($stopLoop) {
                return false;
            }
        });

        $this->info('');
        $this->info("完了: {$this->imported}件インポート / {$this->skipped}件スキップ");

        return self::SUCCESS;
    }
}
