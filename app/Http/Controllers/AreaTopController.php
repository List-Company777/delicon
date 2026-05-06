<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Cast;
use App\Models\Prefecture;
use App\Models\Shop;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AreaTopController extends Controller
{
    public function show(string $area_slug)
    {
        [$areaModel, $prefModel] = $this->resolveArea($area_slug);

        if (!$areaModel && !$prefModel && $area_slug !== 'all') {
            abort(404);
        }

        $areaName = $areaModel?->name ?? $prefModel?->name ?? '全国';

        // 有料店舗（plan 1-3）バナーあり
        $featuredShops = Shop::with(['shopType', 'area'])
            ->where('status', 'active')
            ->where('plan', '<=', 3)
            ->where(fn($q) => $q->whereNotNull('main_image')->orWhereNotNull('shop_file_name'))
            ->when($areaModel, fn($q) => $q->whereHas('area', fn($a) =>
                $a->where('slug', $area_slug)
                  ->orWhereHas('parent', fn($p) => $p->where('slug', $area_slug))
                  ->orWhereHas('prefecture', fn($p) => $p->where('slug', $area_slug))
            ))
            ->when($prefModel && !$areaModel, fn($q) => $q->whereHas('area.prefecture', fn($p) => $p->where('slug', $area_slug)))
            ->orderByRaw('plan ASC, rank_score DESC, display_sort ASC')
            ->limit(12)
            ->get();

        // ジャンル別件数
        $shopTypeCounts = Cache::remember("area_top:shop_types:{$area_slug}", 1800, function () use ($area_slug, $areaModel, $prefModel) {
            $query = DB::table('shops')
                ->join('shop_types', 'shop_types.id', '=', 'shops.shop_type_id')
                ->where('shops.status', 'active')
                ->whereNotNull('shops.shop_type_id');

            if ($areaModel) {
                $areaIds = DB::table('areas')
                    ->where(fn($q) => $q->where('slug', $area_slug)
                        ->orWhere('parent_id', $areaModel->id))
                    ->pluck('id');
                $query->whereIn('shops.area_id', $areaIds);
            } elseif ($prefModel) {
                $areaIds = DB::table('areas')
                    ->where('prefecture_id', $prefModel->id)
                    ->pluck('id');
                $query->whereIn('shops.area_id', $areaIds);
            }

            return $query->selectRaw('shop_types.name, shop_types.slug, COUNT(shops.id) as cnt')
                ->groupBy('shop_types.id', 'shop_types.name', 'shop_types.slug')
                ->orderByDesc('cnt')
                ->get()
                ->map(fn($r) => ['name' => $r->name, 'slug' => $r->slug, 'cnt' => $r->cnt])
                ->all();
        });

        // 総掲載数
        $totalShops = array_sum(array_column($shopTypeCounts, 'cnt'));
        $shopTypeCounts = collect($shopTypeCounts)->map(fn($t) => (object)$t);

        $noindex = $totalShops === 0;
        $status  = $totalShops === 0 ? 404 : 200;

        return response()->view('area.top', compact(
            'area_slug', 'areaName', 'areaModel', 'prefModel',
            'featuredShops', 'shopTypeCounts', 'totalShops', 'noindex'
        ), $status);
    }

    private function resolveArea(string $area_slug): array
    {
        if ($area_slug === 'all') return [null, null];

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
}
