<?php

namespace App\Console\Commands;

use App\Models\Cast;
use Illuminate\Console\Command;

class GenerateCastDetailSitemap extends Command
{
    protected $signature   = 'sitemap:generate-cast-detail';
    protected $description = 'キャスト詳細ページのサイトマップを生成（説明100字以上、50,000件で分割）';

    private const SPLIT_SIZE = 50000;

    public function handle(): void
    {
        $base = rtrim(config('app.url'), '/');
        $now  = now()->toAtomString();

        // 既存の分割ファイルをリセット
        foreach (glob(public_path('sitemap-cast-*.xml')) ?: [] as $old) {
            unlink($old);
        }

        $fileIndex  = 1;
        $urls       = [];
        $totalCount = 0;

        Cast::where('status', 'active')
            ->whereRaw("CHAR_LENGTH(COALESCE(comment,'')) >= 100")
            ->select('id', 'updated_at')
            ->orderBy('id')
            ->chunk(1000, function ($casts) use ($base, $now, &$urls, &$fileIndex, &$totalCount) {
                foreach ($casts as $cast) {
                    $urls[] = [
                        'loc'     => "{$base}/cast/{$cast->id}/",
                        'lastmod' => $cast->updated_at?->toAtomString() ?? $now,
                    ];
                    $totalCount++;

                    if (count($urls) >= self::SPLIT_SIZE) {
                        $this->writeXml($urls, $fileIndex);
                        $this->info("sitemap-cast-{$fileIndex}.xml: " . count($urls) . " 件");
                        $fileIndex++;
                        $urls = [];
                    }
                }
            });

        if (!empty($urls)) {
            $this->writeXml($urls, $fileIndex);
            $this->info("sitemap-cast-{$fileIndex}.xml: " . count($urls) . " 件");
        }

        $this->info("キャスト詳細サイトマップ生成完了: 合計 {$totalCount} 件, {$fileIndex} ファイル");
    }

    private function writeXml(array $urls, int $index): void
    {
        $now  = now()->toAtomString();
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
            $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.5</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';
        file_put_contents(public_path("sitemap-cast-{$index}.xml"), $xml);
    }
}
