<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WarmDatalistCache extends Command
{
    protected $signature   = 'cache:warm-datalist';
    protected $description = 'トップページ datalist キャッシュを事前更新（shops:shuffle-display-sort の Cache::flush() 後に実行）';

    public function handle(): int
    {
        foreach (['female', 'male', 'yoasobi'] as $gender) {
            Cache::put("datalist_area_v2_{$gender}", $this->buildAreaDatalist($gender), 3600);
            Cache::put("datalist_keyword_v1_{$gender}", $this->buildKeywordDatalist($gender), 3600);
        }

        Cache::put('footer_top_areas', $this->buildFooterTopAreas(), 3600);

        $this->info('datalist/フッターリンクキャッシュを更新しました（female/male/yoasobi）');
        return 0;
    }

    private function buildFooterTopAreas(): array
    {
        $result = [];
        foreach (['female', 'male', 'yoasobi'] as $g) {
            $rows = DB::table('search_page_views as spv')
                ->join('areas as a', 'a.slug', '=', 'spv.area_slug')
                ->selectRaw('a.name, a.slug, SUM(spv.count) as total')
                ->where('spv.gender', $g)
                ->where('spv.area_slug', '!=', 'all')
                ->groupBy('a.name', 'a.slug')
                ->orderByDesc('total')
                ->limit(3)
                ->get();
            $result[$g] = $rows->count() >= 3
                ? $rows->map(fn($r) => ['name' => $r->name, 'slug' => $r->slug])->all()
                : [];
        }
        return $result;
    }

    private function buildAreaDatalist(string $gender): array
    {
        return DB::table('areas as a')
            ->leftJoinSub(
                DB::table('jobs as j')
                    ->join('shops as s', 's.id', '=', 'j.shop_id')
                    ->selectRaw('j.area_id, COUNT(*) as cnt')
                    ->where('j.status', 'active')
                    ->where('s.status', 'active')
                    ->groupBy('j.area_id'),
                'jc', 'jc.area_id', '=', 'a.id'
            )
            ->leftJoinSub(
                DB::table('search_page_views')
                    ->selectRaw('area_slug, SUM(`count`) as total')
                    ->where('gender', $gender)
                    ->groupBy('area_slug'),
                'sv', 'sv.area_slug', '=', 'a.slug'
            )
            ->selectRaw('a.name, COALESCE(jc.cnt, 0) as job_count, COALESCE(sv.total, 0) as view_total')
            ->havingRaw('job_count >= 5')
            ->orderByDesc('view_total')
            ->pluck('name')
            ->toArray();
    }

    private function buildKeywordDatalist(string $gender): array
    {
        if ($gender === 'yoasobi') {
            return DB::table('genres as g')
                ->join('shops as s', 's.genre_id', '=', 'g.id')
                ->join('shop_details as sd', 'sd.shop_id', '=', 's.id')
                ->where('sd.status', 'active')
                ->where('s.status', 'active')
                ->selectRaw('g.name, COUNT(*) as cnt')
                ->groupBy('g.id', 'g.name', 'g.sort_order')
                ->havingRaw('cnt >= 2')
                ->orderBy('g.sort_order')
                ->pluck('name')
                ->toArray();
        }
        return DB::table('job_types as jt')
            ->leftJoinSub(
                DB::table('jobs as j')
                    ->join('shops as s', 's.id', '=', 'j.shop_id')
                    ->selectRaw('j.job_type_id, COUNT(*) as cnt')
                    ->where('j.status', 'active')
                    ->where('s.status', 'active')
                    ->groupBy('j.job_type_id'),
                'jc', 'jc.job_type_id', '=', 'jt.id'
            )
            ->leftJoinSub(
                DB::table('search_page_views')
                    ->selectRaw('job_slug, SUM(`count`) as total')
                    ->where('gender', $gender)
                    ->where('job_slug', '!=', 'all')
                    ->groupBy('job_slug'),
                'sv', 'sv.job_slug', '=', 'jt.slug'
            )
            ->where('jt.target_gender', $gender)
            ->selectRaw('jt.name, COALESCE(jc.cnt, 0) as job_count, COALESCE(sv.total, 0) as view_total, jt.sort_order')
            ->havingRaw('job_count >= 5')
            ->orderByDesc('view_total')
            ->orderBy('jt.sort_order')
            ->pluck('name')
            ->toArray();
    }
}
