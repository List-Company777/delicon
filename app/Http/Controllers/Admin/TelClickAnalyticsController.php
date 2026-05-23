<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TelClickAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = in_array($request->integer('period', 7), [7, 30, 90]) ? $request->integer('period', 7) : 7;
        $since  = now()->subDays($period)->startOfDay();

        $byPref = DB::table('cast_tel_clicks as tc')
            ->join('casts', 'casts.id', '=', 'tc.cast_id')
            ->join('shops', 'shops.id', '=', 'casts.shop_id')
            ->join('prefectures', 'prefectures.id', '=', 'shops.prefecture_id')
            ->where('tc.created_at', '>=', $since)
            ->select('prefectures.prefecture as name', DB::raw('COUNT(*) as cnt'))
            ->groupBy('prefectures.id', 'prefectures.prefecture')
            ->orderByDesc('cnt')
            ->get();

        $byType = DB::table('cast_tel_clicks as tc')
            ->join('casts', 'casts.id', '=', 'tc.cast_id')
            ->join('cast_types', 'cast_types.id', '=', 'casts.type_id')
            ->where('tc.created_at', '>=', $since)
            ->whereNotNull('casts.type_id')
            ->select('cast_types.name', DB::raw('COUNT(*) as cnt'))
            ->groupBy('cast_types.id', 'cast_types.name')
            ->orderByDesc('cnt')
            ->get();

        $byAge = DB::table('cast_tel_clicks as tc')
            ->join('casts', 'casts.id', '=', 'tc.cast_id')
            ->where('tc.created_at', '>=', $since)
            ->whereNotNull('casts.age')
            ->selectRaw("CASE WHEN casts.age < 20 THEN '10代' WHEN casts.age BETWEEN 20 AND 24 THEN '20代前半' WHEN casts.age BETWEEN 25 AND 29 THEN '20代後半' WHEN casts.age BETWEEN 30 AND 34 THEN '30代前半' WHEN casts.age BETWEEN 35 AND 39 THEN '30代後半' ELSE '40代以上' END as age_group, MIN(casts.age) as age_min, COUNT(*) as cnt")
            ->groupByRaw("CASE WHEN casts.age < 20 THEN '10代' WHEN casts.age BETWEEN 20 AND 24 THEN '20代前半' WHEN casts.age BETWEEN 25 AND 29 THEN '20代後半' WHEN casts.age BETWEEN 30 AND 34 THEN '30代前半' WHEN casts.age BETWEEN 35 AND 39 THEN '30代後半' ELSE '40代以上' END")
            ->orderBy('age_min')
            ->get();

        $byCup = DB::table('cast_tel_clicks as tc')
            ->join('casts', 'casts.id', '=', 'tc.cast_id')
            ->where('tc.created_at', '>=', $since)
            ->whereNotNull('casts.cup')
            ->where('casts.cup', '!=', '')
            ->select('casts.cup as name', DB::raw('COUNT(*) as cnt'))
            ->groupBy('casts.cup')
            ->orderByDesc('cnt')
            ->get();

        return view('admin.tel-click-analytics.index', compact('byPref', 'byType', 'byAge', 'byCup', 'period'));
    }
}
