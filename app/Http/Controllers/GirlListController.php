<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Cast;
use App\Models\CastDiary;
use App\Models\CastReview;
use App\Models\Prefecture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GirlListController extends Controller
{
    private const PER_PAGE = 24;

    private const AGE_RANGES = [
        'teens'     => [18, 19,  '10代'],
        '20s_early' => [20, 24,  '20代前半'],
        '20s_late'  => [25, 29,  '20代後半'],
        '30s_early' => [30, 34,  '30代前半'],
        '30s_late'  => [35, 39,  '30代後半'],
        '40s'       => [40, 49,  '40代'],
        '50s'       => [50, 59,  '50代'],
        '60s'       => [60, 69,  '60代'],
        '70s'       => [70, 120, '70代以上'],
    ];

    private const TALL_RANGES = [
        'short'  => [null, 150,  '〜150cm'],
        'mid'    => [151,  160,  '151〜160cm'],
        'tall'   => [161,  170,  '161〜170cm'],
        'super'  => [171,  null, '171cm〜'],
    ];

    private const CUP_GROUPS = [
        'ab'     => ['A', 'B',             'A・Bカップ'],
        'c'      => ['C',                  'Cカップ'],
        'd'      => ['D',                  'Dカップ'],
        'ef'     => ['E', 'F',             'E・Fカップ'],
        'g_plus' => ['G', 'H', 'I', 'J', 'K', 'L', 'M', 'G以上'],
    ];

    private function resolveArea(string $area_slug): array
    {
        $areaId = $area_slug !== 'all'
            ? Cache::remember("slug:area_id:{$area_slug}", 86400, fn() => Area::where('slug', $area_slug)->value('id'))
            : null;
        $areaModel = $areaId ? Area::with('prefecture')->find($areaId) : null;

        $prefId = (!$areaModel && $area_slug !== 'all')
            ? Cache::remember("slug:pref_id:{$area_slug}", 86400, fn() => Prefecture::where('slug', $area_slug)->value('id'))
            : null;
        $prefOnlyModel = $prefId ? Prefecture::find($prefId) : null;

        return [$areaModel, $prefOnlyModel];
    }

    private function applyAreaScope($query, ?Area $areaModel, ?Prefecture $prefOnlyModel, string $area_slug)
    {
        if ($areaModel) {
            $query->whereHas('area', fn($a) =>
                $a->where('slug', $area_slug)
                  ->orWhereHas('parent', fn($p) => $p->where('slug', $area_slug))
                  ->orWhereHas('prefecture', fn($p) => $p->where('slug', $area_slug))
            );
        } elseif ($prefOnlyModel) {
            $query->whereHas('area.prefecture', fn($p) => $p->where('slug', $area_slug));
        }
        return $query;
    }

    private function applyFilters($query, Request $request): void
    {
        if ($age = $request->input('age')) {
            if (isset(self::AGE_RANGES[$age])) {
                [$min, $max] = self::AGE_RANGES[$age];
                $query->whereBetween('age', [$min, $max]);
            }
        }

        if ($tall = $request->input('tall')) {
            if (isset(self::TALL_RANGES[$tall])) {
                [$min, $max] = self::TALL_RANGES[$tall];
                if ($min !== null) $query->where('tall', '>=', $min);
                if ($max !== null) $query->where('tall', '<=', $max);
            }
        }

        if ($cup = $request->input('cup')) {
            if (isset(self::CUP_GROUPS[$cup])) {
                $cups = array_slice(self::CUP_GROUPS[$cup], 0, -1);
                $query->whereIn('cup', $cups);
            }
        }

        if ($body = $request->input('body')) {
            $query->where('body_id', (int) $body);
        }
    }

    private function hasActiveFilters(Request $request): bool
    {
        return $request->hasAny(['age', 'tall', 'cup', 'body']);
    }

    public function index(Request $request, string $area_slug)
    {
        return $this->render($request, $area_slug, 'all');
    }

    public function tab(Request $request, string $area_slug, string $cast_tab)
    {
        return $this->render($request, $area_slug, $cast_tab);
    }

    private function render(Request $request, string $area_slug, string $cast_tab)
    {
        [$areaModel, $prefOnlyModel] = $this->resolveArea($area_slug);

        $areaName = $areaModel?->name ?? $prefOnlyModel?->name ?? '全国';
        $prefModel = $areaModel?->prefecture;

        if ($cast_tab === 'diary') {
            $results = $this->getDiaries($area_slug, $areaModel, $prefOnlyModel);
        } elseif ($cast_tab === 'review') {
            $results = $this->getReviews($area_slug, $areaModel, $prefOnlyModel);
        } else {
            $results = $this->getCasts($request, $area_slug, $areaModel, $prefOnlyModel, $cast_tab);
        }

        $filterCount = collect(['age', 'tall', 'cup', 'body'])->filter(fn($k) => $request->filled($k))->count();
        $hasFilters = $filterCount > 0;
        $bodyTypesRaw = Cache::remember('delicon:cast_body_types', 86400,
            fn() => DB::table('cast_body_types')->orderBy('sort_order')->orderBy('id')->get(['id', 'name'])
                ->map(fn($b) => ['id' => $b->id, 'name' => $b->name])->all()
        );
        $bodyTypes = collect($bodyTypesRaw)->map(fn($b) => (object) $b)->all();

        $prefecturesRaw = $area_slug === 'all'
            ? Cache::remember('delicon:prefectures_with_casts', 3600, fn() =>
                DB::table('prefectures')
                    ->join('areas', 'areas.prefecture_id', '=', 'prefectures.id')
                    ->join('shops', 'shops.area_id', '=', 'areas.id')
                    ->join('casts', 'casts.shop_id', '=', 'shops.id')
                    ->where('shops.status', 'active')
                    ->where('casts.status', 'active')
                    ->whereNotNull('prefectures.slug')
                    ->select('prefectures.slug', 'prefectures.prefecture as name')
                    ->groupBy('prefectures.id', 'prefectures.slug', 'prefectures.prefecture')
                    ->orderBy('prefectures.id')
                    ->get()->map(fn($p) => ['slug' => $p->slug, 'name' => $p->name])->all()
              )
            : [];
        $prefectureLinks = collect($prefecturesRaw)->map(fn($p) => (object) $p)->all();

        $noindex = $results->total() < 5 || $filterCount >= 2;
        $status  = $results->total() === 0 ? 404 : 200;

        return response()->view('search.girl_list', compact(
            'area_slug', 'cast_tab', 'areaName', 'prefModel',
            'areaModel', 'prefOnlyModel', 'results', 'noindex',
            'hasFilters', 'bodyTypes', 'prefectureLinks'
        ), $status);
    }

    private function getCasts(Request $request, string $area_slug, ?Area $areaModel, ?Prefecture $prefOnlyModel, string $cast_tab)
    {
        $query = Cast::with(['shop'])
            ->where('status', 'active')
            ->whereHas('shop', function ($q) use ($area_slug, $areaModel, $prefOnlyModel) {
                $q->where('status', 'active');
                $this->applyAreaScope($q, $areaModel, $prefOnlyModel, $area_slug);
            });

        $this->applyFilters($query, $request);

        if ($cast_tab === 'standby') {
            $query->whereDate('working_date', today());
            $query->orderBy('sort_order')->orderByDesc('updated_at');
        } elseif ($cast_tab === 'new') {
            $query->where('is_new', true)
                  ->where(fn($q) => $q->whereNull('new_since')->orWhere('new_since', '>=', now()->subDays(30)));
            $query->orderByDesc('new_since')->orderByDesc('created_at');
        } else {
            $query->orderBy('sort_order')->orderByDesc('updated_at');
        }

        return $query->paginate(self::PER_PAGE)->withQueryString();
    }

    private function getDiaries(string $area_slug, ?Area $areaModel, ?Prefecture $prefOnlyModel)
    {
        return CastDiary::with(['cast.shop', 'images'])
            ->where('status', 'published')
            ->whereHas('cast', function ($q) use ($area_slug, $areaModel, $prefOnlyModel) {
                $q->where('status', 'active')
                  ->whereHas('shop', function ($s) use ($area_slug, $areaModel, $prefOnlyModel) {
                      $s->where('status', 'active');
                      $this->applyAreaScope($s, $areaModel, $prefOnlyModel, $area_slug);
                  });
            })
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    private function getReviews(string $area_slug, ?Area $areaModel, ?Prefecture $prefOnlyModel)
    {
        return CastReview::with(['cast.shop'])
            ->where('is_approved', true)
            ->whereHas('cast', function ($q) use ($area_slug, $areaModel, $prefOnlyModel) {
                $q->where('status', 'active')
                  ->whereHas('shop', function ($s) use ($area_slug, $areaModel, $prefOnlyModel) {
                      $s->where('status', 'active');
                      $this->applyAreaScope($s, $areaModel, $prefOnlyModel, $area_slug);
                  });
            })
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    public function byType(Request $request, string $area_slug, string $type_slug)
    {
        $girlTypeRaw = Cache::remember("slug:girl_type:{$type_slug}", 86400,
            fn() => (array) DB::table('girl_types')->where('slug', $type_slug)
                ->first(['id', 'name', 'slug', 'age_min', 'age_max', 'tall_min', 'tall_max', 'body_type_id'])
        );

        if (!($girlTypeRaw['id'] ?? null)) abort(404);
        $typeId     = $girlTypeRaw['id'];
        $typeName   = $girlTypeRaw['name'];
        $ageMin     = $girlTypeRaw['age_min'];
        $ageMax     = $girlTypeRaw['age_max'];
        $tallMin    = $girlTypeRaw['tall_min'];
        $tallMax    = $girlTypeRaw['tall_max'];
        $bodyTypeId = $girlTypeRaw['body_type_id'];

        [$areaModel, $prefOnlyModel] = $this->resolveArea($area_slug);
        $areaName  = $areaModel?->name ?? $prefOnlyModel?->name ?? '全国';
        $prefModel = $areaModel?->prefecture;

        $query = Cast::with(['shop'])
            ->where('status', 'active')
            ->whereHas('shop', function ($q) use ($area_slug, $areaModel, $prefOnlyModel) {
                $q->where('status', 'active');
                $this->applyAreaScope($q, $areaModel, $prefOnlyModel, $area_slug);
            });

        if ($ageMin !== null || $ageMax !== null) {
            if ($ageMin !== null) $query->where('age', '>=', $ageMin);
            if ($ageMax !== null) $query->where('age', '<=', $ageMax);
        } elseif ($tallMin !== null || $tallMax !== null) {
            if ($tallMin !== null) $query->where('tall', '>=', $tallMin);
            if ($tallMax !== null) $query->where('tall', '<=', $tallMax);
        } elseif ($bodyTypeId !== null) {
            $query->where('body_id', $bodyTypeId);
        } else {
            $query->where('type_id', $typeId);
        }

        $results = $query->orderBy('sort_order')->orderByDesc('updated_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $noindex  = $results->total() <= 5;
        $status   = $results->total() === 0 ? 404 : 200;
        $cast_tab = 'type';
        $hasFilters = false;
        $bodyTypes  = [];
        $prefectureLinks = [];

        return response()->view('search.girl_list', compact(
            'area_slug', 'cast_tab', 'areaName', 'prefModel',
            'areaModel', 'prefOnlyModel', 'results', 'noindex',
            'typeName', 'type_slug', 'hasFilters', 'bodyTypes', 'prefectureLinks'
        ), $status);
    }


    public static function ageRanges(): array  { return self::AGE_RANGES; }
    public static function tallRanges(): array { return self::TALL_RANGES; }
    public static function cupGroups(): array  { return self::CUP_GROUPS; }
}
