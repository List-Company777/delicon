<?php

namespace App\Console\Commands;

use App\Models\Cast;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateCastDetailSitemap extends Command
{
    protected $signature   = 'sitemap:generate-cast-detail';
    protected $description = 'キャスト詳細ページのサイトマップを生成（画像対応・説明100字以上・50,000件で分割）';

    private const SPLIT_SIZE = 50000;

    public function handle(): void
    {
        $base      = rtrim(config('app.url'), '/');
        $now       = now()->toAtomString();
        $threshold = now()->subDays(30)->toDateString();

        $recentWorking = DB::table('casts')
            ->where('status', 'active')
            ->where('working_date', '>=', $threshold)
            ->pluck('id')->flip()->all();

        $recentDiary = DB::table('cast_diaries')
            ->where('status', 'published')
            ->where('created_at', '>=', $threshold)
            ->distinct()->pluck('cast_id')->flip()->all();

        $highPriority = $recentWorking + $recentDiary;

        foreach (glob(public_path('sitemap-cast-*.xml')) ?: [] as $old) {
            unlink($old);
        }

        $fileIndex  = 1;
        $urls       = [];
        $totalCount = 0;

        Cast::where('status', 'active')
            ->whereRaw("CHAR_LENGTH(COALESCE(comment,'')) >= 100")
            ->select('id', 'name', 'img_file_name', 'updated_at')
            ->with(['images' => fn($q) => $q->orderBy('sort_order')->limit(5)])
            ->orderBy('id')
            ->chunk(1000, function ($casts) use ($base, $now, $highPriority, &$urls, &$fileIndex, &$totalCount) {
                foreach ($casts as $cast) {
                    $images = [];
                    if ($cast->img_file_name && !str_starts_with($cast->img_file_name, '/img/common/')) {
                        $images[] = ['loc' => $base . $cast->img_file_name . 'big.jpg', 'title' => $cast->name];
                    }
                    foreach ($cast->images as $img) {
                        $images[] = ['loc' => $base . $img->img_path];
                    }

                    $urls[] = [
                        'loc'      => "{$base}/cast/{$cast->id}/",
                        'lastmod'  => $cast->updated_at?->toAtomString() ?? $now,
                        'priority' => isset($highPriority[$cast->id]) ? '0.7' : '0.4',
                        'images'   => $images,
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
        file_put_contents(public_path("sitemap-cast-{$index}.xml"), $xml);
    }
}
