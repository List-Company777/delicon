<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopType;
use App\Models\Cast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $shopTypes = Cache::remember('shop_types_all', 3600, fn() =>
            ShopType::orderBy('id')->get()
        );

        $query = Shop::where('status', 'active')
            ->with(['shopType'])
            ->withCount('castMembers');

        if ($request->filled('type')) {
            $query->where('shop_type_id', $request->type);
        }
        if ($request->filled('q')) {
            $q = '%' . $request->q . '%';
            $query->where(fn($q2) =>
                $q2->where('name', 'like', $q)->orWhere('address', 'like', $q)
            );
        }

        $shops = $query->orderByDesc('ranking_count')
            ->orderBy('name')
            ->paginate(30);

        return view('shop.index', compact('shops', 'shopTypes'));
    }

    public function show(Shop $shop)
    {
        if ($shop->status !== 'active') {
            abort(404);
        }

        $casts = $shop->castMembers()
            ->with(['castType', 'bodyType', 'tags'])
            ->orderByDesc('is_recommended')
            ->orderBy('sort_order')
            ->paginate(24);

        $news = $shop->news()->take(10)->get();

        return view('shop.show', compact('shop', 'casts', 'news'));
    }
}
