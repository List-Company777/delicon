<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Shop;
use Illuminate\Console\Command;

class GeneratePagesSitemap extends Command
{
    protected $signature   = 'sitemap:generate-pages';
    protected $description = '固定ページ・記事・店舗のサイトマップを生成';

    public function handle(): void
    {
        $now  = now()->toAtomString();
        $base = config('app.url');
        $urls = [];

        // トップページ
        $urls[] = ['loc' => $base . '/', 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '1.0'];

        // 記事一覧
        $urls[] = ['loc' => $base . '/article/', 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '0.8'];

        // 記事個別（公開済み）
        Article::where('is_published', true)
            ->orderByDesc('published_at')
            ->each(function ($article) use ($base, &$urls) {
                $urls[] = [
                    'loc'        => $base . '/article/' . $article->slug . '/',
                    'lastmod'    => ($article->updated_at_manual ?? $article->updated_at)->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.7',
                ];
            });

        // 店舗詳細（active）
        Shop::where('status', 'active')
            ->orderByDesc('updated_at')
            ->each(function ($shop) use ($base, &$urls) {
                $urls[] = [
                    'loc'        => $base . '/shop/' . $shop->id . '/',
                    'lastmod'    => $shop->updated_at->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.6',
                ];
            });

        // 夜ビズ LP
        $urls[] = ['loc' => $base . '/yorubiz/', 'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.6'];

        // 問い合わせ
        $urls[] = ['loc' => $base . '/inquiry/', 'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.3'];

        $this->writeXml($urls);
        $this->info('sitemap-pages.xml を生成しました（' . count($urls) . '件）');
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
