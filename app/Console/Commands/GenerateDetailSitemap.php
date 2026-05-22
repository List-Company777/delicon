<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Shop;
use Illuminate\Console\Command;

class GenerateDetailSitemap extends Command
{
    protected $signature   = 'sitemap:generate-detail';
    protected $description = '店舗詳細・記事詳細ページのサイトマップを生成（画像対応・店舗は説明100字以上のみ）';

    public function handle(): void
    {
        $base  = rtrim(config('app.url'), '/');
        $now   = now()->toAtomString();
        $urls  = [];

        $shopCount = 0;
        Shop::where('status', 'active')
            ->whereRaw("CHAR_LENGTH(COALESCE(base,'')) + CHAR_LENGTH(COALESCE(system_text,'')) >= 100")
            ->select('id', 'name', 'main_image', 'shop_file_name', 'updated_at')
            ->orderBy('id')
            ->chunk(500, function ($shops) use ($base, &$urls, &$shopCount) {
                foreach ($shops as $shop) {
                    $images = [];
                    $imgPath = $shop->main_image ?: null;
                    if ($imgPath) {
                        $images[] = ['loc' => "{$base}/storage/{$imgPath}", 'title' => $shop->name];
                    } elseif ($shop->shop_file_name) {
                        $images[] = ['loc' => $base . $shop->shop_file_name, 'title' => $shop->name];
                    }
                    $urls[] = [
                        'loc'      => "{$base}/shops/{$shop->id}/",
                        'lastmod'  => $shop->updated_at?->toAtomString() ?? now()->toAtomString(),
                        'priority' => '0.7',
                        'images'   => $images,
                    ];
                    $shopCount++;
                }
            });

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
                        'images'   => [],
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
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
            $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            foreach ($url['images'] ?? [] as $img) {
                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>" . htmlspecialchars($img['loc']) . "</image:loc>\n";
                if (!empty($img['title'])) {
                    $xml .= "      <image:title>" . htmlspecialchars($img['title']) . "</image:title>\n";
                }
                $xml .= "    </image:image>\n";
            }
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';
        file_put_contents(public_path('sitemap-detail.xml'), $xml);
    }
}
