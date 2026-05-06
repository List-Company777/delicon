<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WarmFooterCache extends Command
{
    protected $signature   = 'cache:warm-footer';
    protected $description = 'Warm footer per-genre area cache (2+ shops, 30-day PV sort, top 10 per pref)';

    public function handle(): int
    {
        $shopTypes = DB::table('shop_types')->orderBy('id')->get(['id', 'name', 'slug']);

        $views = DB::table('search_page_views')
            ->selectRaw('area_slug, SUM(`count`) as total')
            ->where('date', '>=', now()->subDays(30))
            ->groupBy('area_slug')
            ->pluck('total', 'area_slug');

        // 都道府県スラッグマップ（id → slug）
        $prefSlugs = DB::table('prefectures')->pluck('slug', 'id');

        $genreData = [];
        foreach ($shopTypes as $type) {
            $rows = DB::table('shops')
                ->join('areas', 'areas.id', '=', 'shops.area_id')
                ->where('shops.status', 'active')
                ->where('shops.shop_type_id', $type->id)
                ->whereNotNull('shops.area_id')
                ->selectRaw('shops.area_id, areas.name, areas.slug, areas.prefecture_id, COUNT(shops.id) as cnt')
                ->groupBy('shops.area_id', 'areas.name', 'areas.slug', 'areas.prefecture_id')
                ->havingRaw('cnt >= 5')
                ->get();

            if ($rows->isEmpty()) continue;

            $areas = $rows->map(fn($r) => [
                'name'      => $r->name,
                'slug'      => $r->slug,
                'pref_slug' => $prefSlugs[$r->prefecture_id] ?? null,
                'views'     => (int) ($views[$r->slug] ?? 0),
            ])
            ->sortByDesc('views')
            ->values()
            ->all();

            $genreData[] = ['name' => $type->name, 'slug' => $type->slug, 'areas' => $areas];
        }

        Cache::put('delicon:footer_genre_prefs', $genreData, 86400);
        $total = array_sum(array_map(fn($g) => count($g['areas']), $genreData));
        $this->info('Footer genre cache warmed: ' . count($genreData) . ' genres, ' . $total . ' area entries.');
        return self::SUCCESS;
    }
}
