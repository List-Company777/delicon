<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GeneratePagesSitemap extends Command
{
    protected $signature   = 'sitemap:generate-pages';
    protected $description = '固定ページ・都道府県トップ・ランキングのサイトマップを生成';

    private const MIN_RESULTS = 5;

    public function handle(): void
    {
        $now  = now()->toAtomString();
        $base = rtrim(config('app.url'), '/');
        $urls = [];

        // 固定ページ（noindexページは除外）
        foreach ([
            ['/',            '1.0', 'daily'],
            ['/ranking/',    '0.8', 'daily'],
            ['/inquiry/',    '0.4', 'monthly'],
            ['/keisai/',     '0.7', 'monthly'],
            ['/yorubiz/',    '0.7', 'monthly'],
            ['/privacy/',    '0.3', 'yearly'],
            ['/terms/',      '0.3', 'yearly'],
            ['/advertiser/', '0.3', 'yearly'],
            ['/company/',    '0.3', 'yearly'],
        ] as [$path, $priority, $freq]) {
            $urls[] = ['loc' => $base . $path, 'lastmod' => $now, 'changefreq' => $freq, 'priority' => $priority];
        }

        // 全国トップページ
        $allCount = DB::table('shops')->where('status', 'active')->count();
        if ($allCount >= self::MIN_RESULTS) {
            $urls[] = ['loc' => $base . '/all/', 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '0.9'];
        }

        // 都道府県トップページ
        $prefRows = DB::select("
            SELECT p.slug, COUNT(DISTINCT s.id) as cnt
            FROM prefectures p
            JOIN areas a ON a.prefecture_id = p.id
            JOIN shops s ON s.area_id = a.id
            WHERE s.status = 'active'
            GROUP BY p.id, p.slug
            HAVING cnt >= ?
        ", [self::MIN_RESULTS]);

        foreach ($prefRows as $row) {
            $urls[] = [
                'loc'        => "{$base}/{$row->slug}/",
                'lastmod'    => $now,
                'changefreq' => 'daily',
                'priority'   => '0.8',
            ];
        }

        // 都道府県ランキングページ（キャスト5人以上）
        $prefRankingRows = DB::select("
            SELECT p.slug, COUNT(DISTINCT c.id) as cnt
            FROM prefectures p
            JOIN shops s ON s.prefecture_id = p.id
            JOIN casts c ON c.shop_id = s.id
            WHERE s.status = 'active' AND c.status = 'active'
            GROUP BY p.id, p.slug
            HAVING cnt >= ?
        ", [self::MIN_RESULTS]);

        foreach ($prefRankingRows as $row) {
            $urls[] = [
                'loc'        => "{$base}/{$row->slug}/ranking/",
                'lastmod'    => $now,
                'changefreq' => 'daily',
                'priority'   => '0.7',
            ];
        }

        // 小エリアランキングページ（キャスト5人以上）
        $areaRankingRows = DB::select("
            SELECT a.slug, COUNT(DISTINCT c.id) as cnt
            FROM areas a
            JOIN shops s ON s.area_id = a.id
            JOIN casts c ON c.shop_id = s.id
            WHERE s.status = 'active' AND c.status = 'active'
            GROUP BY a.id, a.slug
            HAVING cnt >= ?
        ", [self::MIN_RESULTS]);

        foreach ($areaRankingRows as $row) {
            $urls[] = [
                'loc'        => "{$base}/{$row->slug}/ranking/",
                'lastmod'    => $now,
                'changefreq' => 'daily',
                'priority'   => '0.6',
            ];
        }

        $this->writeXml($urls);
        $this->info('sitemap-pages.xml を生成しました（' . count($urls) . ' 件）');
    }

    private function writeXml(array $urls): void
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
            $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';
        file_put_contents(public_path('sitemap-pages.xml'), $xml);
    }
}
