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
        // 業種一覧（キャッシュ不要・件数少）
        $shopTypesRaw = ShopType::orderBy('id')->get()
            ->map(fn($t) => ['id' => $t->id, 'name' => $t->name])
            ->all();
        $shopTypes = collect($shopTypesRaw)->map(fn($t) => (object) $t);

        // おすすめ店舗（ranking_count 降順・最大12件）
        $recommendedShopsRaw = Cache::remember('delicon:top_recommended_shops', 1800, function () {
            return Shop::with(['shopType', 'castMembers'])
                ->where('status', 'active')
                ->orderByDesc('ranking_count')
                ->take(12)
                ->get()
                ->map(fn($shop) => [
                    'id'             => $shop->id,
                    'name'           => $shop->name,
                    'catche'         => $shop->catche,
                    'shop_file_name' => $shop->shop_file_name,
                    'shop_type_name' => $shop->shopType?->name,
                    'cast_count'     => $shop->castMembers->count(),
                    'price_60'       => $shop->price_60,
                ])
                ->all();
        });
        $recommendedShops = collect($recommendedShopsRaw)->map(fn($s) => (object) $s);

        // 新着キャスト（id 降順・最大12件）
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
                    'img_file_name'  => $cast->img_file_name,
                    'cast_type_name' => $cast->castType?->name,
                    'shop_id'        => $cast->shop_id,
                    'shop_name'      => $cast->shop?->name,
                ])
                ->all();
        });
        $newCasts = collect($newCastsRaw)->map(fn($c) => (object) $c);

        // 人気キーワード：業種別キャスト数 TOP8
        $popularKeywordsRaw = Cache::remember('delicon:top_shop_types_count', 3600, function () {
            return ShopType::withCount(['shops' => fn($q) => $q->where('status', 'active')])
                ->orderByDesc('shops_count')
                ->take(8)
                ->get()
                ->map(fn($t) => ['name' => $t->name, 'count' => $t->shops_count])
                ->all();
        });
        $popularKeywords = collect($popularKeywordsRaw)->map(fn($k) => (object) $k);

        return view('top.index', compact(
            'shopTypes',
            'recommendedShops',
            'newCasts',
            'popularKeywords'
        ));
    }
}
