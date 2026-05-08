<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Genre;
use App\Models\JobType;
use App\Models\KeywordNormalization;
use App\Models\Prefecture;
use App\Models\SearchKeyword;
use Illuminate\Http\Request;

class KeywordController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'new');

        $keywords = SearchKeyword::where('normalization_status', $status)
            ->orderByDesc('search_count')
            ->paginate(50);

        // 正規化済み・確定済みの場合はマッピング情報も取得
        $normalizationMap = collect();
        if ($status === 'mapped' || $status === 'confirmed') {
            $kwKeys = $keywords->map(fn($k) => $k->keyword . '_' . ($k->gender ?? ''));
            $normalizationMap = KeywordNormalization::with(['area', 'prefecture', 'jobType', 'genre'])
                ->where('is_active', true)
                ->whereIn('keyword', $keywords->pluck('keyword'))
                ->get()
                ->keyBy(fn($n) => $n->keyword . '_' . ($n->gender ?? ''));
        }

        $areas       = Area::orderBy('sort_order')->get();
        $jobTypes    = JobType::orderBy('sort_order')->get();
        $prefectures = Prefecture::orderBy("id")->get();
        $genres      = Genre::orderBy('sort_order')->get();
        $filterTypes = JobType::whereNotNull('keyword_filter')->orderBy('sort_order')->get();

        $counts = [
            'new'       => SearchKeyword::where('normalization_status', 'new')->count(),
            'mapped'    => SearchKeyword::where('normalization_status', 'mapped')->count(),
            'confirmed' => SearchKeyword::where('normalization_status', 'confirmed')->count(),
            'excluded'  => SearchKeyword::where('normalization_status', 'excluded')->count(),
        ];

        return view('admin.keywords.index', compact('keywords', 'normalizationMap', 'areas', 'jobTypes', 'prefectures', 'genres', 'filterTypes', 'status', 'counts'));
    }

    public function map(Request $request, int $id)
    {
        $kw = SearchKeyword::findOrFail($id);

        $request->validate([
            'area_id'        => ['nullable', 'exists:areas,id'],
            'prefecture_id'  => ['nullable', 'exists:prefectures,id'],
            'job_type_id'    => ['nullable', 'exists:job_types,id'],
            'genre_id'       => ['nullable', 'exists:genres,id'],
            'search_keyword' => ['nullable', 'string', 'max:200'],
            'filter_slug'    => ['nullable', 'string', 'max:100', 'exists:job_types,slug'],
        ]);

        $areaId     = $request->input('area_id') ?: null;
        $prefId     = $request->input('prefecture_id') ?: null;
        $jobTypeId  = $request->input('job_type_id') ?: null;
        $genreId    = $request->input('genre_id') ?: null;
        $filterSlug = $request->input('filter_slug') ?: null;
        // LP関連の指定がある場合はフリーワードをクリア
        $searchKw   = ($areaId || $prefId || $jobTypeId || $genreId || $filterSlug)
            ? null
            : (trim($request->input('search_keyword', '')) ?: null);

        KeywordNormalization::updateOrCreate(
            ['keyword' => $kw->keyword, 'gender' => $kw->gender],
            [
                'area_id'        => $areaId,
                'prefecture_id'  => $prefId,
                'job_type_id'    => $jobTypeId,
                'genre_id'       => $genreId,
                'search_keyword' => $searchKw,
                'filter_slug'    => $filterSlug,
                'is_active'      => true,
            ]
        );

        $newStatus = $request->boolean('directly_confirm') ? 'confirmed' : 'mapped';
        $kw->update(['normalization_status' => $newStatus]);

        $msg = $newStatus === 'confirmed'
            ? "「{$kw->keyword}」を確定しました"
            : "「{$kw->keyword}」を仮確定しました";

        return back()->with('success', $msg);
    }

    public function confirm(int $id)
    {
        $kw = SearchKeyword::findOrFail($id);
        $kw->update(['normalization_status' => 'confirmed']);

        return back()->with('success', "「{$kw->keyword}」を確定しました");
    }

    public function exclude(int $id)
    {
        $kw = SearchKeyword::findOrFail($id);
        $kw->update(['normalization_status' => 'excluded']);

        return back()->with('success', "「{$kw->keyword}」を除外しました");
    }

    public function generateCandidates(Request $request)
    {
        $genders = ['female', 'male', 'yoasobi'];
        $registered = 0;

        foreach ($genders as $gender) {
            $norms = KeywordNormalization::where('is_active', true)
                ->where('gender', $gender)
                ->whereNull('search_keyword')
                ->whereNull('filter_slug')
                ->whereExists(fn($q) => $q->from('search_keywords')
                    ->whereColumn('search_keywords.keyword', 'keyword_normalizations.keyword')
                    ->whereColumn('search_keywords.gender', 'keyword_normalizations.gender')
                    ->where('normalization_status', 'confirmed')
                )
                ->get();

            // エリアのみ（職種なし）の確定キーワード
            $areaOnlyNorms = $norms->filter(fn($n) =>
                ($n->area_id || $n->prefecture_id) && !$n->job_type_id && !$n->genre_id
            );

            // 職種のみ（エリアなし）の確定キーワード
            $jobOnlyNorms = $norms->filter(fn($n) =>
                !$n->area_id && !$n->prefecture_id && ($n->job_type_id || $n->genre_id)
            );

            foreach ($areaOnlyNorms as $areaNorm) {
                foreach ($jobOnlyNorms as $jobNorm) {
                    $candidate = $areaNorm->keyword . ' ' . $jobNorm->keyword;

                    $exists = SearchKeyword::where('keyword', $candidate)
                        ->where('gender', $gender)
                        ->exists();

                    if (!$exists) {
                        SearchKeyword::create([
                            'keyword'              => $candidate,
                            'gender'               => $gender,
                            'search_count'         => 0,
                            'normalization_status' => 'new',
                        ]);
                        $registered++;
                    }
                }
            }
        }

        return back()->with('success', "候補キーワードを {$registered} 件登録しました");
    }

    public function reset(int $id)
    {
        $kw = SearchKeyword::findOrFail($id);

        // マッピングを無効化
        KeywordNormalization::where('keyword', $kw->keyword)
            ->where('gender', $kw->gender)
            ->update(['is_active' => false]);

        $kw->update(['normalization_status' => 'new']);

        return back()->with('success', "「{$kw->keyword}」を未判定に戻しました");
    }
}
