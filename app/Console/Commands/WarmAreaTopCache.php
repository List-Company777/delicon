<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WarmAreaTopCache extends Command
{
    protected $signature   = 'cache:warm-area-top';
    protected $description = 'Warm area top page caches for high-traffic areas';

    public function handle(): int
    {
        // 30日間アクセス上位エリア
        $topSlugs = DB::table('search_page_views')
            ->selectRaw('area_slug, SUM(`count`) as total')
            ->where('date', '>=', now()->subDays(30))
            ->groupBy('area_slug')
            ->orderByDesc('total')
            ->limit(20)
            ->pluck('area_slug')
            ->all();

        // 主要都道府県は必ず含める
        $mustInclude = ['tokyo', 'osaka', 'kanagawa', 'aichi', 'saitama', 'chiba', 'hyogo', 'fukuoka'];
        $slugs = array_values(array_unique(array_merge($mustInclude, $topSlugs)));

        $baseUrl = rtrim(config('app.url'), '/');
        $count   = 0;
        foreach ($slugs as $slug) {
            try {
                Http::timeout(30)->get("{$baseUrl}/{$slug}/");
                $count++;
            } catch (\Throwable $e) {
                $this->warn("Failed: {$slug} — " . $e->getMessage());
            }
        }

        $this->info("Warmed {$count}/" . count($slugs) . ' area top pages.');
        return self::SUCCESS;
    }
}
