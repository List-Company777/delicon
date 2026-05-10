<?php

namespace App\Http\Controllers\Manage;

use App\Models\Cast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CastAnalyticsController extends BaseController
{
    public function index(Request $request)
    {
        $shop   = $this->shopOrFail();
        $period = in_array($request->integer('period', 7), [7, 30]) ? $request->integer('period', 7) : 7;
        $sort   = in_array($request->input('sort'), ['tel', 'views', 'favorites', 'reviews', 'score']) ? $request->input('sort') : 'score';
        $since  = now()->subDays($period)->startOfDay();

        $casts = Cast::where('shop_id', $shop->id)
            ->select('id', 'name', 'image', 'status')
            ->get();

        if ($casts->isEmpty()) {
            return view('manage.cast_analytics.index', compact('casts', 'period', 'sort'));
        }

        $castIds = $casts->pluck('id');

        $telClicks = DB::table('cast_tel_clicks')
            ->whereIn('cast_id', $castIds)
            ->where('created_at', '>=', $since)
            ->select('cast_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('cast_id')
            ->pluck('cnt', 'cast_id');

        $views = DB::table('cast_views')
            ->whereIn('cast_id', $castIds)
            ->where('viewed_at', '>=', $since)
            ->select('cast_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('cast_id')
            ->pluck('cnt', 'cast_id');

        $favorites = DB::table('cast_favorites')
            ->whereIn('cast_id', $castIds)
            ->select('cast_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('cast_id')
            ->pluck('cnt', 'cast_id');

        $reviews = DB::table('cast_reviews')
            ->whereIn('cast_id', $castIds)
            ->where('is_approved', true)
            ->select('cast_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('cast_id')
            ->pluck('cnt', 'cast_id');

        $rows = $casts->map(function ($cast) use ($telClicks, $views, $favorites, $reviews) {
            $tel   = (int) ($telClicks[$cast->id] ?? 0);
            $view  = (int) ($views[$cast->id] ?? 0);
            $fav   = (int) ($favorites[$cast->id] ?? 0);
            $rev   = (int) ($reviews[$cast->id] ?? 0);
            $score = $tel * 10 + $fav * 3 + $rev * 5 + $view;
            return (object) [
                'cast'     => $cast,
                'tel'      => $tel,
                'views'    => $view,
                'favorites'=> $fav,
                'reviews'  => $rev,
                'score'    => $score,
            ];
        });

        $rows = match ($sort) {
            'tel'       => $rows->sortByDesc('tel'),
            'views'     => $rows->sortByDesc('views'),
            'favorites' => $rows->sortByDesc('favorites'),
            'reviews'   => $rows->sortByDesc('reviews'),
            default     => $rows->sortByDesc('score'),
        };

        $rows = $rows->values();

        return view('manage.cast_analytics.index', compact('rows', 'period', 'sort'));
    }
}
