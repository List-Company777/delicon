<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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

        // 無料店舗のみ: 同業種・同エリアの有料店舗を最大4件表示
        $relatedShops = new Collection();
        if (! $shop->hasBudget() && $shop->genre_id && $shop->area_id) {
            $relatedShops = Shop::with(['genre', 'area'])
                ->where('id', '!=', $shop->id)
                ->where('status', 'active')
                ->where('genre_id', $shop->genre_id)
                ->where('area_id', $shop->area_id)
                ->whereHas('detail', fn($q) => $q->where('status', 'active'))
                ->orderByRaw('budget_balance >= bid_price DESC')
                ->orderByDesc('bid_price')
                ->limit(8)
                ->get(['id', 'name', 'main_image', 'genre_id', 'area_id', 'budget_balance', 'bid_price'])
                ->filter(fn($s) => $s->hasBudget())
                ->take(4);
        }

        return view('shop.show', compact('shop', 'relatedShops'));
    }
}
