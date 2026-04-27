<?php

namespace App\Http\Controllers;

use App\Models\KeywordNormalization;
use App\Models\SearchKeyword;
use Illuminate\Support\Facades\DB;

class TopController extends Controller
{
    public function index()
    {
        $popularKeywords = SearchKeyword::where('normalization_status', '!=', 'excluded')
            ->orderByDesc('search_count')
            ->limit(20)
            ->get();

        // 正規化済みキーワードのディレクトリURLを一括で取得（N+1回避）
        $normalizations = KeywordNormalization::with(['area', 'jobType'])
            ->where('is_active', true)
            ->whereIn('keyword', $popularKeywords->pluck('keyword'))
            ->get()
            ->keyBy(fn($n) => $n->keyword . '_' . ($n->gender ?? ''));

        $popularKeywords = $popularKeywords->map(function ($kw) use ($normalizations) {
            $key  = $kw->keyword . '_' . ($kw->gender ?? '');
            $norm = $normalizations->get($key);
            if ($norm) {
                $kw->directory_url = route('search.directory', [
                    'gender'    => $kw->gender,
                    'area_slug' => $norm->area?->slug ?? 'all',
                    'job_slug'  => $norm->jobType?->slug ?? 'all',
                ]);
            }
            return $kw;
        });

        // 人気エリア：search_page_views から過去30日のエリア別PV上位6件をgender別に取得
        $popularAreas = DB::table('search_page_views as v')
            ->selectRaw('v.gender, v.area_slug, SUM(v.count) as total, a.name as area_name')
            ->join('areas as a', 'a.slug', '=', 'v.area_slug')
            ->whereRaw('v.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')
            ->where('v.area_slug', '!=', 'all')
            ->whereIn('v.gender', ['female', 'male', 'business'])
            ->groupBy('v.gender', 'v.area_slug', 'a.name')
            ->orderByDesc('total')
            ->get()
            ->groupBy('gender')
            ->map(fn($rows) => $rows->take(6));

        return view('top.index', compact('popularKeywords', 'popularAreas'));
    }
}
