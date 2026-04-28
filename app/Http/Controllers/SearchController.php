<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Article;
use App\Models\Genre;
use App\Models\Job;
use App\Models\JobType;
use App\Models\Prefecture;
use App\Models\ShopDetail;
use App\Models\SearchKeyword;
use App\Models\KeywordNormalization;
use App\Models\SearchPageView;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $gender   = $request->input('gender', 'female'); // business / male / female
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
            return redirect()->route('search.directory', [
                'gender'    => $gender,
                'area_slug' => 'all',
                'job_slug'  => 'all',
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
                // 都道府県のみ指定（職種なし）→ /{gender}/{pref_slug}/all/ へ
                if ($norm->prefecture_id && !$norm->area_id && !$norm->job_type_id && !$norm->genre_id && !$norm->filter_slug) {
                    return redirect()->route('search.directory', [
                        'gender'    => $gender,
                        'area_slug' => $norm->prefecture->slug,
                        'job_slug'  => 'all',
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
                // filter_slug が設定されていればフィルター付きLPへ
                if ($norm->filter_slug) {
                    return redirect()->route('search.filtered_directory', [
                        'gender'      => $gender,
                        'area_slug'   => $areaSlug,
                        'job_slug'    => $jobTypeSlug,
                        'filter_slug' => $norm->filter_slug,
                    ], 301);
                }
                return redirect()->route('search.directory', [
                    'gender'      => $gender,
                    'area_slug'   => $areaSlug,
                    'job_slug'    => $jobTypeSlug,
                ], 301);
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
        $results = $this->getResults($gender, $area, $keyword, wageType: $wageType, wageMin: $wageMin, arubaito: $arubaito, allYouCanDrink: $allYouCanDrink, hasKaraoke: $hasKaraoke, hasPrivateRoom: $hasPrivateRoom, discountFirstSet: $discountFirstSet);

        return view('search.index', compact('gender', 'area', 'keyword', 'wageType', 'wageMin', 'arubaito', 'results', 'allYouCanDrink', 'hasKaraoke', 'hasPrivateRoom', 'discountFirstSet'));
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

        $results = $this->getResults($gender, '', '', prefSlug: $pref_slug, arubaito: $arubaito, allYouCanDrink: $allYouCanDrink, hasKaraoke: $hasKaraoke, hasPrivateRoom: $hasPrivateRoom, discountFirstSet: $discountFirstSet);

        $noindex = $results->total() <= 5;
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

        $lpStats          = $noindex ? null : $this->computeLpStats($gender, null, null, $pref_slug);
        $lpRelated        = $this->computeRelatedLinks($gender, null, $pref_slug, 'all', $prefModel);
        $relatedArticles  = $this->computeRelatedArticles($gender);

        return response()->view('search.index', compact(
            'gender', 'area_slug', 'job_slug', 'results',
            'areaName', 'jobTypeName',
            'area', 'keyword', 'wageType', 'wageMin', 'arubaito',
            'allYouCanDrink', 'hasKaraoke', 'hasPrivateRoom', 'discountFirstSet', 'isPrefPage',
            'noindex', 'lpStats', 'lpRelated', 'relatedArticles'
        ), $status);
    }

    // 正規化ディレクトリURL（LP）
    public function directory(Request $request, string $gender, string $area_slug, string $job_slug)
    {
        $areaModel    = $area_slug !== 'all' ? Area::where('slug', $area_slug)->first() : null;
        $prefOnlyModel = (!$areaModel && $area_slug !== 'all') ? Prefecture::where('slug', $area_slug)->first() : null;
        $jobTypeModel = $job_slug  !== 'all' ? JobType::where('slug', $job_slug)->first()  : null;
        $genreModel   = ($job_slug !== 'all' && !$jobTypeModel) ? Genre::where('slug', $job_slug)->first() : null;

        $area    = ($areaModel && $area_slug !== 'all') ? $area_slug : '';
        $keyword = $job_slug  === 'all' ? '' : $job_slug;

        $areaName    = $areaModel?->name ?? $prefOnlyModel?->name ?? '';
        $jobTypeName = $jobTypeModel?->name ?? $genreModel?->name ?? '';
        $prefModel   = $areaModel?->prefecture;
        $isPrefPage  = (bool) $prefOnlyModel;

        $wageType         = '';
        $wageMin          = 0;
        $arubaito         = $request->boolean('arubaito');
        $allYouCanDrink   = $request->boolean('all_you_can_drink');
        $hasKaraoke       = $request->boolean('has_karaoke');
        $hasPrivateRoom   = $request->boolean('has_private_room');
        $discountFirstSet = $request->boolean('discount_first_set');

        $results = $prefOnlyModel
            ? $this->getResults($gender, '', '', prefSlug: $area_slug, arubaito: $arubaito, allYouCanDrink: $allYouCanDrink, hasKaraoke: $hasKaraoke, hasPrivateRoom: $hasPrivateRoom, discountFirstSet: $discountFirstSet)
            : $this->getResults($gender, $area, $keyword, useSlug: true, arubaito: $arubaito, allYouCanDrink: $allYouCanDrink, hasKaraoke: $hasKaraoke, hasPrivateRoom: $hasPrivateRoom, discountFirstSet: $discountFirstSet);

        $noindex = $results->total() <= 5;
        $status  = $results->total() === 0 ? 404 : 200;

        SearchPageView::record($gender, $area_slug, $job_slug);

        $lpStats         = $noindex ? null : $this->computeLpStats($gender, $areaModel, $jobTypeModel ?? $genreModel, $prefOnlyModel ? $area_slug : '');
        $lpRelated       = $this->computeRelatedLinks($gender, $areaModel, $area_slug, $job_slug, $prefOnlyModel);
        $relatedArticles = $this->computeRelatedArticles($gender);

        return response()->view('search.index', compact(
            'gender', 'area_slug', 'job_slug', 'results',
            'areaName', 'jobTypeName', 'prefModel', 'isPrefPage',
            'area', 'keyword', 'wageType', 'wageMin', 'arubaito',
            'allYouCanDrink', 'hasKaraoke', 'hasPrivateRoom', 'discountFirstSet',
            'noindex', 'lpStats', 'lpRelated', 'relatedArticles'
        ), $status);
    }

    // フィルター付きディレクトリURL（LP + クイックタグ絞り込み）
    // 例: /male/shinjuku/all/hibarai/
    public function filteredDirectory(Request $request, string $gender, string $area_slug, string $job_slug, string $filter_slug)
    {
        $filterType = JobType::where('slug', $filter_slug)->whereNotNull('keyword_filter')->first();
        if (!$filterType) {
            abort(404);
        }

        $areaModel    = $area_slug !== 'all' ? Area::where('slug', $area_slug)->first() : null;
        $jobTypeModel = $job_slug  !== 'all' ? JobType::where('slug', $job_slug)->first()  : null;
        $genreModel   = ($job_slug !== 'all' && !$jobTypeModel) ? Genre::where('slug', $job_slug)->first() : null;

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

        $results = $this->getResults($gender, $area, $keyword, useSlug: true, filterKeyword: $filterType->keyword_filter, arubaito: $arubaito, allYouCanDrink: $allYouCanDrink, hasKaraoke: $hasKaraoke, hasPrivateRoom: $hasPrivateRoom, discountFirstSet: $discountFirstSet);

        $noindex = $results->total() <= 5;
        $status  = $results->total() === 0 ? 404 : 200;

        SearchPageView::record($gender, $area_slug, $job_slug);

        $lpStats         = $noindex ? null : $this->computeLpStats($gender, $areaModel, $jobTypeModel ?? $genreModel);
        $lpRelated       = $this->computeRelatedLinks($gender, $areaModel, $area_slug, $job_slug);
        $relatedArticles = $this->computeRelatedArticles($gender);

        return response()->view('search.index', compact(
            'gender', 'area_slug', 'job_slug', 'filter_slug', 'results',
            'areaName', 'jobTypeName', 'filterName', 'prefModel',
            'area', 'keyword', 'wageType', 'wageMin', 'arubaito',
            'allYouCanDrink', 'hasKaraoke', 'hasPrivateRoom', 'discountFirstSet',
            'noindex', 'lpStats', 'lpRelated', 'relatedArticles'
        ), $status);
    }

    private function getResults(string $gender, string $area, string $keyword, bool $useSlug = false, string $filterKeyword = '', string $wageType = '', int $wageMin = 0, bool $arubaito = false, bool $allYouCanDrink = false, bool $hasKaraoke = false, bool $hasPrivateRoom = false, bool $discountFirstSet = false, string $prefSlug = '')
    {
        $page    = (int) request()->input('page', 1);
        $perPage = 20;

        // キャッシュにはIDのみ保存（Eloquentオブジェクトはシリアライズ不可）
        $idsCacheKey = 'search_ids:' . md5(implode('|', [
            $gender, $area, $keyword, $useSlug, $filterKeyword,
            $wageType, $wageMin, $arubaito, $allYouCanDrink, $hasKaraoke,
            $hasPrivateRoom, $discountFirstSet, $prefSlug,
        ]));

        $allIds = Cache::remember($idsCacheKey, 300, function () use (
            $gender, $area, $keyword, $useSlug, $filterKeyword,
            $wageType, $wageMin, $arubaito, $allYouCanDrink, $hasKaraoke,
            $hasPrivateRoom, $discountFirstSet, $prefSlug
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

        if ($gender === 'business') {
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
                ->when($filterKeyword, fn($q) => $q->whereHas('shop', fn($s) => $s->where('name', 'like', "%{$filterKeyword}%")))
                ->when($allYouCanDrink,   fn($q) => $q->where('all_you_can_drink', true))
                ->when($hasKaraoke,      fn($q) => $q->where('has_karaoke', true))
                ->when($hasPrivateRoom,  fn($q) => $q->where('has_private_room', true))
                ->when($discountFirstSet, fn($q) => $q->where('discount_first_set', true))
                ->orderByDesc(fn($q) =>
                    $q->selectRaw('CASE
                        WHEN budget_balance >= bid_price THEN bid_price
                        WHEN main_image IS NOT NULL THEN 15
                        ELSE 5
                    END')->from('shops')->whereColumn('shops.id', 'shop_details.shop_id')
                )
                ->pluck('shop_details.id')->all();
        } else {
            $searchGroups = $gender === 'male'
                ? ['male', 'both']
                : ['female', 'both'];

            $query = Job::with(['shop.area', 'jobType', 'area'])
                ->where('status', 'active')
                ->whereIn('search_group', $searchGroups)
                ->withinPlanLimit()
                ->when($prefSlug, fn($q) => $q->whereHas('area.prefecture', fn($p) => $p->where('slug', $prefSlug)))
                ->when($area, fn($q) => $useSlug
                    ? $q->whereHas('area', fn($a) =>
                        $a->where('slug', $area)
                          ->orWhereHas('parent', fn($p) => $p->where('slug', $area))
                          ->orWhereHas('prefecture', fn($p) => $p->where('slug', $area))
                      )
                    : $q->where(fn($q2) =>
                        $q2->whereHas('area', fn($a) => $a->where('name', 'like', "%{$area}%")
                                ->orWhere('slug', 'like', "%{$area}%"))
                           ->orWhereHas('shop', fn($s) =>
                                $s->where('nearest_line', 'like', "%{$area}%")
                                  ->orWhere('nearest_station_name', 'like', "%{$stationArea}%"))
                           ->orWhereHas('area', fn($a) =>
                                $a->whereHas('prefecture', fn($p) => $p->where('name', 'like', "%{$area}%")))
                    )
                )
                ->when($keyword, fn($q) => $useSlug
                    ? ($keywordFilterValue
                        // keyword_filter型：タイトル全文検索
                        ? $this->whereTitleMatch($q, $keywordFilterValue)
                        // 通常LP検索：job_type slug/group_slug OR genre slug
                        : $q->where(fn($q2) =>
                            $q2->whereHas('jobType', fn($j) => $j->where('slug', $keyword)->orWhere('group_slug', $keyword))
                               ->orWhereHas('shop', fn($s) => $s->whereHas('genre', fn($g) => $g->where('slug', $keyword)))
                          )
                      )
                    : $q->where(fn($q2) =>
                        $this->whereTitleMatch($q2, $keyword)
                           ->orWhereHas('jobType', fn($j) => $j->where('name', 'like', "%{$keyword}%"))
                           ->orWhereHas('shop', fn($s) => $s->whereHas('genre', fn($g) => $g->where('name', 'like', "%{$keyword}%")))
                      )
                )
                ->when($filterKeyword, fn($q) => $this->whereTitleMatch($q, $filterKeyword))
                ->when($wageType && $wageMin > 0, fn($q) =>
                    $q->where('wage_type', $wageType)->where('hourly_wage_min', '>=', $wageMin)
                )
                ->when($arubaito, fn($q) =>
                    $q->where('wage_type', 'hourly')->where('employment_type', 'PART_TIME')
                )
                ->orderByDesc(fn($q) =>
                    $q->selectRaw('CASE
                        WHEN budget_balance >= bid_price THEN bid_price
                        WHEN main_image IS NOT NULL THEN 15
                        ELSE 5
                    END')->from('shops')->whereColumn('shops.id', 'jobs.shop_id')
                )
                ->pluck('jobs.id')->all();
        }

        return $query;
        }); // Cache::remember

        // ページ分のIDを切り出してモデルを取得（キャッシュ外）
        $total   = count($allIds);
        $pageIds = array_slice($allIds, ($page - 1) * $perPage, $perPage);

        if (empty($pageIds)) {
            $items = collect();
        } elseif ($gender === 'business') {
            $idOrder = implode(',', $pageIds);
            $items = ShopDetail::with(['shop.area', 'shop.genre'])
                ->whereIn('id', $pageIds)
                ->orderByRaw("FIELD(id, {$idOrder})")
                ->get();
        } else {
            $idOrder = implode(',', $pageIds);
            $items = Job::with(['shop.area', 'jobType', 'area'])
                ->whereIn('id', $pageIds)
                ->orderByRaw("FIELD(id, {$idOrder})")
                ->get();
        }

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /** LP統計バー用の集計（noindexページでは呼ばない） */
    private function computeLpStats(string $gender, ?Area $areaModel, mixed $typeModel, string $prefSlug = ''): array
    {
        if ($gender === 'business') {
            $query = ShopDetail::where('status', 'active')
                ->when($areaModel, fn($q) => $q->whereHas('shop', fn($s) => $s->where('area_id', $areaModel->id)))
                ->when($prefSlug, fn($q) => $q->whereHas('shop.area.prefecture', fn($p) => $p->where('slug', $prefSlug)));

            return [
                'all_you_can_drink' => (clone $query)->where('all_you_can_drink', true)->count(),
                'has_karaoke'       => (clone $query)->where('has_karaoke', true)->count(),
                'has_private_room'  => (clone $query)->where('has_private_room', true)->count(),
                'discount_first_set'=> (clone $query)->where('discount_first_set', true)->count(),
            ];
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
            ->when($prefSlug, fn($q) => $q->whereHas('area.prefecture', fn($p) => $p->where('slug', $prefSlug)))
            ->when($typeModel, fn($q) => $q->whereHas('jobType', fn($j) =>
                $j->where('slug', $typeModel->slug)->orWhere('group_slug', $typeModel->slug ?? '')
            ));

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
            'daily_count'     => (int) ($agg?->daily_count     ?? 0),
            'part_time_count' => (int) ($agg?->part_time_count ?? 0),
        ];

        // 時給：female/male 共通、5件以上あれば表示
        if ($hourlyCount >= 5) {
            $stats['avg_hourly']    = (int) $agg->avg_hourly;
            $stats['hourly_count']  = $hourlyCount;
        }

        // 月給：male のみ、5件以上あれば表示
        if ($gender === 'male' && $monthlyCount >= 5) {
            $stats['avg_monthly']   = (int) $agg->avg_monthly;
            $stats['monthly_count'] = $monthlyCount;
        }

        return $stats;
    }

    /** LP関連リンク用データ（noindexページでも表示） */
    private function computeRelatedLinks(string $gender, ?Area $areaModel, string $areaSlug, string $jobSlug, ?Prefecture $prefModel = null): array
    {
        $searchGroups = match($gender) {
            'male'     => ['male', 'both'],
            'business' => ['business'],
            default    => ['female', 'both'],
        };

        // 関連エリア：同都道府県の他エリア（求人/店舗あり）
        $relatedAreas = collect();
        if ($areaModel?->prefecture_id) {
            $relatedAreas = Area::where('prefecture_id', $areaModel->prefecture_id)
                ->where('id', '!=', $areaModel->id)
                ->when($gender === 'business',
                    fn($q) => $q->whereHas('shops', fn($s) => $s->where('status', 'active')),
                    fn($q) => $q->whereHas('jobs', fn($j) => $j->where('status', 'active')->whereIn('search_group', $searchGroups))
                )
                ->orderBy('sort_order')
                ->limit(10)
                ->get(['id', 'name', 'slug']);
        } elseif ($prefModel) {
            // 都道府県LPの場合：同都道府県のエリア一覧
            $relatedAreas = Area::where('prefecture_id', $prefModel->id)
                ->when($gender === 'business',
                    fn($q) => $q->whereHas('shops', fn($s) => $s->where('status', 'active')),
                    fn($q) => $q->whereHas('jobs', fn($j) => $j->where('status', 'active')->whereIn('search_group', $searchGroups))
                )
                ->orderBy('sort_order')
                ->limit(10)
                ->get(['id', 'name', 'slug']);
        }

        // 関連職種/業種
        $relatedTypes = collect();
        if ($gender === 'business') {
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

        return ['areas' => $relatedAreas, 'types' => $relatedTypes];
    }

    private function computeRelatedArticles(string $gender): \Illuminate\Database\Eloquent\Collection
    {
        $articleGender = $gender === 'business' ? 'business' : $gender;

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
