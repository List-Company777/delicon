<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Shop;
use Illuminate\Console\Command;

class GenerateDetailSitemap extends Command
{
    protected $signature   = 'sitemap:generate-detail';
    protected $description = '店舗詳細・記事詳細ページのサイトマップを生成（店舗は説明100字以上のみ）';

    public function handle(): void
    {
        $base  = rtrim(config('app.url'), '/');
        $now   = now()->toAtomString();
        $urls  = [];

        // 店舗詳細 — base + system_text が合計100文字以上のもののみ
        $shopCount = 0;
        Shop::where('status', 'active')
            ->whereRaw("CHAR_LENGTH(COALESCE(base,'')) + CHAR_LENGTH(COALESCE(system_text,'')) >= 100")
            ->select('id', 'updated_at')
            ->orderBy('id')
            ->chunk(500, function ($shops) use ($base, &$urls, &$shopCount) {
                foreach ($shops as $shop) {
                    $urls[] = [
                        'loc'      => "{$base}/shops/{$shop->id}/",
                        'lastmod'  => $shop->updated_at?->toAtomString() ?? now()->toAtomString(),
                        'priority' => '0.7',
                    ];
                    $shopCount++;
                }
            });

        // 記事詳細
        $articleCount = 0;
        Article::where('is_published', true)
            ->where('published_at', '<=', now())
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

        $this->writeXml($urls);
        $this->info("sitemap-detail.xml 生成完了: 店舗 {$shopCount} 件 + 記事 {$articleCount} 件 = " . count($urls) . " 件");
    }

    private function writeXml(array $urls): void
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
