<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Article;
use App\Models\Genre;
use App\Models\Job;
use App\Models\JobType;
use App\Models\ShopType;
use App\Models\Prefecture;
use App\Models\Shop;
use App\Models\ShopDetail;
use App\Models\SearchKeyword;
use App\Models\KeywordNormalization;
use Illuminate\Support\Facades\DB;
use App\Models\SearchPageView;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $gender   = $request->input('gender', 'female'); // yoasobi / male / female
        $area     = $request->input('area') ?? '';
        $keyword  = $request->input('keyword') ?? '';
        $wageType = $request->input('wage_type') ?? '';
        $wageMin  = (int) $request->input('wage_min', 0);
        $arubaito = $request->boolean('arubaito');

        // 空文字パラメータが含まれる場合は正規URLへ301リダイレクト（重複URL防止）
        if (collect($request->query())->contains(fn($v) => $v === '')) {
            $clean = array_filter($request->query(), fn($v) => $v !== '');
            return redirect()->away(url('/search/') . ($clean ? '?' . http_build_query($clean) : ''), 301);
        }

        // 条件なしの場合はディレクトリLPへリダイレクト
        if (!$area && !$keyword && !$wageType && !$wageMin && !$arubaito) {
            return redirect()->route('shop.list', [
                'area_slug' => 'all',
            ], 301);
        }

        // 正規化チェック：正規化済みのURLがあればリダイレクト（詳細条件がある場合はスキップ）
        if (($area || $keyword) && !$wageType && !$wageMin && !$arubaito) {
            $norm = KeywordNormalization::where('keyword', trim($area . ' ' . $keyword))
                ->where('gender', $gender)
                ->where('is_active', true)
                ->with(['area', 'prefecture', 'jobType', 'genre'])
                ->first();

            if ($norm) {
                // 都道府県のみ指定（職種なし）→ /{pref_slug}/shop-list/ へ
                if ($norm->prefecture_id && !$norm->area_id && !$norm->job_type_id && !$norm->genre_id && !$norm->filter_slug) {
                    return redirect()->route('shop.list', [
                        'area_slug' => $norm->prefecture->slug,
                    ], 301);
                }
                // フリーワード検索へリダイレクト（未経験歓迎など属性系キーワード）
                if ($norm->search_keyword && !$norm->area_id && !$norm->prefecture_id && !$norm->job_type_id && !$norm->genre_id) {
                    return redirect()->away(
                        url('/search/') . '?' . http_build_query(['gender' => $gender, 'keyword' => $norm->search_keyword]),
                        301
                    );
                }
                $areaSlug    = $norm->area?->slug ?? $norm->prefecture?->slug ?? 'all';
                $jobTypeSlug = $norm->jobType?->slug ?? $norm->genre?->slug ?? 'all';
                // girl_type_id が設定されている場合 → girl-list/type/ LP へ
                if ($norm->girl_type_id) {
                    $gt = DB::table('girl_types')->find($norm->girl_type_id);
                    if ($gt) {
                        return redirect()->route('girl.list.type', [
                            'area_slug'  => $areaSlug,
                            'type_slug'  => $gt->slug,
                        ], 301);
                    }
                }
                // filter_slug が設定されていればフィルター付きLPへ
                if ($norm->filter_slug) {
                    return redirect()->route('shop.list.filter', [
                        'area_slug'   => $areaSlug,
                        'filter_slug' => $norm->filter_slug,
                    ], 301);
                }
                return redirect()->route($jobTypeSlug === 'all' ? 'shop.list' : 'shop.list.filter', array_filter([
                    'area_slug'   => $areaSlug,
                    'filter_slug' => $jobTypeSlug !== 'all' ? $jobTypeSlug : null,
                ]), 301);
            }
        }

        // 検索ワードを記録
        if ($area || $keyword) {
            $this->recordKeyword($area . ($keyword ? ' ' . $keyword : ''), $gender);
        }

        $allYouCanDrink    = $request->boolean('all_you_can_drink');
        $hasKaraoke        = $request->boolean('has_karaoke');
        $hasPrivateRoom    = $request->boolean('has_private_room');
        $discountFirstSet  = $request->boolean('discount_first_set');

        // 結果取得
        $shopTypeIds = array_filter(array_map('intval', (array) request()->input('shop_type_ids', [])));
        $ageRange    = request()->input('age_range', '');
        $results = $this->getResults($gender, $area, $keyword, wageType: $wageType, wageMin: $wageMin, arubaito: $arubaito, allYouCanDrink: $allYouCanDrink, hasKaraoke: $hasKaraoke, hasPrivateRoom: $hasPrivateRoom, discountFirstSet: $discountFirstSet, shopTypeIds: $shopTypeIds, ageRange: $ageRange);

        return view('search.index', compact('gender', 'area', 'keyword', 'wageType', 'wageMin', 'arubaito', 'results', 'allYouCanDrink', 'hasKaraoke', 'hasPrivateRoom', 'discountFirstSet', 'shopTypeIds', 'ageRange'));
    }

    public function shopList(Request $request, string $area_slug)
    {
        return $this->renderDirectory($request, 'female', $area_slug, 'all');
    }

    public function shopListFilter(Request $request, string $area_slug, string $filter_slug)
    {
        return $this->renderDirectory($request, 'female', $area_slug, $filter_slug);
    }

    // 都道府県LP
    public function prefecture(Request $request, string $gender, string $pref_slug)
    {
        $prefModel = Prefecture::where('slug', $pref_slug)->firstOrFail();

        $arubaito         = $request->boolean('arubaito');
        $allYouCanDrink   = $request->boolean('all_you_can_drink');
        $hasKaraoke       = $request->boolean('has_karaoke');
        $hasPrivateRoom   = $request->boolean('has_private_room');
        $discountFirstSet = $request->boolean('discount_first_set');

        $shopTypeIds = array_filter(array_map('intval', (array) request()->input('shop_type_ids', [])));
        $ageRange    = request()->input('age_range', '');
        $results = $this->getResults($gender, '', '', prefSlug: $pref_slug, arubaito: $arubaito, allYouCanDrink: $allYouCanDrink, hasKaraoke: $hasKaraoke, hasPrivateRoom: $hasPrivateRoom, discountFirstSet: $discountFirstSet, shopTypeIds: $shopTypeIds, ageRange: $ageRange);

        $noindex = $results->total() < 5;
        $status  = $results->total() === 0 ? 404 : 200;

        SearchPageView::record($gender, $pref_slug, 'all');

        $areaName    = $prefModel->name;
        $jobTypeName = '';
        $area_slug   = $pref_slug;
        $job_slug    = 'all';
        $area        = '';
        $keyword     = '';
        $wageType    = '';
        $wageMin     = 0;
        $isPrefPage  = true;

        $lpStats          = $noindex ? null : $this->computeLpStats($gender, null, null, $pref_slug, arubaito: $arubaito, allYouCanDrink: $allYouCanDrink, hasKaraoke: $hasKaraoke, hasPrivateRoom: $hasPrivateRoom, discountFirstSet: $discountFirstSet);
        $lpRelated        = $this->computeRelatedLinks($gender, null, $pref_slug, 'all', $prefModel);
        $relatedArticles  = $this->computeRelatedArticles($gender);

        return response()->view('search.index', compact(
            'gender', 'area_slug', 'job_slug', 'results',
            'areaName', 'jobTypeName',
            'area', 'keyword', 'wageType', 'wageMin', 'arubaito',
            'allYouCanDrink', 'hasKaraoke', 'hasPrivateRoom', 'discountFirstSet', 'isPrefPage',
            'noindex', 'lpStats', 'lpRelated', 'relatedArticles',
            'shopTypeIds', 'ageRange'
        ), $status);
    }

    // 正規化ディレクトリURL（LP）
    private function renderDirectory(Request $request, string $gender, string $area_slug, string $job_slug)
    {
        // IDのみキャッシュしてモデルはPKで取得（Eloquentモデルの直列化によるデシリアライズエラーを防止）
        $areaId        = $area_slug !== 'all' ? Cache::remember("slug:area_id:{$area_slug}", 86400, fn() => Area::where('slug', $area_slug)->value('id')) : null;
        $areaModel     = $areaId ? Area::with('prefecture')->find($areaId) : null;
        $prefId        = (!$areaModel && $area_slug !== 'all') ? Cache::remember("slug:pref_id:{$area_slug}", 86400, fn() => Prefecture::where('slug', $area_slug)->value('id')) : null;
        $prefOnlyModel = $prefId ? Prefecture::find($prefId) : null;
        $jobTypeId     = $job_slug !== 'all' ? Cache::remember("slug:jobtype_id:{$job_slug}", 86400, fn() => JobType::where('slug', $job_slug)->value('id')) : null;
        $jobTypeModel  = $jobTypeId ? JobType::find($jobTypeId) : null;
        $genreId       = ($job_slug !== 'all' && !$jobTypeModel) ? Cache::remember("slug:genre_id:{$job_slug}", 86400, fn() => Genre::where('slug', $job_slug)->value('id')) : null;
        $genreModel    = $genreId ? Genre::find($genreId) : null;
        $shopTypeIdFromSlug = ($job_slug !== 'all' && !$jobTypeModel && !$genreModel)
            ? Cache::remember("slug:shoptype_id:{$job_slug}", 86400, fn() => ShopType::where('slug', $job_slug)->value('id'))
            : null;
        $shopTypeModelFromSlug = $shopTypeIdFromSlug ? ShopType::find($shopTypeIdFromSlug) : null;

        $area    = ($areaModel && $area_slug !== 'all') ? $area_slug : '';
        $keyword = ($job_slug === 'all' || $shopTypeModelFromSlug) ? '' : $job_slug;

        $areaName    = $areaModel?->name ?? $prefOnlyModel?->name ?? '';
        $jobTypeName = $jobTypeModel?->name ?? $genreModel?->name ?? $shopTypeModelFromSlug?->name ?? '';
        $prefModel   = $areaModel?->prefecture;
        $isPrefPage  = (bool) $prefOnlyModel;

        $wageType         = '';
        $wageMin          = 0;
        $arubaito         = $request->boolean('arubaito');
        $allYouCanDrink   = $request->boolean('all_you_can_drink');
        $hasKaraoke       = $request->boolean('has_karaoke');
        $hasPrivateRoom   = $request->boolean('has_private_room');
        $discountFirstSet = $request->boolean('discount_first_set');

        $shopTypeIds = $shopTypeModelFromSlug
            ? [$shopTypeIdFromSlug]
            : array_filter(array_map('intval', (array) request()->input('shop_type_ids', [])));
        $ageRange    = request()->input('age_range', '');
        $results = $prefOnlyModel
            ? $this->getResults($gender, '', '', prefSlug: $area_slug, arubaito: $arubaito, allYouCanDrink: $allYouCanDrink, hasKaraoke: $hasKaraoke, hasPrivateRoom: $hasPrivateRoom, discountFirstSet: $discountFirstSet, shopTypeIds: $shopTypeIds, ageRange: $ageRange)
            : $this->getResults($gender, $area, $keyword, useSlug: true, arubaito: $arubaito, allYouCanDrink: $allYouCanDrink, hasKaraoke: $hasKaraoke, hasPrivateRoom: $hasPrivateRoom, discountFirstSet: $discountFirstSet, shopTypeIds: $shopTypeIds, ageRange: $ageRange);

        $noindex = $results->total() < 5;
        $status  = $results->total() === 0 ? 404 : 200;

        SearchPageView::record($gender, $area_slug, $job_slug);

        $shopTypesRaw = Cache::remember('delicon:shop_types_list', 86400, fn() =>
            ShopType::orderBy('id')->get(['id', 'name', 'slug'])
                ->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'slug' => $t->slug])
                ->all()
        );
        $shopTypes = collect($shopTypesRaw)->map(fn($t) => (object) $t);

        // 小エリア絞り込み（都道府県ページのみ）
        $subAreas = collect();
        if ($isPrefPage && $prefOnlyModel) {
            $subAreasRaw = Cache::remember("pref:sub_areas_shops:{$area_slug}", 1800, function () use ($prefOnlyModel) {
                $areaIds = DB::table('areas')->where('prefecture_id', $prefOnlyModel->id)->pluck('id');
                $counts  = DB::table('shops')
                    ->where('status', 'active')->whereIn('area_id', $areaIds)
                    ->groupBy('area_id')->selectRaw('area_id, COUNT(*) as cnt')
                    ->pluck('cnt', 'area_id');
                return DB::table('areas')
                    ->where('prefecture_id', $prefOnlyModel->id)->whereNull('parent_id')
                    ->get(['id', 'name', 'slug'])
                    ->filter(fn($a) => ($counts[$a->id] ?? 0) > 0)
                    ->sortByDesc(fn($a) => $counts[$a->id] ?? 0)->values()
                    ->map(fn($a) => ['name' => $a->name, 'slug' => $a->slug, 'cnt' => $counts[$a->id] ?? 0])
                    ->all();
            });
            $subAreas = collect($subAreasRaw)->map(fn($a) => (object) $a);
        }

        return response()->view('search.shop_list', compact(
            'gender', 'area_slug', 'job_slug', 'results',
            'areaName', 'jobTypeName', 'prefModel', 'isPrefPage',
            'area', 'keyword', 'wageType', 'wageMin', 'arubaito',
            'allYouCanDrink', 'hasKaraoke', 'hasPrivateRoom', 'discountFirstSet',
            'noindex', 'shopTypes', 'shopTypeIds', 'ageRange', 'subAreas'
        ), $status);
    }

    // フィルター付きディレクトリURL（LP + クイックタグ絞り込み）
    // 例: /male/shinjuku/all/hibarai/
    private function renderFilteredDirectory(Request $request, string $gender, string $area_slug, string $job_slug, string $filter_slug)
    {
        $filterType = JobType::where('slug', $filter_slug)->whereNotNull('keyword_filter')->first();
        if (!$filterType) {
            abort(404);
        }

        $areaId       = $area_slug !== 'all' ? Cache::remember("slug:area_id:{$area_slug}", 86400, fn() => Area::where('slug', $area_slug)->value('id')) : null;
        $areaModel    = $areaId ? Area::with('prefecture')->find($areaId) : null;
        $jobTypeId    = $job_slug !== 'all' ? Cache::remember("slug:jobtype_id:{$job_slug}", 86400, fn() => JobType::where('slug', $job_slug)->value('id')) : null;
        $jobTypeModel = $jobTypeId ? JobType::find($jobTypeId) : null;
        $genreId      = ($job_slug !== 'all' && !$jobTypeModel) ? Cache::remember("slug:genre_id:{$job_slug}", 86400, fn() => Genre::where('slug', $job_slug)->value('id')) : null;
        $genreModel   = $genreId ? Genre::find($genreId) : null;

        $area    = $area_slug === 'all' ? '' : $area_slug;
        $keyword = $job_slug  === 'all' ? '' : $job_slug;

        $areaName    = $areaModel?->name    ?? '';
        $jobTypeName = $jobTypeModel?->name ?? $genreModel?->name ?? '';
        $filterName  = $filterType->name;
        $prefModel   = $areaModel?->prefecture;

        $wageType         = '';
        $wageMin          = 0;
        $arubaito         = $request->boolean('arubaito');
        $allYouCanDrink   = $request->boolean('all_you_can_drink');
        $hasKaraoke       = $request->boolean('has_karaoke');
        $hasPrivateRoom   = $request->boolean('has_private_room');
        $discountFirstSet = $request->boolean('discount_first_set');

        // shop: プレフィックスはShopDetail列への直接フィルタ（例: shop:has_karaoke）
        $rawFilter      = $filterType->keyword_filter;
        $filterKeyword  = str_starts_with($rawFilter, 'shop:') ? '' : $rawFilter;
        $shopBoolFilter = str_starts_with($rawFilter, 'shop:') ? substr($rawFilter, 5) : '';

        $shopTypeIds = array_filter(array_map('intval', (array) request()->input('shop_type_ids', [])));
        $ageRange    = request()->input('age_range', '');
        $results = $this->getResults($gender, $area, $keyword, useSlug: true, filterKeyword: $filterKeyword, shopBoolFilter: $shopBoolFilter, arubaito: $arubaito, allYouCanDrink: $allYouCanDrink, hasKaraoke: $hasKaraoke, hasPrivateRoom: $hasPrivateRoom, discountFirstSet: $discountFirstSet, shopTypeIds: $shopTypeIds, ageRange: $ageRange);

        $noindex = $results->total() < 5;
        $status  = $results->total() === 0 ? 404 : 200;

        SearchPageView::record($gender, $area_slug, $job_slug);

        $lpStats         = $noindex ? null : $this->computeLpStats($gender, $areaModel, $jobTypeModel ?? $genreModel, filterKeyword: $filterKeyword, shopBoolFilter: $shopBoolFilter, arubaito: $arubaito, allYouCanDrink: $allYouCanDrink, hasKaraoke: $hasKaraoke, hasPrivateRoom: $hasPrivateRoom, discountFirstSet: $discountFirstSet);
        $lpRelated       = $this->computeRelatedLinks($gender, $areaModel, $area_slug, $job_slug);
        $relatedArticles = $this->computeRelatedArticles($gender);

        return response()->view('search.index', compact(
            'gender', 'area_slug', 'job_slug', 'filter_slug', 'results',
            'areaName', 'jobTypeName', 'filterName', 'prefModel',
            'area', 'keyword', 'wageType', 'wageMin', 'arubaito',
            'allYouCanDrink', 'hasKaraoke', 'hasPrivateRoom', 'discountFirstSet',
            'noindex', 'lpStats', 'lpRelated', 'relatedArticles',
            'shopTypeIds', 'ageRange'
        ), $status);
    }

    private function getResults(string $gender, string $area, string $keyword, bool $useSlug = false, string $filterKeyword = '', string $shopBoolFilter = '', string $wageType = '', int $wageMin = 0, bool $arubaito = false, bool $allYouCanDrink = false, bool $hasKaraoke = false, bool $hasPrivateRoom = false, bool $discountFirstSet = false, string $prefSlug = '', array $shopTypeIds = [], string $ageRange = '')
    {
        $page    = (int) request()->input('page', 1);
        $perPage = $gender === 'yoasobi' ? 20 : 20;

        // キャッシュにはIDのみ保存（Eloquentオブジェクトはシリアライズ不可）
        $idsCacheKey = 'search_ids:' . md5(implode('|', [
            $gender, $area, $keyword, $useSlug, $filterKeyword, $shopBoolFilter,
            $wageType, $wageMin, $arubaito, $allYouCanDrink, $hasKaraoke,
            $hasPrivateRoom, $discountFirstSet, $prefSlug, implode(',', $shopTypeIds), $ageRange,
        ]));

        $allIds = Cache::remember($idsCacheKey, 1800, function () use (
            $gender, $area, $keyword, $useSlug, $filterKeyword, $shopBoolFilter,
            $wageType, $wageMin, $arubaito, $allYouCanDrink, $hasKaraoke,
            $hasPrivateRoom, $discountFirstSet, $prefSlug, $shopTypeIds, $ageRange
        ) {
        // 「新宿駅」→「新宿」のように末尾の「駅」を除いた語も駅名検索に使う
        $stationArea = preg_replace('/駅$/u', '', $area);

        // LP（useSlug）かつ keyword が指定されているとき、keyword_filter 型の job_type かどうかを確認
        $keywordFilterValue = null;
        if ($useSlug && $keyword) {
            $kfType = JobType::where(fn($q) =>
                $q->where('slug', $keyword)->orWhere('group_slug', $keyword)
            )->whereNotNull('keyword_filter')->value('keyword_filter');
            $keywordFilterValue = $kfType ?: null;
        }

        if ($gender === 'yoasobi') {
            $query = ShopDetail::with(['shop.area', 'shop.genre'])
                ->where('status', 'active')
                ->when($prefSlug, fn($q) => $q->whereHas('shop', fn($s) =>
                    $s->whereHas('area.prefecture', fn($p) => $p->where('slug', $prefSlug))
                ))
                ->when($area, fn($q) => $useSlug
                    ? $q->whereHas('shop.area', fn($a) =>
                        $a->where('slug', $area)
                          ->orWhereHas('parent', fn($p) => $p->where('slug', $area))
                          ->orWhereHas('prefecture', fn($p) => $p->where('slug', $area))
                      )
                    : $q->where(fn($q2) =>
                        $q2->whereHas('shop.area', fn($a) => $a->where('name', 'like', "%{$area}%")
                                ->orWhere('slug', 'like', "%{$area}%"))
                           ->orWhereHas('shop', fn($s) =>
                                $s->where('nearest_line', 'like', "%{$area}%")
                                  ->orWhere('nearest_station_name', 'like', "%{$stationArea}%"))
                           ->orWhereHas('shop', fn($s) =>
                                $s->whereHas('area.prefecture', fn($p) => $p->where('name', 'like', "%{$area}%")))
                    )
                )
                ->when($keyword, fn($q) => $useSlug
                    ? $q->whereHas('shop', fn($s) =>
                        $s->whereHas('genre', fn($g) => $g->where('slug', $keyword))
                      )
                    : $q->whereHas('shop', fn($s) =>
                        $s->where('name', 'like', "%{$keyword}%")
                          ->orWhereHas('genre', fn($g) => $g->where('name', 'like', "%{$keyword}%"))
                      )
                )
                ->when($filterKeyword,   fn($q) => $q->whereHas('shop', fn($s) => $s->where('name', 'like', "%{$filterKeyword}%")))
                ->when($shopBoolFilter, fn($q) => $q->where($shopBoolFilter, true))
                ->when($allYouCanDrink, fn($q) => $q->where('all_you_can_drink', true))
                ->when($hasKaraoke,     fn($q) => $q->where('has_karaoke', true))
                ->when($hasPrivateRoom,  fn($q) => $q->where('has_private_room', true))
                ->when($discountFirstSet, fn($q) => $q->where('discount_first_set', true))
                ->when($shopTypeIds, fn($q) => $q->whereHas('shop', fn($s) => $s->whereIn('shop_type_id', $shopTypeIds)))
                ->when($ageRange, function ($q) use ($ageRange) {
                    [$min, $maxRaw] = $this->parseAgeRange($ageRange);
                    $maxVal = $maxRaw >= 120 ? 200 : $maxRaw;
                    $shopIds = \Illuminate\Support\Facades\DB::table('casts')
                        ->selectRaw('shop_id')
                        ->where('status', 'active')
                        ->where('age', '>', 0)
                        ->groupBy('shop_id')
                        ->havingRaw("SUM(CASE WHEN age BETWEEN ? AND ? THEN 1 ELSE 0 END) >= GREATEST(
                    SUM(CASE WHEN age BETWEEN 18 AND 19 THEN 1 ELSE 0 END),
                    SUM(CASE WHEN age BETWEEN 20 AND 24 THEN 1 ELSE 0 END),
                    SUM(CASE WHEN age BETWEEN 25 AND 34 THEN 1 ELSE 0 END),
                    SUM(CASE WHEN age BETWEEN 35 AND 44 THEN 1 ELSE 0 END),
                    SUM(CASE WHEN age >= 45 THEN 1 ELSE 0 END)
                ) AND SUM(CASE WHEN age BETWEEN ? AND ? THEN 1 ELSE 0 END) > 0", [$min, $maxVal, $min, $maxVal])
                        ->pluck('shop_id');
                    return $q->whereHas('shop', fn($s) => $s->whereIn('id', $shopIds));
                })
                ->orderByDesc(fn($q) =>
                    $q->select('rank_score')->from('shops')->whereColumn('shops.id', 'shop_details.shop_id')
                )
                ->orderBy(fn($q) =>
                    $q->select('display_sort')->from('shops')->whereColumn('shops.id', 'shop_details.shop_id')
                )
                ->pluck('shop_details.id')->all();
        } else {
            // デリヘルリストは店舗案内サイト（求人なし）→ Shop を直接検索
            $query = Shop::where('status', 'active')->where('plan', '<=', 4)
                ->when($prefSlug, fn($q) => $q->whereHas('area.prefecture', fn($p) => $p->where('slug', $prefSlug)))
                ->when($area, fn($q) => $useSlug
                    ? $q->whereHas('area', fn($a) =>
                        $a->where('slug', $area)
                          ->orWhereHas('parent', fn($p) => $p->where('slug', $area))
                          ->orWhereHas('prefecture', fn($p) => $p->where('slug', $area)))
                    : $q->where(fn($q2) =>
                        $q2->whereHas('area', fn($a) => $a->where('name', 'like', "%{$area}%")->orWhere('slug', 'like', "%{$area}%"))
                           ->orWhere('nearest_line', 'like', "%{$area}%")
                           ->orWhere('nearest_station_name', 'like', "%{$stationArea}%")
                           ->orWhereHas('area', fn($a) => $a->whereHas('prefecture', fn($p) => $p->where('name', 'like', "%{$area}%")))
                    )
                )
                ->when($keyword, fn($q) => $useSlug
                    ? $q->whereHas('genre', fn($g) => $g->where('slug', $keyword))
                    : $q->where(fn($q2) =>
                        $q2->where('name', 'like', "%{$keyword}%")
                           ->orWhereHas('genre', fn($g) => $g->where('name', 'like', "%{$keyword}%")))
                )
                ->when($filterKeyword, fn($q) => $q->where('name', 'like', "%{$filterKeyword}%"))
                ->when($hasKaraoke,      fn($q) => $q->whereHas('detail', fn($s) => $s->where('has_karaoke', true)))
                ->when($allYouCanDrink, fn($q) => $q->whereHas('detail', fn($s) => $s->where('all_you_can_drink', true)))
                ->when($hasPrivateRoom,  fn($q) => $q->whereHas('detail', fn($s) => $s->where('has_private_room', true)))
                ->when($discountFirstSet, fn($q) => $q->whereHas('detail', fn($s) => $s->where('discount_first_set', true)))
                ->when($shopTypeIds, fn($q) => $q->whereIn('shop_type_id', $shopTypeIds))
                ->when($ageRange, function ($q) use ($ageRange) {
                    [$min, $maxRaw] = $this->parseAgeRange($ageRange);
                    $maxVal = $maxRaw >= 120 ? 200 : $maxRaw;
                    return $q->whereIn('id', \Illuminate\Support\Facades\DB::table('casts')
                        ->selectRaw('shop_id')
                        ->where('status', 'active')
                        ->where('age', '>', 0)
                        ->groupBy('shop_id')
                        ->havingRaw("SUM(CASE WHEN age BETWEEN ? AND ? THEN 1 ELSE 0 END) >= GREATEST(
                        SUM(CASE WHEN age BETWEEN 18 AND 19 THEN 1 ELSE 0 END),
                        SUM(CASE WHEN age BETWEEN 20 AND 24 THEN 1 ELSE 0 END),
                        SUM(CASE WHEN age BETWEEN 25 AND 34 THEN 1 ELSE 0 END),
                        SUM(CASE WHEN age BETWEEN 35 AND 44 THEN 1 ELSE 0 END),
                        SUM(CASE WHEN age >= 45 THEN 1 ELSE 0 END)
                    ) AND SUM(CASE WHEN age BETWEEN ? AND ? THEN 1 ELSE 0 END) > 0", [$min, $maxVal, $min, $maxVal])
                        ->pluck('shop_id')
                    );
                })
                ->orderByRaw("plan ASC, COALESCE(CASE plan WHEN 1 THEN plan1_since WHEN 2 THEN plan2_since WHEN 3 THEN plan3_since WHEN 4 THEN plan4_since ELSE NULL END, '9999-12-31') ASC, id ASC")
                ->pluck('id')->all();
        }

        return $query;
        }); // Cache::remember

        // ページ分のIDを切り出してモデルを取得（キャッシュ外）
        $total   = count($allIds);
        $pageIds = array_slice($allIds, ($page - 1) * $perPage, $perPage);

        if (empty($pageIds)) {
            $items = collect();
        } elseif ($gender === 'yoasobi') {
            $idOrder = implode(',', $pageIds);
            $items = ShopDetail::with(['shop.area', 'shop.genre'])
                ->whereIn('id', $pageIds)
                ->orderByRaw("FIELD(id, {$idOrder})")
                ->get();
        } else {
            $idOrder = implode(',', $pageIds);
            $items = Shop::with(['area', 'genre', 'prefecture', 'detail', 'shopType',
                'castMembers' => fn($c) => $c->where('status', 'active')->orderByRaw('working_date = CURDATE() DESC')->orderBy('sort_order')->take(5),
            ])
            ->withCount(['castMembers as active_cast_count' => fn($c) => $c->where('status', 'active')])
            ->whereIn('id', $pageIds)
            ->orderByRaw("FIELD(id, {$idOrder})")
            ->get();
        }

        return (new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page
        ))->withPath(rtrim(request()->url(), '/') . '/')->withQueryString();
    }

    /** LP統計バー用の集計（noindexページでは呼ばない） */
    private function computeLpStats(
        string $gender,
        ?Area  $areaModel,
        mixed  $typeModel,
        string $prefSlug       = '',
        string $filterKeyword  = '',
        string $shopBoolFilter = '',
        bool   $arubaito          = false,
        bool   $allYouCanDrink    = false,
        bool   $hasKaraoke        = false,
        bool   $hasPrivateRoom    = false,
        bool   $discountFirstSet  = false,
    ): array {
        $cacheKey = 'lp_stats:' . md5(implode('|', [
            $gender,
            $areaModel?->id ?? '',
            $typeModel?->slug ?? '',
            $prefSlug,
            $filterKeyword,
            $shopBoolFilter,
            $arubaito ? '1' : '',
            $allYouCanDrink ? '1' : '',
            $hasKaraoke ? '1' : '',
            $hasPrivateRoom ? '1' : '',
            $discountFirstSet ? '1' : '',
        ]));

        return Cache::remember($cacheKey, 1800, function () use (
            $gender, $areaModel, $typeModel, $prefSlug,
            $filterKeyword, $shopBoolFilter,
            $arubaito, $allYouCanDrink, $hasKaraoke, $hasPrivateRoom, $discountFirstSet
        ) {
        if ($gender === 'yoasobi') {
            $query = ShopDetail::where('status', 'active')
                ->when($areaModel, fn($q) => $q->whereHas('shop', fn($s) => $s->where('area_id', $areaModel->id)))
                ->when($prefSlug,  fn($q) => $q->whereHas('shop.area.prefecture', fn($p) => $p->where('slug', $prefSlug)))
                ->when($shopBoolFilter,   fn($q) => $q->where($shopBoolFilter, true))
                ->when($allYouCanDrink,   fn($q) => $q->where('all_you_can_drink', true))
                ->when($hasKaraoke,       fn($q) => $q->where('has_karaoke', true))
                ->when($hasPrivateRoom,   fn($q) => $q->where('has_private_room', true))
                ->when($discountFirstSet, fn($q) => $q->where('discount_first_set', true));

            $stats = [
                'all_you_can_drink'  => (clone $query)->where('all_you_can_drink', true)->count(),
                'has_karaoke'        => (clone $query)->where('has_karaoke', true)->count(),
                'has_private_room'   => (clone $query)->where('has_private_room', true)->count(),
                'discount_first_set' => (clone $query)->where('discount_first_set', true)->count(),
            ];

            // フィルター適用時は絞り込み後の総店舗数も返す
            if ($shopBoolFilter || $allYouCanDrink || $hasKaraoke || $hasPrivateRoom || $discountFirstSet) {
                $stats['total_shops'] = $query->count();
            }

            return $stats;
        }

        $searchGroups = $gender === 'male' ? ['male', 'both'] : ['female', 'both'];

        $query = Job::where('status', 'active')
            ->whereIn('search_group', $searchGroups)
            ->when($areaModel, fn($q) => $q->where(fn($q2) =>
                $q2->where('area_id', $areaModel->id)
                   ->orWhereHas('shop', fn($s) => $s->where('area_id', $areaModel->id))
                   ->orWhereHas('area', fn($a) => $a->where('parent_id', $areaModel->id))
                   ->orWhereHas('shop.area', fn($a) => $a->where('parent_id', $areaModel->id))
            ))
            ->when($prefSlug,      fn($q) => $q->whereHas('area.prefecture', fn($p) => $p->where('slug', $prefSlug)))
            ->when($typeModel,     fn($q) => $q->whereHas('jobType', fn($j) =>
                $j->where('slug', $typeModel->slug)->orWhere('group_slug', $typeModel->slug ?? '')
            ))
            ->when($filterKeyword, fn($q) => $this->whereTitleMatch($q, $filterKeyword))
            ->when($arubaito,      fn($q) => $q->where('wage_type', 'hourly')->where('employment_type', 'PART_TIME'))
            ->when($shopBoolFilter,   fn($q) => $q->whereHas('shop.detail', fn($s) => $s->where($shopBoolFilter, true)))
            ->when($allYouCanDrink,   fn($q) => $q->whereHas('shop.detail', fn($s) => $s->where('all_you_can_drink', true)))
            ->when($hasKaraoke,       fn($q) => $q->whereHas('shop.detail', fn($s) => $s->where('has_karaoke', true)))
            ->when($hasPrivateRoom,   fn($q) => $q->whereHas('shop.detail', fn($s) => $s->where('has_private_room', true)))
            ->when($discountFirstSet, fn($q) => $q->whereHas('shop.detail', fn($s) => $s->where('discount_first_set', true)));

        $agg = (clone $query)
            ->selectRaw('
                SUM(CASE WHEN wage_type = "hourly"  AND hourly_wage_min > 0 THEN 1 ELSE 0 END) as hourly_count,
                ROUND(AVG(CASE WHEN wage_type = "hourly"  AND hourly_wage_min > 0 THEN hourly_wage_min END)) as avg_hourly,
                SUM(CASE WHEN wage_type = "monthly" AND hourly_wage_min > 0 THEN 1 ELSE 0 END) as monthly_count,
                ROUND(AVG(CASE WHEN wage_type = "monthly" AND hourly_wage_min > 0 THEN hourly_wage_min END)) as avg_monthly,
                SUM(CASE WHEN wage_type = "daily" THEN 1 ELSE 0 END) as daily_count,
                SUM(CASE WHEN wage_type = "hourly" AND employment_type = "PART_TIME" THEN 1 ELSE 0 END) as part_time_count
            ')
            ->first();

        $hourlyCount  = (int) ($agg?->hourly_count  ?? 0);
        $monthlyCount = (int) ($agg?->monthly_count ?? 0);

        $stats = [
            'total_jobs'      => $query->count(),
            'daily_count'     => (int) ($agg?->daily_count     ?? 0),
            'part_time_count' => (int) ($agg?->part_time_count ?? 0),
        ];

        // 時給：female/male 共通、5件以上あれば表示
        if ($hourlyCount >= 5) {
            $stats['avg_hourly']   = (int) $agg->avg_hourly;
            $stats['hourly_count'] = $hourlyCount;
        }

        // 月給：male のみ、5件以上あれば表示
        if ($gender === 'male' && $monthlyCount >= 5) {
            $stats['avg_monthly']   = (int) $agg->avg_monthly;
            $stats['monthly_count'] = $monthlyCount;
        }

        return $stats;
        }); // Cache::remember lp_stats
    }

    /** LP関連リンク用データ（noindexページでも表示） */
    private function computeRelatedLinks(string $gender, ?Area $areaModel, string $areaSlug, string $jobSlug, ?Prefecture $prefModel = null): array
    {
        $cacheKey = 'lp_related:' . md5(implode('|', [
            $gender,
            $areaModel?->id ?? '',
            $areaSlug,
            $jobSlug,
            $prefModel?->id ?? '',
        ]));

        // Eloquent Collectionをそのままigbinaryシリアライズすると
        // OPcacheリロード後にデシリアライズ失敗するため、純粋な配列で保存する
        $data = Cache::remember($cacheKey, 1800, function () use ($gender, $areaModel, $areaSlug, $jobSlug, $prefModel) {
        $searchGroups = match($gender) {
            'male'     => ['male', 'both'],
            'yoasobi' => ['yoasobi'],
            default    => ['female', 'both'],
        };

        // 関連エリア：同都道府県の他エリア（求人/店舗あり）
        $relatedAreas = collect();
        if ($areaModel?->prefecture_id) {
            $relatedAreas = Area::where('prefecture_id', $areaModel->prefecture_id)
                ->where('id', '!=', $areaModel->id)
                ->when($gender === 'yoasobi',
                    fn($q) => $q->whereHas('shops', fn($s) => $s->where('status', 'active')),
                    fn($q) => $q->whereHas('jobs', fn($j) => $j->where('status', 'active')->whereIn('search_group', $searchGroups))
                )
                ->orderBy('sort_order')
                ->limit(10)
                ->get(['id', 'name', 'slug']);
        } elseif ($prefModel) {
            // 都道府県LPの場合：同都道府県のエリア一覧
            $relatedAreas = Area::where('prefecture_id', $prefModel->id)
                ->when($gender === 'yoasobi',
                    fn($q) => $q->whereHas('shops', fn($s) => $s->where('status', 'active')),
                    fn($q) => $q->whereHas('jobs', fn($j) => $j->where('status', 'active')->whereIn('search_group', $searchGroups))
                )
                ->orderBy('sort_order')
                ->limit(10)
                ->get(['id', 'name', 'slug']);
        }

        // 関連職種/業種
        $relatedTypes = collect();
        if ($gender === 'yoasobi') {
            $relatedTypes = \App\Models\Genre::whereHas('shops', fn($q) =>
                    $q->where('status', 'active')
                      ->when($areaModel, fn($s) => $s->where('area_id', $areaModel->id))
                )
                ->when($jobSlug !== 'all', fn($q) => $q->where('slug', '!=', $jobSlug))
                ->orderBy('sort_order')
                ->limit(8)
                ->get(['id', 'name', 'slug']);
        } else {
            $relatedTypes = JobType::where(fn($q) =>
                    $q->where('target_gender', $gender)->orWhere('target_gender', 'both')
                )
                ->whereNull('keyword_filter') // フィルター用スラッグは除外
                ->whereHas('jobs', fn($q) =>
                    $q->where('status', 'active')
                      ->whereIn('search_group', $searchGroups)
                      ->when($areaModel, fn($j) =>
                          $j->where(fn($j2) =>
                              $j2->where('area_id', $areaModel->id)
                                 ->orWhereHas('shop', fn($s) => $s->where('area_id', $areaModel->id))
                                 ->orWhereHas('area', fn($a) => $a->where('parent_id', $areaModel->id))
                                 ->orWhereHas('shop.area', fn($a) => $a->where('parent_id', $areaModel->id))
                          )
                      )
                )
                ->when($jobSlug !== 'all', fn($q) => $q->where('slug', '!=', $jobSlug))
                ->orderBy('sort_order')
                ->limit(8)
                ->get(['id', 'name', 'slug']);
        }

        return [
            'areas' => $relatedAreas->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'slug' => $m->slug])->all(),
            'types' => $relatedTypes->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'slug' => $m->slug])->all(),
        ];
        }); // Cache::remember lp_related

        // 取り出し後にCollection<stdClass>に変換（ビューが->slug / ->isNotEmpty()を使うため）
        return [
            'areas' => collect($data['areas'])->map(fn($a) => (object) $a),
            'types' => collect($data['types'])->map(fn($a) => (object) $a),
        ];
    }

    private function computeRelatedArticles(string $gender): \Illuminate\Database\Eloquent\Collection
    {
        $articleGender = $gender;

        return Article::published()
            ->where('gender', $articleGender)
            ->latest('published_at')
            ->limit(3)
            ->get(['id', 'slug', 'title', 'lead', 'hero_image', 'published_at']);
    }

    /**
     * jobs.title に対して MATCH AGAINST（ngram）を適用する。
     * ngram_token_size=2 のため2文字未満はLIKEにフォールバック。
     */
    private function parseAgeRange(string $ageRange): array
    {
        if (str_ends_with($ageRange, '+')) {
            return [(int) $ageRange, 120];
        }
        if (str_contains($ageRange, '-')) {
            [$min, $max] = explode('-', $ageRange, 2);
            return [(int) $min, (int) $max];
        }
        return [0, 120];
    }

    private function whereTitleMatch(\Illuminate\Database\Eloquent\Builder $query, string $keyword): \Illuminate\Database\Eloquent\Builder
    {
        if (mb_strlen($keyword) >= 2) {
            return $query->whereRaw('MATCH(title) AGAINST(? IN NATURAL LANGUAGE MODE)', [$keyword]);
        }
        return $query->where('title', 'like', "%{$keyword}%");
    }

    private function recordKeyword(string $keyword, string $gender): void
    {
        $keyword = mb_strtolower(trim($keyword));
        if (empty($keyword)) return;

        SearchKeyword::firstOrCreate(
            ['keyword' => $keyword, 'gender' => $gender],
            ['search_count' => 0]
        )->increment('search_count');
    }
}
