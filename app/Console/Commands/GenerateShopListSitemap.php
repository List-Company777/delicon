<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateShopListSitemap extends Command
{
    protected $signature   = 'sitemap:generate-shop-list';
    protected $description = 'shop-list 年齢フィルターページ（1フィルター×5件以上）のサイトマップを生成';

    private const MIN_RESULTS = 5;

    private const AGE_RANGES = [
        '18-19' => [18, 19],
        '20-24' => [20, 24],
        '25-34' => [25, 34],
        '35-44' => [35, 44],
        '45+'   => [45, 200],
    ];

    public function handle(): int
    {
        $base = rtrim(config('app.url'), '/');
        $urls = [];

        $this->info('shop-list age_range フィルター集計中...');
        $urls = array_merge($urls, $this->ageRangeUrls($base));

        $this->writeXml($urls);
        $this->info('sitemap-shop.xml 生成完了: ' . count($urls) . ' 件');

        return 0;
    }

    private function dominantAgeShopIds(int $min, int $max): array
    {
        return DB::table('casts')
            ->selectRaw('shop_id')
            ->where('status', 'active')
            ->where('age', '>', 0)
            ->groupBy('shop_id')
            ->havingRaw("
                SUM(CASE WHEN age BETWEEN ? AND ? THEN 1 ELSE 0 END) >= GREATEST(
                    SUM(CASE WHEN age BETWEEN 18 AND 19 THEN 1 ELSE 0 END),
                    SUM(CASE WHEN age BETWEEN 20 AND 24 THEN 1 ELSE 0 END),
                    SUM(CASE WHEN age BETWEEN 25 AND 34 THEN 1 ELSE 0 END),
                    SUM(CASE WHEN age BETWEEN 35 AND 44 THEN 1 ELSE 0 END),
                    SUM(CASE WHEN age >= 45 THEN 1 ELSE 0 END)
                ) AND SUM(CASE WHEN age BETWEEN ? AND ? THEN 1 ELSE 0 END) > 0
            ", [$min, $max, $min, $max])
            ->pluck('shop_id')
            ->all();
    }

    private function ageRangeUrls(string $base): array
    {
        $urls = [];

        foreach (self::AGE_RANGES as $rangeKey => [$min, $max]) {
            $shopIds = $this->dominantAgeShopIds($min, $max);

            if (empty($shopIds)) continue;

            // 全国
            $allCount = DB::table('shops')
                ->where('status', 'active')
                ->whereIn('id', $shopIds)
                ->count();
            if ($allCount >= self::MIN_RESULTS) {
                $urls[] = "{$base}/all/shop-list/?age_range={$rangeKey}";
            }

            // エリア別
            $areaRows = DB::table('shops')
                ->join('areas', 'shops.area_id', '=', 'areas.id')
                ->where('shops.status', 'active')
                ->whereIn('shops.id', $shopIds)
                ->whereNotNull('areas.slug')
                ->select('areas.slug', DB::raw('COUNT(*) as cnt'))
                ->groupBy('areas.id', 'areas.slug')
                ->having('cnt', '>=', self::MIN_RESULTS)
                ->get();

            foreach ($areaRows as $row) {
                $urls[] = "{$base}/{$row->slug}/shop-list/?age_range={$rangeKey}";
            }

            // 都道府県別
            $prefRows = DB::table('shops')
                ->join('areas', 'shops.area_id', '=', 'areas.id')
                ->join('prefectures', 'areas.prefecture_id', '=', 'prefectures.id')
                ->where('shops.status', 'active')
                ->whereIn('shops.id', $shopIds)
                ->whereNotNull('prefectures.slug')
                ->select('prefectures.slug', DB::raw('COUNT(*) as cnt'))
                ->groupBy('prefectures.id', 'prefectures.slug')
                ->having('cnt', '>=', self::MIN_RESULTS)
                ->get();

            foreach ($prefRows as $row) {
                $urls[] = "{$base}/{$row->slug}/shop-list/?age_range={$rangeKey}";
            }
        }

        return array_unique($urls);
    }

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
        file_put_contents(public_path('sitemap-shop.xml'), $xml);
    }
}
