<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ShopController extends Controller
{
    public function show(Request $request, int $id)
    {
        $shop = Shop::with([
            'detail', 'genre', 'area', 'prefecture', 'partner',
            'pricePlans.setPrices', 'pricePlans.extensionPrices',
            'setPrices', 'otherCharges', 'externalUrls',
            'jobs' => function ($q) {
                $q->where('status', 'active')->with('jobType')->orderByDesc('is_hotlink');
            },
        ])
            ->where('status', 'active')
            ->findOrFail($id);

        if (! $shop->detail || $shop->detail->status !== 'active') {
            abort(404);
        }

        // 無料店舗のみ: 同業種・同エリアの有料/XML店舗を最大4件（不足時は同都道府県で補完）
        $relatedShops = new Collection();
        if (! $shop->hasBudget() && $shop->genre_id) {
            $relatedShops = Cache::remember("related_shops:{$shop->id}", 1800, function () use ($shop) {
                $cols = ['id', 'name', 'main_image', 'genre_id', 'area_id', 'prefecture_id', 'budget_balance', 'bid_price', 'xml_source'];
                $base = Shop::with(['genre', 'area'])
                    ->where('id', '!=', $shop->id)
                    ->where('status', 'active')
                    ->where('genre_id', $shop->genre_id)
                    ->whereHas('detail', fn($q) => $q->where('status', 'active'))
                    ->where(fn($q) => $q->whereRaw('budget_balance >= bid_price')->orWhereNotNull('xml_source'))
                    ->orderByRaw('budget_balance >= bid_price DESC')
                    ->orderByDesc('bid_price');

                $result = new Collection();
                if ($shop->area_id) {
                    $result = (clone $base)->where('area_id', $shop->area_id)->limit(4)->get($cols);
                }

                if ($result->count() < 4 && $shop->prefecture_id) {
                    $additional = (clone $base)
                        ->whereNotIn('id', $result->pluck('id')->push($shop->id))
                        ->where('prefecture_id', $shop->prefecture_id)
                        ->limit(4 - $result->count())
                        ->get($cols);
                    $result = $result->merge($additional);
                }

                return $result;
            });
        }

        return view('shop.show', compact('shop', 'relatedShops'));
    }
}
