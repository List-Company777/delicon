<?php
namespace App\Http\Controllers;

use App\Models\Cast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RankingController extends Controller
{
    public function index()
    {
        $rankingIds = Cache::remember('delicon:ranking', 3600, function () {
            $since = now()->subDays(7);

            $planBonus = "
                CASE WHEN shops.plan = 1 THEN 50
                     WHEN shops.plan = 2 THEN 30
                     WHEN shops.plan = 3 THEN 15
                     ELSE 0 END
            ";

            return Cast::query()
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
                ->take(30)
                ->pluck('casts.id')
                ->toArray();
        });

        $ranking = empty($rankingIds)
            ? collect()
            : Cast::with(['shop', 'castType'])
                ->whereIn('id', $rankingIds)
                ->orderByRaw('FIELD(id, ' . implode(',', $rankingIds) . ')')
                ->get();

        return view('ranking.index', compact('ranking'));
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
