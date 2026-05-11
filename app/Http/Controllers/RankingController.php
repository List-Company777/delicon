<?php
namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Cast;
use App\Models\Prefecture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RankingController extends Controller
{
    public function index()
    {
        $rankingIds = $this->fetchRankingIds('delicon:ranking', null, null);
        $ranking    = $this->loadCasts($rankingIds);
        $noindex    = false;

        $prefLinks = Cache::remember('ranking:pref_links', 3600, function () {
            return DB::table('prefectures')
                ->join('shops', 'shops.prefecture_id', '=', 'prefectures.id')
                ->join('casts', 'casts.shop_id', '=', 'shops.id')
                ->where('casts.status', 'active')
                ->where('shops.status', 'active')
                ->select('prefectures.prefecture as name', 'prefectures.slug')
                ->distinct()
                ->orderBy('prefectures.id')
                ->get()
                ->map(fn($r) => ['name' => $r->name, 'slug' => $r->slug])
                ->all();
        });

        return view('ranking.index', [
            'ranking'   => $ranking,
            'noindex'   => $noindex,
            'pageTitle' => '人気女性ランキング',
            'pageDesc'  => '電話・お気に入り・口コミ・閲覧数から算出した人気女性ランキングTOP30。',
            'canonical' => route('ranking.index'),
            'pageType'  => 'all',
            'navLabel'  => '都道府県から探す',
            'navLinks'  => $prefLinks,
            'prefModel' => null,
        ]);
    }

    public function bySlug(string $slug)
    {
        [$areaModel, $prefModel] = $this->resolveArea($slug);

        if (!$areaModel && !$prefModel) {
            abort(404);
        }

        if ($areaModel) {
            $pref    = $areaModel->prefecture;
            $areaIds = Cache::remember("ranking:area_ids:{$slug}", 86400, function () use ($slug, $areaModel) {
                return DB::table('areas')
                    ->where(fn($q) => $q->where('slug', $slug)->orWhere('parent_id', $areaModel->id))
                    ->pluck('id')->all();
            });

            $rankingIds = $this->fetchRankingIds("delicon:ranking:{$slug}", $areaIds, null);
            $ranking    = $this->loadCasts($rankingIds);
            $noindex    = count($rankingIds) < 5;

            $siblingLinks = Cache::remember("ranking:siblings:{$slug}", 3600, function () use ($pref, $areaModel) {
                return DB::table('areas')
                    ->where('prefecture_id', $pref->id)
                    ->where('id', '!=', $areaModel->id)
                    ->orderBy('sort_order')
                    ->select('name', 'slug')
                    ->get()
                    ->map(fn($r) => ['name' => $r->name, 'slug' => $r->slug])
                    ->all();
            });

            return view('ranking.index', [
                'ranking'   => $ranking,
                'noindex'   => $noindex,
                'pageTitle' => "{$areaModel->name}の人気女性ランキング",
                'pageDesc'  => "{$areaModel->name}（{$pref->name}）のデリヘル人気女性ランキングTOP30。電話・お気に入り・口コミ・閲覧数から算出。",
                'canonical' => route('ranking.area', $slug),
                'pageType'  => 'area',
                'navLabel'  => "{$pref->name}の他エリア",
                'navLinks'  => $siblingLinks,
                'prefModel' => $pref,
            ]);
        }

        // 都道府県ランキング
        $rankingIds = $this->fetchRankingIds("delicon:ranking:{$slug}", null, $prefModel->id);
        $ranking    = $this->loadCasts($rankingIds);
        $noindex    = count($rankingIds) < 5;

        $areaLinks = Cache::remember("ranking:pref_areas:{$slug}", 3600, function () use ($prefModel) {
            return DB::table('areas')
                ->where('prefecture_id', $prefModel->id)
                ->orderBy('sort_order')
                ->select('name', 'slug')
                ->get()
                ->map(fn($r) => ['name' => $r->name, 'slug' => $r->slug])
                ->all();
        });

        return view('ranking.index', [
            'ranking'   => $ranking,
            'noindex'   => $noindex,
            'pageTitle' => "{$prefModel->name}の人気女性ランキング",
            'pageDesc'  => "{$prefModel->name}のデリヘル人気女性ランキングTOP30。電話・お気に入り・口コミ・閲覧数から算出。",
            'canonical' => route('ranking.area', $slug),
            'pageType'  => 'pref',
            'navLabel'  => "{$prefModel->name}のエリアから探す",
            'navLinks'  => $areaLinks,
            'prefModel' => $prefModel,
        ]);
    }

    public function recordTelClick(Request $request, int $castId)
    {
        if ($this->isCrawler($request)) {
            return response()->noContent();
        }

        $cacheKey = "tel_click:{$castId}:{$request->ip()}";
        if (!Cache::add($cacheKey, 1, 3600)) {
            return response()->noContent();
        }

        DB::table('cast_tel_clicks')->insert([
            'cast_id'    => $castId,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
        Cache::forget('delicon:ranking');
        return response()->noContent();
    }

    private function fetchRankingIds(string $cacheKey, ?array $areaIds, ?int $prefId): array
    {
        return Cache::remember($cacheKey, 3600, function () use ($areaIds, $prefId) {
            $since = now()->subDays(7);

            $planBonus = "
                CASE WHEN shops.plan = 1 THEN 50
                     WHEN shops.plan = 2 THEN 30
                     WHEN shops.plan = 3 THEN 15
                     ELSE 0 END
            ";

            $q = Cast::query()
                ->join('shops', 'shops.id', '=', 'casts.shop_id')
                ->where('casts.status', 'active')
                ->where('shops.status', 'active')
                ->leftJoin(
                    DB::raw("(SELECT cast_id, COUNT(*) AS tel_clicks FROM cast_tel_clicks WHERE created_at >= '{$since}' GROUP BY cast_id) tc"),
                    'tc.cast_id', '=', 'casts.id'
                )
                ->leftJoin(
                    DB::raw("(SELECT cast_id, COUNT(*) AS fav_count FROM cast_favorites GROUP BY cast_id) fc"),
                    'fc.cast_id', '=', 'casts.id'
                )
                ->leftJoin(
                    DB::raw("(SELECT cast_id, COUNT(*) AS review_count FROM cast_reviews WHERE is_approved = 1 GROUP BY cast_id) rc"),
                    'rc.cast_id', '=', 'casts.id'
                )
                ->leftJoin(
                    DB::raw("(SELECT cast_id, COUNT(*) AS view_count FROM cast_views WHERE viewed_at >= '{$since}' GROUP BY cast_id) vc"),
                    'vc.cast_id', '=', 'casts.id'
                )
                ->selectRaw("casts.id, (
                    COALESCE(tc.tel_clicks, 0) * 10
                    + COALESCE(fc.fav_count, 0) * 3
                    + COALESCE(rc.review_count, 0) * 5
                    + COALESCE(vc.view_count, 0) * 1
                    + {$planBonus}
                ) AS ranking_score")
                ->orderByDesc('ranking_score')
                ->take(30);

            if (!empty($areaIds)) {
                $q->whereIn('shops.area_id', $areaIds);
            } elseif ($prefId) {
                $q->where('shops.prefecture_id', $prefId);
            }

            return $q->pluck('casts.id')->toArray();
        });
    }

    private function loadCasts(array $ids): \Illuminate\Support\Collection
    {
        if (empty($ids)) return collect();
        return Cast::with(['shop', 'castType'])
            ->whereIn('id', $ids)
            ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
            ->get();
    }

    private function resolveArea(string $area_slug): array
    {
        $areaId = Cache::remember("slug:area_id:{$area_slug}", 86400,
            fn() => Area::where('slug', $area_slug)->value('id')
        );
        $areaModel = $areaId ? Area::with('prefecture')->find($areaId) : null;

        $prefId = (!$areaModel)
            ? Cache::remember("slug:pref_id:{$area_slug}", 86400,
                fn() => Prefecture::where('slug', $area_slug)->value('id'))
            : null;
        $prefModel = $prefId ? Prefecture::find($prefId) : null;

        return [$areaModel, $prefModel];
    }

    private function isCrawler(Request $request): bool
    {
        $ua = strtolower($request->userAgent() ?? '');
        if ($ua === '') return true;

        foreach (['bot','crawl','spider','slurp','mediapartners','facebookexternalhit',
                  'twitterbot','linkedinbot','whatsapp','applebot','pinterest',
                  'semrush','ahrefsbot','mj12bot','dotbot','bingpreview',
                  'yandex','baiduspider','duckduckbot'] as $p) {
            if (str_contains($ua, $p)) return true;
        }
        return false;
    }
}
