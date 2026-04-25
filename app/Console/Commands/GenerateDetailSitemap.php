<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Job;
use App\Models\Shop;
use Illuminate\Console\Command;

class GenerateDetailSitemap extends Command
{
    protected $signature   = 'sitemap:generate-detail';
    protected $description = '公開中の求人・店舗詳細ページのサイトマップを生成する';

    public function handle(): void
    {
        $this->info('詳細ページ サイトマップ生成開始...');

        $base = rtrim(config('app.url'), '/');
        $now  = now()->toAtomString();
        $urls = [];

        // 公開中の求人（job/show）
        $jobCount = 0;
        Job::where('status', 'active')
            ->select('id', 'updated_at')
            ->orderBy('id')
            ->chunk(500, function ($jobs) use ($base, &$urls, &$jobCount) {
                foreach ($jobs as $job) {
                    $urls[] = [
                        'loc'     => "{$base}/job/{$job->id}/",
                        'lastmod' => $job->updated_at?->toAtomString() ?? now()->toAtomString(),
                        'priority'=> '0.7',
                    ];
                    $jobCount++;
                }
            });

        // 公開中の店舗（shop/show）— shops と shop_details 両方 active
        $shopCount = 0;
        Shop::where('status', 'active')
            ->whereHas('detail', fn($q) => $q->where('status', 'active'))
            ->select('id', 'updated_at')
            ->orderBy('id')
            ->chunk(500, function ($shops) use ($base, &$urls, &$shopCount) {
                foreach ($shops as $shop) {
                    $urls[] = [
                        'loc'     => "{$base}/shop/{$shop->id}/",
                        'lastmod' => $shop->updated_at?->toAtomString() ?? now()->toAtomString(),
                        'priority'=> '0.7',
                    ];
                    $shopCount++;
                }
            });

        // 公開済み記事（article/show）
        $articleCount = 0;
        Article::published()
            ->select('slug', 'updated_at', 'updated_at_manual')
            ->orderBy('id')
            ->chunk(500, function ($articles) use ($base, &$urls, &$articleCount) {
                foreach ($articles as $article) {
                    $lastmod = $article->updated_at_manual
                        ? $article->updated_at_manual->toAtomString()
                        : ($article->updated_at?->toAtomString() ?? now()->toAtomString());
                    $urls[] = [
                        'loc'      => "{$base}/article/{$article->slug}/",
                        'lastmod'  => $lastmod,
                        'priority' => '0.6',
                    ];
                    $articleCount++;
                }
            });

        $this->writeXml($urls, $now);

        $total = count($urls);
        $this->info("完了: 求人 {$jobCount} 件 + 店舗 {$shopCount} 件 + 記事 {$articleCount} 件 = {$total} 件のURLを出力");
    }

    private function writeXml(array $urls, string $now): void
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
            $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        file_put_contents(public_path('sitemap-detail.xml'), $xml);
    }
}
