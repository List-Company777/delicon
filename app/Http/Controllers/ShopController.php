<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Prefecture;
use App\Models\Shop;
use App\Models\ShopType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $shopTypesRaw = Cache::remember('delicon:shop_types', 3600, fn() =>
            ShopType::orderBy('id')->get()->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->all()
        );
        $shopTypes = collect($shopTypesRaw)->map(fn($t) => (object) $t);

        // エリア一覧（有効な店舗が存在するエリアのみ、キャッシュ済み）
        $areasRaw = Cache::remember('delicon:active_areas', 3600, fn() =>
            Area::whereHas('shops', fn($q) => $q->where('status', 'active'))
                ->orderBy('sort_order')->orderBy('name')
                ->get(['id','name','slug'])
                ->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'slug' => $a->slug])
                ->all()
        );
        $areas = collect($areasRaw)->map(fn($a) => (object) $a);

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
        if ($request->filled('area_id')) {
            $query->where('area_id', (int) $request->area_id);
        }
        if ($request->filled('prefecture_id')) {
            $query->where('prefecture_id', (int) $request->prefecture_id);
        }

        $shops = $query->orderByDesc('ranking_count')
            ->orderBy('name')
            ->paginate(30);

        return view('shop.index', compact('shops', 'shopTypes', 'areas'));
    }

    public function byRegion(string $slug)
    {
        // area.slug で検索（より詳細）
        $area = Area::where('slug', $slug)->first();
        if ($area) {
            $shops = Shop::where('status', 'active')
                ->where('area_id', $area->id)
                ->with(['shopType'])
                ->withCount('castMembers')
                ->orderByDesc('ranking_count')->orderBy('name')
                ->paginate(30);

            $title       = $area->name . 'のデリヘル店舗一覧';
            $description = $area->name . 'のデリヘル・風俗店を一覧掲載。システム・料金・在籍キャストを詳しく紹介。';
            $breadcrumbs = [
                ['name' => 'ホーム',   'url' => route('top') . '/'],
                ['name' => '店舗一覧', 'url' => route('shop.index') . '/'],
                ['name' => $area->name . 'の店舗'],
            ];
            return view('shop.region', compact('shops', 'title', 'description', 'breadcrumbs', 'slug'));
        }

        // prefecture.slug で検索
        $prefecture = Prefecture::where('slug', $slug)->first();
        if ($prefecture) {
            $shops = Shop::where('status', 'active')
                ->where('prefecture_id', $prefecture->id)
                ->with(['shopType'])
                ->withCount('castMembers')
                ->orderByDesc('ranking_count')->orderBy('name')
                ->paginate(30);

            $title       = $prefecture->name . 'のデリヘル店舗一覧';
            $description = $prefecture->name . 'のデリヘル・風俗店を一覧掲載。システム・料金・在籍キャストを詳しく紹介。';
            $breadcrumbs = [
                ['name' => 'ホーム',   'url' => route('top') . '/'],
                ['name' => '店舗一覧', 'url' => route('shop.index') . '/'],
                ['name' => $prefecture->name . 'の店舗'],
            ];
            return view('shop.region', compact('shops', 'title', 'description', 'breadcrumbs', 'slug'));
        }

        abort(404);
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

        $news = $shop->news()->latest()->take(3)->get();

        return view('shop.show', compact('shop', 'casts', 'news'));
    }
}
