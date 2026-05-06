<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Prefecture;
use App\Models\Shop;
use App\Models\ShopType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $shopTypesRaw = Cache::remember('delicon:shop_types', 3600, fn() =>
            ShopType::orderBy('id')->get()->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->all()
        );
        $shopTypes = collect($shopTypesRaw)->map(fn($t) => (object) $t);

        // エリア一覧（有効な店舗が存在するエリアのみ、キャッシュ済み）
        $areasRaw = Cache::remember('delicon:active_areas_v3', 3600, fn() =>
            Area::with('prefecture:id,slug,parent_slug')
                ->whereHas('shops', fn($q) => $q->where('status', 'active'))
                ->orderBy('sort_order')->orderBy('name')
                ->get(['id','name','slug','prefecture_id'])
                ->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'slug' => $a->slug, 'pref_slug' => $a->prefecture?->parent_slug])
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

    /**
     * /shops/{pref}/ — 都道府県レベルLP（東京・大阪etc.）
     * 配下のエリア一覧リンクも表示
     */
    public function byPref(string $pref)
    {
        $prefIds = Prefecture::where('parent_slug', $pref)->pluck('id');
        if ($prefIds->isEmpty()) abort(404);

        $prefName = $this->parentSlugToName($pref);

        $shops = Shop::where('status', 'active')
            ->whereIn('prefecture_id', $prefIds)
            ->with(['shopType'])
            ->withCount('castMembers')
            ->orderByDesc('ranking_count')->orderBy('name')
            ->paginate(30);

        // 配下エリア（店舗が存在するもの）
        $areas = Area::whereIn('prefecture_id', $prefIds)
            ->whereHas('shops', fn($q) => $q->where('status', 'active'))
            ->orderBy('sort_order')->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $title       = $prefName . 'のデリヘル店舗一覧';
        $description = $prefName . 'のデリヘル・風俗店を一覧掲載。エリア別・システム・料金・在籍キャストを詳しく紹介。';
        $breadcrumbs = [
            ['name' => 'ホーム',           'url' => route('top') . '/'],
            ['name' => '店舗一覧',         'url' => route('shop.index') . '/'],
            ['name' => $prefName . 'の店舗'],
        ];
        $parentPref = $pref;
        return view('shop.region', compact('shops', 'title', 'description', 'breadcrumbs', 'areas', 'parentPref'));
    }

    /**
     * /shops/{pref}/{area}/ — エリアレベルLP（新宿・渋谷etc.）
     */
    public function byPrefArea(string $pref, string $areaSlug)
    {
        $area = Area::where('slug', $areaSlug)
            ->whereHas('prefecture', fn($q) => $q->where('parent_slug', $pref))
            ->with('prefecture')
            ->firstOrFail();

        $prefName = $this->parentSlugToName($pref);

        $shops = Shop::where('status', 'active')
            ->where('area_id', $area->id)
            ->with(['shopType'])
            ->withCount('castMembers')
            ->orderByDesc('ranking_count')->orderBy('name')
            ->paginate(30);

        $areas = collect(); // エリアページでは子リンク不要
        $title       = $area->name . 'のデリヘル店舗一覧';
        $description = $prefName . '・' . $area->name . 'のデリヘル・風俗店を一覧掲載。システム・料金・在籍キャストを詳しく紹介。';
        $breadcrumbs = [
            ['name' => 'ホーム',               'url' => route('top') . '/'],
            ['name' => '店舗一覧',             'url' => route('shop.index') . '/'],
            ['name' => $prefName . 'の店舗',   'url' => route('shop.pref', $pref) . '/'],
            ['name' => $area->name . 'の店舗'],
        ];
        $parentPref = $pref;
        $slug = $areaSlug;
        return view('shop.region', compact('shops', 'title', 'description', 'breadcrumbs', 'areas', 'parentPref', 'slug'));
    }

    private function parentSlugToName(string $slug): string
    {
        return match($slug) {
            'tokyo'     => '東京',
            'kanagawa'  => '神奈川',
            'saitama'   => '埼玉',
            'chiba'     => '千葉',
            'ibaraki'   => '茨城',
            'tochigi'   => '栃木',
            'gunma'     => '群馬',
            'osaka'     => '大阪',
            'kyoto'     => '京都',
            'nara'      => '奈良',
            'shiga'     => '滋賀',
            'hyogo'     => '兵庫',
            'wakayama'  => '和歌山',
            'aichi'     => '愛知',
            'gifu'      => '岐阜',
            'shizuoka'  => '静岡',
            'mie'       => '三重',
            default     => $slug,
        };
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

        $news = $shop->news()->orderByDesc("is_pinned")->latest()->take(3)->get();

        $footerPrefSlug = null;
        if ($shop->area_id) {
            $footerPrefSlug = Cache::remember("slug:pref_by_area:{$shop->area_id}", 86400,
                fn() => DB::table('prefectures')
                    ->join('areas', 'areas.prefecture_id', '=', 'prefectures.id')
                    ->where('areas.id', $shop->area_id)
                    ->value('prefectures.slug')
            );
        }

        return view('shop.show', compact('shop', 'casts', 'news', 'footerPrefSlug'));
    }
}
