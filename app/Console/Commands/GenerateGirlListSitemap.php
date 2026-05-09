<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateGirlListSitemap extends Command
{
    protected $signature   = 'sitemap:generate-girl-list';
    protected $description = 'girl-list ページ（ベース・タイプ別・フィルター、5件以上）のサイトマップを生成';

    private const MIN_RESULTS = 5;

    private const AGE_RANGES = [
        'teens'     => [18, 19],
        '20s_early' => [20, 24],
        '20s_late'  => [25, 29],
        '30s_early' => [30, 34],
        '30s_late'  => [35, 39],
        '40s'       => [40, 49],
        '50s'       => [50, 59],
        '60s'       => [60, 69],
        '70s'       => [70, 120],
    ];

    private const TALL_RANGES = [
        'short' => [null, 150],
        'mid'   => [151, 160],
        'tall'  => [161, 170],
        'super' => [171, null],
    ];

    private const CUP_GROUPS = [
        'ab'     => ['A', 'B'],
        'c'      => ['C'],
        'd'      => ['D'],
        'ef'     => ['E', 'F'],
        'g_plus' => ['G', 'H', 'I', 'J', 'K', 'L', 'M'],
    ];

    public function handle(): int
    {
        $base = rtrim(config('app.url'), '/');
        $urls = [];

        $this->info('ベースページ集計中...');
        $urls = array_merge($urls, $this->baseUrls($base));

        $this->info('タイプ別ページ集計中...');
        $urls = array_merge($urls, $this->typeUrls($base));

        $this->info('body フィルター集計中...');
        $urls = array_merge($urls, $this->bodyUrls($base));

        $this->info('age フィルター集計中...');
        $urls = array_merge($urls, $this->ageUrls($base));

        $this->info('tall フィルター集計中...');
        $urls = array_merge($urls, $this->tallUrls($base));

        $this->info('cup フィルター集計中...');
        $urls = array_merge($urls, $this->cupUrls($base));

        $urls = array_unique($urls);
        $this->writeXml($urls);
        $this->info('sitemap-girl.xml 生成完了: ' . count($urls) . ' 件');

        return 0;
    }

    // ── ベースページ（フィルターなし） ───────────────────────────
    private function baseUrls(string $base): array
    {
        $urls = [];

        // 全国
        $allCount = DB::table('casts')
            ->join('shops', 'casts.shop_id', '=', 'shops.id')
            ->where('casts.status', 'active')
            ->where('shops.status', 'active')
            ->count();
        if ($allCount >= self::MIN_RESULTS) {
            $urls[] = "{$base}/all/girl-list/";
        }

        // エリア別
        $areaRows = DB::select("
            SELECT a.slug, COUNT(*) AS cnt
            FROM casts c
            JOIN shops s ON c.shop_id = s.id
            JOIN areas a ON s.area_id = a.id
            WHERE c.status = 'active' AND s.status = 'active'
            GROUP BY a.id, a.slug
            HAVING cnt >= ?
        ", [self::MIN_RESULTS]);

        foreach ($areaRows as $row) {
            $urls[] = "{$base}/{$row->slug}/girl-list/";
        }

        // 都道府県別
        $prefRows = DB::select("
            SELECT p.slug, COUNT(*) AS cnt
            FROM casts c
            JOIN shops s ON c.shop_id = s.id
            JOIN areas a ON s.area_id = a.id
            JOIN prefectures p ON a.prefecture_id = p.id
            WHERE c.status = 'active' AND s.status = 'active'
            GROUP BY p.id, p.slug
            HAVING cnt >= ?
        ", [self::MIN_RESULTS]);

        foreach ($prefRows as $row) {
            $urls[] = "{$base}/{$row->slug}/girl-list/";
        }

        return array_unique($urls);
    }

    // ── タイプ別ページ ───────────────────────────────────────────
    private function typeUrls(string $base): array
    {
        $girlTypes = DB::table('girl_types')
            ->get(['id', 'slug', 'age_min', 'age_max', 'tall_min', 'tall_max', 'body_type_id', 'cast_type_id'])
            ->all();

        $urls = [];

        foreach ($girlTypes as $type) {
            $where  = "c.status = 'active' AND s.status = 'active'";
            $binds  = [];

            if ($type->age_min !== null) { $where .= ' AND c.age >= ?'; $binds[] = $type->age_min; }
            if ($type->age_max !== null) { $where .= ' AND c.age <= ?'; $binds[] = $type->age_max; }
            if ($type->tall_min !== null) { $where .= ' AND c.tall >= ?'; $binds[] = $type->tall_min; }
            if ($type->tall_max !== null) { $where .= ' AND c.tall <= ?'; $binds[] = $type->tall_max; }
            if ($type->body_type_id !== null) { $where .= ' AND c.body_id = ?'; $binds[] = $type->body_type_id; }
            if ($type->cast_type_id !== null) { $where .= ' AND c.type_id = ?'; $binds[] = $type->cast_type_id; }

            // 全国
            $allRows = DB::select("
                SELECT COUNT(*) AS cnt
                FROM casts c
                JOIN shops s ON c.shop_id = s.id
                WHERE {$where}
            ", $binds);
            if (($allRows[0]->cnt ?? 0) >= self::MIN_RESULTS) {
                $urls[] = "{$base}/all/girl-list/type/{$type->slug}/";
            }

            // エリア別
            $areaRows = DB::select("
                SELECT a.slug, COUNT(*) AS cnt
                FROM casts c
                JOIN shops s ON c.shop_id = s.id
                JOIN areas a ON s.area_id = a.id
                WHERE {$where}
                GROUP BY a.id, a.slug
                HAVING cnt >= ?
            ", array_merge($binds, [self::MIN_RESULTS]));

            foreach ($areaRows as $row) {
                $urls[] = "{$base}/{$row->slug}/girl-list/type/{$type->slug}/";
            }

            // 都道府県別
            $prefRows = DB::select("
                SELECT p.slug, COUNT(*) AS cnt
                FROM casts c
                JOIN shops s ON c.shop_id = s.id
                JOIN areas a ON s.area_id = a.id
                JOIN prefectures p ON a.prefecture_id = p.id
                WHERE {$where}
                GROUP BY p.id, p.slug
                HAVING cnt >= ?
            ", array_merge($binds, [self::MIN_RESULTS]));

            foreach ($prefRows as $row) {
                $urls[] = "{$base}/{$row->slug}/girl-list/type/{$type->slug}/";
            }
        }

        return array_unique($urls);
    }

    // ── body フィルター ──────────────────────────────────────────
    private function bodyUrls(string $base): array
    {
        // エリア別集計
        $areaRows = DB::select("
            SELECT a.slug AS slug, c.body_id, COUNT(*) AS cnt
            FROM casts c
            JOIN shops s ON c.shop_id = s.id
            JOIN areas a ON s.area_id = a.id
            WHERE c.status = 'active' AND s.status = 'active' AND c.body_id IS NOT NULL
            GROUP BY a.slug, c.body_id
            HAVING cnt >= ?
        ", [self::MIN_RESULTS]);

        // 都道府県別集計
        $prefRows = DB::select("
            SELECT p.slug AS slug, c.body_id, COUNT(*) AS cnt
            FROM casts c
            JOIN shops s ON c.shop_id = s.id
            JOIN areas a ON s.area_id = a.id
            JOIN prefectures p ON a.prefecture_id = p.id
            WHERE c.status = 'active' AND s.status = 'active' AND c.body_id IS NOT NULL
            GROUP BY p.slug, c.body_id
            HAVING cnt >= ?
        ", [self::MIN_RESULTS]);

        // 全国集計
        $allRows = DB::select("
            SELECT c.body_id, COUNT(*) AS cnt
            FROM casts c
            JOIN shops s ON c.shop_id = s.id
            WHERE c.status = 'active' AND s.status = 'active' AND c.body_id IS NOT NULL
            GROUP BY c.body_id
            HAVING cnt >= ?
        ", [self::MIN_RESULTS]);

        $urls = [];
        foreach (array_merge($areaRows, $prefRows) as $row) {
            $urls[] = "{$base}/{$row->slug}/girl-list/?body={$row->body_id}";
        }
        foreach ($allRows as $row) {
            $urls[] = "{$base}/all/girl-list/?body={$row->body_id}";
        }
        return array_unique($urls);
    }

    // ── age フィルター ───────────────────────────────────────────
    private function ageUrls(string $base): array
    {
        $urls = [];
        foreach (self::AGE_RANGES as $key => [$min, $max]) {
            $areaRows = DB::select("
                SELECT a.slug AS slug, COUNT(*) AS cnt
                FROM casts c
                JOIN shops s ON c.shop_id = s.id
                JOIN areas a ON s.area_id = a.id
                WHERE c.status = 'active' AND s.status = 'active'
                  AND c.age BETWEEN ? AND ?
                GROUP BY a.slug
                HAVING cnt >= ?
            ", [$min, $max, self::MIN_RESULTS]);

            $prefRows = DB::select("
                SELECT p.slug AS slug, COUNT(*) AS cnt
                FROM casts c
                JOIN shops s ON c.shop_id = s.id
                JOIN areas a ON s.area_id = a.id
                JOIN prefectures p ON a.prefecture_id = p.id
                WHERE c.status = 'active' AND s.status = 'active'
                  AND c.age BETWEEN ? AND ?
                GROUP BY p.slug
                HAVING cnt >= ?
            ", [$min, $max, self::MIN_RESULTS]);

            $allCnt = DB::table('casts')
                ->join('shops', 'casts.shop_id', '=', 'shops.id')
                ->where('casts.status', 'active')->where('shops.status', 'active')
                ->whereBetween('casts.age', [$min, $max])
                ->count();

            foreach (array_merge($areaRows, $prefRows) as $row) {
                $urls[] = "{$base}/{$row->slug}/girl-list/?age={$key}";
            }
            if ($allCnt >= self::MIN_RESULTS) {
                $urls[] = "{$base}/all/girl-list/?age={$key}";
            }
        }
        return array_unique($urls);
    }

    // ── tall フィルター ──────────────────────────────────────────
    private function tallUrls(string $base): array
    {
        $urls = [];
        foreach (self::TALL_RANGES as $key => [$min, $max]) {
            $where = "c.status = 'active' AND s.status = 'active'";
            $binds = [];
            if ($min !== null) { $where .= ' AND c.tall >= ?'; $binds[] = $min; }
            if ($max !== null) { $where .= ' AND c.tall <= ?'; $binds[] = $max; }

            $areaRows = DB::select("
                SELECT a.slug AS slug, COUNT(*) AS cnt
                FROM casts c JOIN shops s ON c.shop_id = s.id JOIN areas a ON s.area_id = a.id
                WHERE {$where} GROUP BY a.slug HAVING cnt >= ?
            ", array_merge($binds, [self::MIN_RESULTS]));

            $prefRows = DB::select("
                SELECT p.slug AS slug, COUNT(*) AS cnt
                FROM casts c JOIN shops s ON c.shop_id = s.id
                JOIN areas a ON s.area_id = a.id
                JOIN prefectures p ON a.prefecture_id = p.id
                WHERE {$where} GROUP BY p.slug HAVING cnt >= ?
            ", array_merge($binds, [self::MIN_RESULTS]));

            $allQuery = DB::table('casts')->join('shops', 'casts.shop_id', '=', 'shops.id')
                ->where('casts.status', 'active')->where('shops.status', 'active');
            if ($min !== null) $allQuery->where('casts.tall', '>=', $min);
            if ($max !== null) $allQuery->where('casts.tall', '<=', $max);
            $allCnt = $allQuery->count();

            foreach (array_merge($areaRows, $prefRows) as $row) {
                $urls[] = "{$base}/{$row->slug}/girl-list/?tall={$key}";
            }
            if ($allCnt >= self::MIN_RESULTS) {
                $urls[] = "{$base}/all/girl-list/?tall={$key}";
            }
        }
        return array_unique($urls);
    }

    // ── cup フィルター ───────────────────────────────────────────
    private function cupUrls(string $base): array
    {
        $areaRows = DB::select("
            SELECT a.slug AS slug, c.cup, COUNT(*) AS cnt
            FROM casts c
            JOIN shops s ON c.shop_id = s.id
            JOIN areas a ON s.area_id = a.id
            WHERE c.status = 'active' AND s.status = 'active' AND c.cup IS NOT NULL AND c.cup != ''
            GROUP BY a.slug, c.cup
        ");

        $prefRows = DB::select("
            SELECT p.slug AS slug, c.cup, COUNT(*) AS cnt
            FROM casts c
            JOIN shops s ON c.shop_id = s.id
            JOIN areas a ON s.area_id = a.id
            JOIN prefectures p ON a.prefecture_id = p.id
            WHERE c.status = 'active' AND s.status = 'active' AND c.cup IS NOT NULL AND c.cup != ''
            GROUP BY p.slug, c.cup
        ");

        $allRows = DB::select("
            SELECT c.cup, COUNT(*) AS cnt
            FROM casts c JOIN shops s ON c.shop_id = s.id
            WHERE c.status = 'active' AND s.status = 'active' AND c.cup IS NOT NULL AND c.cup != ''
            GROUP BY c.cup
        ");

        $cupToGroup = [];
        foreach (self::CUP_GROUPS as $groupKey => $cups) {
            foreach ($cups as $cup) {
                $cupToGroup[$cup] = $groupKey;
            }
        }

        $slugGroupCnt = [];
        foreach (array_merge($areaRows, $prefRows) as $row) {
            $g = $cupToGroup[$row->cup] ?? null;
            if ($g === null) continue;
            $slugGroupCnt[$row->slug][$g] = ($slugGroupCnt[$row->slug][$g] ?? 0) + $row->cnt;
        }

        $allGroupCnt = [];
        foreach ($allRows as $row) {
            $g = $cupToGroup[$row->cup] ?? null;
            if ($g === null) continue;
            $allGroupCnt[$g] = ($allGroupCnt[$g] ?? 0) + $row->cnt;
        }

        $urls = [];
        foreach ($slugGroupCnt as $slug => $groups) {
            foreach ($groups as $groupKey => $cnt) {
                if ($cnt >= self::MIN_RESULTS) {
                    $urls[] = "{$base}/{$slug}/girl-list/?cup={$groupKey}";
                }
            }
        }
        foreach ($allGroupCnt as $groupKey => $cnt) {
            if ($cnt >= self::MIN_RESULTS) {
                $urls[] = "{$base}/all/girl-list/?cup={$groupKey}";
            }
        }
        return array_unique($urls);
    }

    // ── XML出力 ──────────────────────────────────────────────────
    private function writeXml(array $urls): void
    {
        $now = now()->toAtomString();
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($url) . "</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "    <changefreq>daily</changefreq>\n";
            $xml .= "    <priority>0.6</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';
        file_put_contents(public_path('sitemap-girl.xml'), $xml);
    }
}
