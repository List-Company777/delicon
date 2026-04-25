<?php

namespace App\Http\Controllers;

use App\Models\KeywordNormalization;
use App\Models\SearchKeyword;

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

        return view('top.index', compact('popularKeywords'));
    }
}
