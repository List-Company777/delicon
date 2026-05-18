<?php

namespace App\Http\Controllers;

use App\Models\Cast;
use App\Models\CastDeletionRequest;
use App\Models\CastType;
use App\Models\CastBodyType;
use Illuminate\Http\Request;
use App\Models\CastFavorite;
use App\Models\CastView;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CastController extends Controller
{
    public function index(Request $request)
    {
        $castTypesRaw = Cache::remember('delicon:cast_types', 3600, fn() =>
            CastType::orderBy('id')->get()->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->all()
        );
        $castTypes = collect($castTypesRaw)->map(fn($t) => (object) $t);

        $bodyTypesRaw = Cache::remember('delicon:cast_body_types', 3600, fn() =>
            CastBodyType::orderBy('id')->get()->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->all()
        );
        $bodyTypes = collect($bodyTypesRaw)->map(fn($t) => (object) $t);

        $query = Cast::active()
            ->with(['shop', 'castType', 'bodyType', 'tags'])
            ->whereHas('shop', fn($q) => $q->where('status', 'active'));

        if ($request->filled('type')) {
            $query->where('type_id', $request->type);
        }
        if ($request->filled('body')) {
            $query->where('body_id', $request->body);
        }
        if ($request->filled('age_from')) {
            $query->where('age', '>=', (int) $request->age_from);
        }
        if ($request->filled('age_to')) {
            $query->where('age', '<=', (int) $request->age_to);
        }
        if ($request->filled('cup')) {
            $query->where('cup', $request->cup);
        }

        $casts = $query->orderByDesc('is_recommended')
            ->orderBy('sort_order')
            ->paginate(30)
            ->withPath(rtrim(request()->url(), '/') . '/')->withQueryString();

        return view('cast.index', compact('casts', 'castTypes', 'bodyTypes'));
    }

    public function show(Cast $cast)
    {
        if ($cast->status !== 'active') {
            abort(404);
        }

        $cast->load([
            'shop', 'castType', 'bodyType',
            'charms', 'plays', 'personalities', 'tags',
            'images', 'schedules', 'reviews', 'diaries.images', 'diaries.likes',
        ]);

        $this->recordView($cast);

        $isFavorited = auth()->check()
            ? CastFavorite::where('user_id', auth()->id())->where('cast_id', $cast->id)->exists()
            : false;

        // 類似キャスト（IDをキャッシュ → 軽量whereInで再取得）
        $similarCastIds = Cache::remember("cast:similar_ids:{$cast->id}", 1800,
            fn() => $this->getSimilarCasts($cast)->pluck('id')->all()
        );
        $similarCasts = $similarCastIds
            ? Cast::active()->with(['shop'])->whereIn('id', $similarCastIds)
                ->orderByRaw('FIELD(id,' . implode(',', $similarCastIds) . ')')
                ->get()
            : collect();

        $shopPlan = $cast->shop?->plan ?? 5;

        // 無料店所属の場合：同エリア有料店の類似キャスト（IDキャッシュ）
        $nearbyPaidSimilarCastIds = ($shopPlan >= 4 && $cast->shop?->area_id)
            ? Cache::remember("cast:nearby_paid_similar_ids:{$cast->id}", 1800,
                fn() => $this->getNearbyPaidSimilarCasts($cast)->pluck('id')->all()
              )
            : [];
        $nearbyPaidSimilarCasts = $nearbyPaidSimilarCastIds
            ? Cast::active()->with(['shop'])->whereIn('id', $nearbyPaidSimilarCastIds)
                ->orderByRaw('FIELD(id,' . implode(',', $nearbyPaidSimilarCastIds) . ')')
                ->get()
            : collect();

        $otherCasts = Cast::active()
            ->where('shop_id', $cast->shop_id)
            ->where('id', '!=', $cast->id)
            ->with(['castType'])
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        $footerPrefSlug = null;
        if ($cast->shop?->area_id) {
            $areaId = $cast->shop->area_id;
            $footerPrefSlug = Cache::remember("slug:pref_by_area:{$areaId}", 86400,
                fn() => DB::table('prefectures')
                    ->join('areas', 'areas.prefecture_id', '=', 'prefectures.id')
                    ->where('areas.id', $areaId)
                    ->value('prefectures.slug')
            );
        }

        $likedDiaryIds = auth()->check()
            ? \App\Models\DiaryLike::where('user_id', auth()->id())
                ->whereIn('diary_id', $cast->diaries->pluck('id'))
                ->pluck('diary_id')->all()
            : [];

        $noindex = mb_strlen($cast->comment ?? '') < 100;

        // 無料店所属の場合：同エリア・同ジャンルの有料店（店舗IDキャッシュ）
        $nearbyPaidShops = collect();
        if ($shopPlan >= 4) {
            $areaId  = $cast->shop?->area_id;
            $genreId = $cast->shop?->genre_id;
            if ($areaId && $genreId) {
                $nearbyPaidShopIds = Cache::remember("cast:nearby_paid_shop_ids:{$cast->id}", 3600,
                    function () use ($cast, $areaId, $genreId) {
                        $ids = \App\Models\Shop::where('status', 'active')
                            ->whereBetween('plan', [1, 3])
                            ->where('genre_id', $genreId)
                            ->where('area_id', $areaId)
                            ->whereNotNull('main_image')
                            ->orderByDesc('rank_score')
                            ->limit(3)
                            ->pluck('id')->all();
                        if (count($ids) < 3) {
                            $prefId = $cast->shop->prefecture_id;
                            if ($prefId) {
                                $extra = \App\Models\Shop::where('status', 'active')
                                    ->whereBetween('plan', [1, 3])
                                    ->where('genre_id', $genreId)
                                    ->where('prefecture_id', $prefId)
                                    ->whereNotIn('id', $ids)
                                    ->whereNotNull('main_image')
                                    ->orderByDesc('rank_score')
                                    ->limit(3 - count($ids))
                                    ->pluck('id')->all();
                                $ids = array_merge($ids, $extra);
                            }
                        }
                        return $ids;
                    }
                );
                if ($nearbyPaidShopIds) {
                    $nearbyPaidShops = \App\Models\Shop::whereIn('id', $nearbyPaidShopIds)
                        ->with('area:id,name,slug')
                        ->orderByRaw('FIELD(id,' . implode(',', $nearbyPaidShopIds) . ')')
                        ->get(['id', 'name', 'plan', 'rank_score', 'main_image', 'area_id']);
                }
            }
        }

        return view('cast.show', compact('cast', 'otherCasts', 'isFavorited', 'similarCasts', 'nearbyPaidSimilarCasts', 'footerPrefSlug', 'likedDiaryIds', 'noindex', 'nearbyPaidShops'));
    }

    public function submitDeletionRequest(Request $request, Cast $cast)
    {
        // ハニーポット（ボット対策）
        if ($request->filled('website')) {
            return redirect()->route('cast.show', $cast->id)->with('deletion_sent', true);
        }
        if (!($cast->shop?->isPaid() ?? false)) {
            abort(403);
        }

        $request->validate([
            'requester_name'  => ['required', 'string', 'max:50'],
            'requester_email' => ['required', 'email', 'max:100'],
            'reason'          => ['nullable', 'string', 'max:500'],
        ]);

        CastDeletionRequest::create([
            'cast_id'         => $cast->id,
            'requester_name'  => $request->requester_name,
            'requester_email' => $request->requester_email,
            'reason'          => $request->reason,
        ]);

        return redirect()->route('cast.show', $cast->id)->with('deletion_sent', true);
    }

    private function recordView(Cast $cast): void
    {
        $request = request();

        $ua = strtolower($request->userAgent() ?? '');
        if ($ua === '') return;
        foreach (['bot','crawl','spider','slurp','mediapartners','facebookexternalhit',
                  'twitterbot','linkedinbot','whatsapp','applebot','pinterest',
                  'semrush','ahrefsbot','mj12bot','dotbot','bingpreview',
                  'yandex','baiduspider','duckduckbot'] as $p) {
            if (str_contains($ua, $p)) return;
        }

        if (auth()->check()) {
            $cacheKey = "cast_view:{$cast->id}:u" . auth()->id();
        } else {
            $cacheKey = "cast_view:{$cast->id}:{$request->ip()}";
        }
        if (!Cache::add($cacheKey, 1, 3600)) return;

        $data = [
            'cast_id'   => $cast->id,
            'viewed_at' => now(),
        ];

        if (auth()->check()) {
            $data['user_id']    = auth()->id();
            $data['session_id'] = null;
        } else {
            $data['user_id']    = null;
            $data['session_id'] = session()->getId();
        }

        CastView::create($data);

        if (!auth()->check()) {
            $viewed = session()->get('viewed_cast_ids', []);
            $viewed = array_values(array_unique(array_merge([$cast->id], $viewed)));
            session()->put('viewed_cast_ids', array_slice($viewed, 0, 10));
        }
    }

    private function getNearbyPaidSimilarCasts(Cast $cast, int $limit = 3): \Illuminate\Support\Collection
    {
        return Cast::active()
            ->where('casts.id', '!=', $cast->id)
            ->join('shops', 'casts.shop_id', '=', 'shops.id')
            ->where('shops.status', 'active')
            ->whereBetween('shops.plan', [1, 3])
            ->where('shops.area_id', $cast->shop->area_id)
            ->with(['shop'])
            ->select('casts.*')
            ->selectRaw('
                (CASE WHEN casts.body_id = ? THEN 3 ELSE 0 END) +
                (CASE WHEN casts.type_id = ? THEN 3 ELSE 0 END) +
                (CASE WHEN casts.age IS NOT NULL AND ABS(CAST(casts.age AS SIGNED) - ?) <= 5 THEN 1 ELSE 0 END)
                AS similarity_score',
                [$cast->body_id ?? 0, $cast->type_id ?? 0, $cast->age ?? 0]
            )
            ->orderByDesc('similarity_score')
            ->orderByDesc('casts.is_recommended')
            ->take($limit)
            ->get();
    }

    private function getSimilarCasts(Cast $cast, int $limit = 6): \Illuminate\Support\Collection
    {
        return Cast::active()
            ->where('casts.id', '!=', $cast->id)
            ->join('shops', 'casts.shop_id', '=', 'shops.id')
            ->where('shops.status', 'active')
            ->with(['shop'])
            ->select('casts.*')
            ->selectRaw('
                (CASE WHEN casts.body_id = ? THEN 2 ELSE 0 END) +
                (CASE WHEN casts.type_id = ? THEN 2 ELSE 0 END) +
                (CASE WHEN casts.age IS NOT NULL AND ABS(CAST(casts.age AS SIGNED) - ?) <= 3 THEN 1 ELSE 0 END) +
                (CASE WHEN shops.plan <= 4 THEN 1 ELSE 0 END)
                AS similarity_score',
                [$cast->body_id ?? 0, $cast->type_id ?? 0, $cast->age ?? 0]
            )
            ->having('similarity_score', '>', 0)
            ->orderByDesc('similarity_score')
            ->orderByDesc('is_recommended')
            ->take($limit)
            ->get();
    }
}
