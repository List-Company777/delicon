<?php

namespace App\Http\Controllers;

use App\Models\Cast;
use App\Models\Shop;
use App\Models\ShopType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TopController extends Controller
{
    public function index()
    {
        $shopTypesRaw = ShopType::orderBy('id')->get()
            ->map(fn($t) => ['id' => $t->id, 'name' => $t->name])
            ->all();
        $shopTypes = collect($shopTypesRaw)->map(fn($t) => (object) $t);

        $recommendedShopsRaw = Cache::remember('delicon:top_recommended_shops', 1800, function () {
            return Shop::with(['shopType', 'castMembers'])
                ->where('status', 'active')
                ->orderByDesc('ranking_count')
                ->take(12)
                ->get()
                ->map(fn($shop) => [
                    'id'              => $shop->id,
                    'name'            => $shop->name,
                    'catche'          => $shop->catche,
                    'shop_file_name'  => $shop->shop_file_name,
                    'shop_banner_url' => $shop->shop_file_name
                        ? ($shop->shop_file_name . (!pathinfo($shop->shop_file_name, PATHINFO_EXTENSION) ? '.jpg' : ''))
                        : null,
                    'shop_type_name'  => $shop->shopType?->name,
                    'cast_count'      => $shop->castMembers->count(),
                    'price_60'        => $shop->price_60,
                ])
                ->all();
        });
        $recommendedShops = collect($recommendedShopsRaw)->map(fn($s) => (object) $s);

        $newCastsRaw = Cache::remember('delicon:top_new_casts', 900, function () {
            return Cast::with(['shop', 'castType'])
                ->where('status', 'active')
                ->orderByDesc('id')
                ->take(12)
                ->get()
                ->map(fn($cast) => [
                    'id'             => $cast->id,
                    'name'           => $cast->name,
                    'age'            => $cast->age,
                    'cup'            => $cast->cup,
                    'img_url'        => ($cast->img_file_name && !str_starts_with($cast->img_file_name, '/img/common/'))
                        ? $cast->img_file_name . 'big.jpg'
                        : '/img/no-cast.jpg',
                    'cast_type_name' => $cast->castType?->name,
                    'shop_id'        => $cast->shop_id,
                    'shop_name'      => $cast->shop?->name,
                ])
                ->all();
        });
        $newCasts = collect($newCastsRaw)->map(fn($c) => (object) $c);

        $popularKeywordsRaw = Cache::remember('delicon:top_shop_types_count', 3600, function () {
            return ShopType::withCount(['shops' => fn($q) => $q->where('status', 'active')])
                ->orderByDesc('shops_count')
                ->take(8)
                ->get()
                ->map(fn($t) => ['name' => $t->name, 'count' => $t->shops_count])
                ->all();
        });
        $popularKeywords = collect($popularKeywordsRaw)->map(fn($k) => (object) $k);

        $workingTodayRaw = Cache::remember('delicon:top_working_today_' . today()->toDateString(), 300, function () {
            return Cast::with(['shop', 'castType'])
                ->where('status', 'active')
                ->whereDate('working_date', today())
                ->orderByDesc('is_recommended')
                ->take(12)
                ->get()
                ->map(fn($cast) => [
                    'id'             => $cast->id,
                    'name'           => $cast->name,
                    'age'            => $cast->age,
                    'cup'            => $cast->cup,
                    'img_url'        => ($cast->img_file_name && !str_starts_with($cast->img_file_name, '/img/common/'))
                        ? $cast->img_file_name . 'big.jpg' : '/img/no-cast.jpg',
                    'cast_type_name' => $cast->castType?->name,
                    'shop_id'        => $cast->shop_id,
                    'shop_name'      => $cast->shop?->name,
                ])->all();
        });
        $workingToday = collect($workingTodayRaw)->map(fn($c) => (object) $c);

        $newArrivalRaw = Cache::remember('delicon:top_new_arrival', 1800, function () {
            return Cast::with(['shop', 'castType'])
                ->where('status', 'active')
                ->where('is_new', true)
                ->whereNotNull('new_since')
                ->whereRaw('DATE_ADD(new_since, INTERVAL 1 MONTH) > CURDATE()')
                ->orderByDesc('new_since')
                ->take(12)
                ->get()
                ->map(fn($cast) => [
                    'id'             => $cast->id,
                    'name'           => $cast->name,
                    'age'            => $cast->age,
                    'cup'            => $cast->cup,
                    'img_url'        => ($cast->img_file_name && !str_starts_with($cast->img_file_name, '/img/common/'))
                        ? $cast->img_file_name . 'big.jpg' : '/img/no-cast.jpg',
                    'cast_type_name' => $cast->castType?->name,
                    'shop_id'        => $cast->shop_id,
                    'shop_name'      => $cast->shop?->name,
                    'join_date'      => $cast->join_date?->format('m/d入店'),
                ])->all();
        });
        $newArrivals = collect($newArrivalRaw)->map(fn($c) => (object) $c);

        // ログイン中かつ好み設定ありの場合のみおすすめキャストを取得
        $recommendations = collect();
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->hasPreferences()) {
                $typeIds = $user->pref_cast_type_ids ?? [];
                $areaIds = $user->pref_area_ids ?? [];

                $query = Cast::with(['shop', 'castType'])
                    ->where('status', 'active')
                    ->whereHas('shop', fn($q) => $q->where('status', 'active'))
                    ->orderByDesc('is_recommended')
                    ->orderByDesc('id');

                if (!empty($typeIds)) {
                    $query->whereIn('type_id', $typeIds);
                }
                if (!empty($areaIds)) {
                    $query->whereHas('shop', fn($q) => $q->whereIn('area_id', $areaIds));
                }

                $recommendations = $query->take(12)->get();
            }
        }

        return view('top.index', compact(
            'shopTypes',
            'recommendedShops',
            'newCasts',
            'popularKeywords',
            'workingToday',
            'newArrivals',
            'recommendations'
        ));
    }
}
