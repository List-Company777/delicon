<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TopController extends Controller
{
    public function index()
    {
        // 人気エリア：search_page_views から過去30日のエリア別PV上位6件をgender別に取得
        $popularAreasRaw = Cache::remember('top:popular_areas', 1800, fn() =>
            DB::table('search_page_views as v')
                ->selectRaw('v.gender, v.area_slug, SUM(v.count) as total, a.name as area_name')
                ->join('areas as a', 'a.slug', '=', 'v.area_slug')
                ->whereRaw('v.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')
                ->where('v.area_slug', '!=', 'all')
                ->whereIn('v.gender', ['female', 'male', 'yoasobi'])
                ->groupBy('v.gender', 'v.area_slug', 'a.name')
                ->orderByDesc('total')
                ->get()
                ->groupBy('gender')
                ->map(fn($rows) => $rows->take(6)->map(fn($r) => (array) $r)->values()->all())
                ->all()
        );
        $popularAreas = collect($popularAreasRaw)->map(fn($rows) => collect($rows)->map(fn($r) => (object) $r));

        // よく検索されているキーワード：search_page_views から過去30日のエリア+職種別PV集計
        $popularKeywordsRaw = Cache::remember('top:popular_keywords', 1800, function () {
            // エリア単独（job_slug='all'）: areas と JOIN して日本語名を取得
            $areaKws = DB::table('search_page_views as spv')
                ->join('areas as a', 'a.slug', '=', 'spv.area_slug')
                ->selectRaw("spv.gender, a.name as keyword, a.slug as area_slug, 'all' as job_slug, SUM(spv.count) as search_count")
                ->whereRaw('spv.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')
                ->where('spv.area_slug', '!=', 'all')
                ->where('spv.job_slug', 'all')
                ->whereIn('spv.gender', ['female', 'male', 'yoasobi'])
                ->groupBy('spv.gender', 'spv.area_slug', 'a.name', 'a.slug')
                ->get();

            // 職種単独（area_slug='all'）: job_types と JOIN して日本語名を取得
            $jobKws = DB::table('search_page_views as spv')
                ->join('job_types as jt', 'jt.slug', '=', 'spv.job_slug')
                ->selectRaw("spv.gender, jt.name as keyword, 'all' as area_slug, jt.slug as job_slug, SUM(spv.count) as search_count")
                ->whereRaw('spv.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')
                ->where('spv.job_slug', '!=', 'all')
                ->where('spv.area_slug', 'all')
                ->whereIn('spv.gender', ['female', 'male', 'yoasobi'])
                ->groupBy('spv.gender', 'spv.job_slug', 'jt.name', 'jt.slug')
                ->get();

            return $areaKws->concat($jobKws)
                ->sortByDesc('search_count')
                ->take(20)
                ->map(fn($kw) => [
                    'keyword'       => $kw->keyword,
                    'gender'        => $kw->gender,
                    'search_count'  => $kw->search_count,
                    'directory_url' => route('search.directory', [
                        'gender'    => $kw->gender,
                        'area_slug' => $kw->area_slug,
                        'job_slug'  => $kw->job_slug,
                    ]) . '/',
                ])
                ->values()
                ->all();
        });
        $popularKeywords = collect($popularKeywordsRaw)->map(fn($kw) => (object) $kw);

        return view('top.index', compact('popularKeywords', 'popularAreas'));
    }
}
