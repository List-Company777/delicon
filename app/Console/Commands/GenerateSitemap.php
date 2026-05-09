<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature   = 'sitemap:generate';
    protected $description = 'サイトマップインデックス（sitemap.xml）を生成する';

    public function handle(): void
    {
        $base = rtrim(config('app.url'), '/');
        $now  = now()->toAtomString();

        $sitemaps = [
            'sitemap-pages.xml',
            'sitemap-detail.xml',
            'sitemap-girl.xml',
            'sitemap-shop.xml',
        ];

        // キャスト詳細サイトマップ（分割ファイルを動的に検出）
        $castFiles = glob(public_path('sitemap-cast-*.xml')) ?: [];
        natsort($castFiles);
        foreach ($castFiles as $path) {
            $sitemaps[] = basename($path);
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($sitemaps as $filename) {
            $xml .= "  <sitemap>\n";
            $xml .= "    <loc>{$base}/{$filename}</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }
        $xml .= '</sitemapindex>';

        file_put_contents(public_path('sitemap.xml'), $xml);
        $this->info('sitemap.xml（インデックス）を生成しました（' . count($sitemaps) . ' サイトマップ）');
    }
}
