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
            ->paginate(30);

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

        $similarCasts = $this->getSimilarCasts($cast);

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

        return view('cast.show', compact('cast', 'otherCasts', 'isFavorited', 'similarCasts', 'footerPrefSlug', 'likedDiaryIds', 'noindex'));
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

        // クローラー除外
        $ua = strtolower($request->userAgent() ?? '');
        if ($ua === '') return;
        foreach (['bot','crawl','spider','slurp','mediapartners','facebookexternalhit',
                  'twitterbot','linkedinbot','whatsapp','applebot','pinterest',
                  'semrush','ahrefsbot','mj12bot','dotbot','bingpreview',
                  'yandex','baiduspider','duckduckbot'] as $p) {
            if (str_contains($ua, $p)) return;
        }

        // 同一IP 1時間以内の重複排除（ログイン済みはユーザーIDで、非ログインはIPで）
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
                (CASE WHEN shops.plan <= 3 THEN 1 ELSE 0 END)
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
