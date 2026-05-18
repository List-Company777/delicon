<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Cast;
use App\Models\CastDiary;
use App\Models\Prefecture;
use App\Models\Shop;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AreaTopController extends Controller
{
    public function show(string $area_slug)
    {
        [$areaModel, $prefModel] = $this->resolveArea($area_slug);

        if (!$areaModel && !$prefModel && $area_slug !== 'all') {
            abort(404);
        }

        if ($areaModel && !$prefModel) {
            return redirect("/{$area_slug}/shop-list/", 301);
        }

        $areaName = $areaModel?->name ?? $prefModel?->name ?? '全国';

        // エリアIDリスト（全クエリで共用）
        $areaIds = Cache::remember("area_top:area_ids:{$area_slug}", 3600, function () use ($area_slug, $areaModel, $prefModel) {
            if ($areaModel) {
                return DB::table('areas')
                    ->where(fn($q) => $q->where('slug', $area_slug)->orWhere('parent_id', $areaModel->id))
                    ->pluck('id')->all();
            }
            if ($prefModel) {
                return DB::table('areas')->where('prefecture_id', $prefModel->id)->pluck('id')->all();
            }
            return [];
        });

        // ① ピックアップ（plan4=ミドル以上、main_image あり）
        $pickupShopsRaw = Cache::remember("area_top:pickup:{$area_slug}", 900, function () use ($areaIds) {
            $q = Shop::with(['shopType', 'castMembers'])
                ->where('status', 'active')
                ->where('plan', '<=', 2)
                ->whereNotNull('main_image');
            if (!empty($areaIds)) {
                $q->whereIn('area_id', $areaIds);
            }
            return $q->orderByRaw('RAND()')
                ->limit(3)
                ->get()
                ->map(fn($shop) => [
                    'id'             => $shop->id,
                    'name'           => $shop->name,
                    'catche'         => $shop->catche,
                    'main_image_url' => Storage::url($shop->main_image),
                    'shop_type_name' => $shop->shopType?->name,
                    'cast_count'     => $shop->castMembers->count(),
                    'price_60'       => $shop->price_60,
                ])
                ->all();
        });
        $pickupShops = collect($pickupShopsRaw)->map(fn($s) => (object) $s);

        // ② 有料店舗グリッド（plan3=基本有料以上）
        $featuredShopsRaw = Cache::remember("area_top:featured:{$area_slug}", 600, function () use ($areaIds) {
            $q = Shop::with(['shopType'])
                ->where('status', 'active')
                ->where('plan', '<=', 4)
                ->where(fn($q) => $q->whereNotNull('main_image')->orWhereNotNull('shop_file_name'));
            if (!empty($areaIds)) {
                $q->whereIn('area_id', $areaIds);
            }
            return $q->orderByRaw('plan ASC, rank_score DESC, display_sort ASC')
                ->limit(12)
                ->get()
                ->map(fn($shop) => [
                    'id'             => $shop->id,
                    'name'           => $shop->name,
                    'main_image'     => $shop->main_image,
                    'price_60'       => $shop->price_60,
                    'shop_type_name' => $shop->shopType?->name,
                ])
                ->all();
        });
        $featuredShops = collect($featuredShopsRaw)->map(fn($s) => (object) $s);

        // ③ 本日出勤中のキャスト（ランダム）
        $workingCastsRaw = Cache::remember("area_top:working:{$area_slug}:" . now()->format('Y-m-d-H'), 300, function () use ($areaIds) {
            $q = Cast::with(['shop', 'castType'])
                ->where('status', 'active')
                ->whereHas('schedules', function ($sq) {
                    $sq->where(function ($q) {
                        // 今日のシフト（開始済みかつ終了前）
                        $q->whereDate('work_date', today())
                          ->where(fn($q2) => $q2->whereNull('start_time')->orWhereRaw('start_time <= CURTIME()'))
                          ->where(fn($q2) => $q2->whereNull('end_time')->orWhereRaw('end_time > CURTIME()'));
                    })->orWhere(function ($q) {
                        // 昨日のシフトで深夜帯に続くもの（25:00〜 表記対応）
                        $q->whereDate('work_date', today()->subDay())
                          ->whereRaw('end_time > ADDTIME(CURTIME(), SEC_TO_TIME(86400))');
                    });
                });
            if (!empty($areaIds)) {
                $q->whereHas('shop', fn($sq) => $sq->whereIn('area_id', $areaIds));
            }
            return $q->inRandomOrder()
                ->limit(6)
                ->get()
                ->map(fn($cast) => [
                    'id'             => $cast->id,
                    'name'           => $cast->name,
                    'age'            => $cast->age,
                    'cup'            => $cast->cup,
                    'img_url'        => ($cast->img_file_name && !str_starts_with($cast->img_file_name, '/img/common/'))
                        ? $cast->img_file_name . 'big.jpg' : '/img/no-cast.svg',
                    'img_webp_url'   => ($cast->img_file_name && !str_starts_with($cast->img_file_name, '/img/common/'))
                        ? $cast->img_file_name . 'big.jpg.webp' : null,
                    'cast_type_name' => $cast->castType?->name,
                    'shop_id'        => $cast->shop_id,
                    'shop_name'      => $cast->shop?->name,
                ])
                ->all();
        });
        $workingCasts = collect($workingCastsRaw)->map(fn($c) => (object) $c);

        // ④ 新着日記
        $recentDiariesRaw = Cache::remember("area_top:diaries:{$area_slug}", 600, function () use ($areaIds) {
            $q = CastDiary::with(['cast.shop', 'images' => fn($q) => $q->orderBy('sort_order')->limit(1)])
                ->where('status', 'published');
            if (!empty($areaIds)) {
                $q->whereHas('cast.shop', fn($sq) => $sq->whereIn('area_id', $areaIds));
            }
            return $q->orderByDesc('id')
                ->limit(6)
                ->get()
                ->map(fn($diary) => [
                    'id'         => $diary->id,
                    'title'      => $diary->title,
                    'body'       => mb_substr(strip_tags($diary->body ?? ''), 0, 60),
                    'img_url'    => $diary->images->first()?->img_path
                        ? Storage::url($diary->images->first()->img_path) : null,
                    'cast_id'    => $diary->cast_id,
                    'cast_name'  => $diary->cast?->name,
                    'shop_name'  => $diary->cast?->shop?->name,
                    'created_at' => $diary->created_at?->format('m/d'),
                ])
                ->all();
        });
        $recentDiaries = collect($recentDiariesRaw)->map(fn($d) => (object) $d);

        // ⑤ 新着キャスト（入店1ヶ月以内）
        $recentCastsRaw = Cache::remember("area_top:new_casts:{$area_slug}", 900, function () use ($areaIds) {
            $q = Cast::with(['shop', 'castType'])
                ->where('status', 'active')
                ->where('is_new', true)
                ->whereNotNull('new_since')
                ->whereRaw('new_since <= CURDATE()')
                ->whereRaw('DATE_ADD(new_since, INTERVAL 1 MONTH) > CURDATE()');
            if (!empty($areaIds)) {
                $q->whereHas('shop', fn($sq) => $sq->whereIn('area_id', $areaIds));
            }
            return $q->orderByDesc('new_since')
                ->limit(6)
                ->get()
                ->map(fn($cast) => [
                    'id'             => $cast->id,
                    'name'           => $cast->name,
                    'age'            => $cast->age,
                    'cup'            => $cast->cup,
                    'img_url'        => ($cast->img_file_name && !str_starts_with($cast->img_file_name, '/img/common/'))
                        ? $cast->img_file_name . 'big.jpg' : '/img/no-cast.svg',
                    'img_webp_url'   => ($cast->img_file_name && !str_starts_with($cast->img_file_name, '/img/common/'))
                        ? $cast->img_file_name . 'big.jpg.webp' : null,
                    'cast_type_name' => $cast->castType?->name,
                    'shop_id'        => $cast->shop_id,
                    'shop_name'      => $cast->shop?->name,
                    'join_date'      => $cast->join_date?->format('m/d入店'),
                ])
                ->all();
        });
        $recentCasts = collect($recentCastsRaw)->map(fn($c) => (object) $c);
        // 入店予定（new_since が未来日）
        $comingSoonCastsRaw = Cache::remember("area_top:coming_soon:{$area_slug}", 900, function () use ($areaIds) {
            $q = Cast::with(['shop', 'castType'])
                ->where('status', 'active')
                ->where('is_new', true)
                ->whereNotNull('new_since')
                ->whereRaw('new_since > CURDATE()');
            if (!empty($areaIds)) {
                $q->whereHas('shop', fn($sq) => $sq->whereIn('area_id', $areaIds));
            }
            return $q->orderBy('new_since')
                ->limit(6)
                ->get()
                ->map(fn($cast) => [
                    'id'             => $cast->id,
                    'name'           => $cast->name,
                    'age'            => $cast->age,
                    'img_url'        => ($cast->img_file_name && !str_starts_with($cast->img_file_name, '/img/common/'))
                        ? $cast->img_file_name . 'big.jpg' : '/img/no-cast.svg',
                    'img_webp_url'   => ($cast->img_file_name && !str_starts_with($cast->img_file_name, '/img/common/'))
                        ? $cast->img_file_name . 'big.jpg.webp' : null,
                    'cast_type_name' => $cast->castType?->name,
                    'shop_id'        => $cast->shop_id,
                    'shop_name'      => $cast->shop?->name,
                    'new_since'      => $cast->new_since?->format('m月d日入店予定'),
                ])
                ->all();
        });
        $comingSoonCasts = collect($comingSoonCastsRaw)->map(fn($c) => (object) $c);

        // ⑥ バナー広告（VIPコース=plan5、限定枠：東京・大阪10、その他5）
        $bannerLimit = in_array($area_slug, ['tokyo', 'osaka']) ? 10 : 5;
        $bannerShopsRaw = Cache::remember("area_top:banners:{$area_slug}", 1800, function () use ($areaIds, $bannerLimit) {
            $q = Shop::where('status', 'active')
                ->whereNotNull("shop_file_name");
            if (!empty($areaIds)) {
                $q->whereIn('area_id', $areaIds);
            }
            return $q->orderByRaw('rank_score DESC, display_sort ASC')
                ->limit($bannerLimit)
                ->get()
                ->map(fn($shop) => [
                    'id'         => $shop->id,
                    'name'       => $shop->name,
                    'banner_url'      => $shop->shop_file_name
                        . (!pathinfo($shop->shop_file_name, PATHINFO_EXTENSION) ? '.jpg' : ''),
                    'banner_webp_url' => $shop->shop_file_name
                        . (!pathinfo($shop->shop_file_name, PATHINFO_EXTENSION) ? '.jpg' : '')
                        . '.webp',
                ])
                ->all();
        });
        $bannerShops = collect($bannerShopsRaw)->map(fn($s) => (object) $s);

        // ⑦ 三行広告
        $sangyoShopsRaw = Cache::remember("area_top:sangyo_v2:{$area_slug}", 1800, function () use ($areaIds) {
            $q = Shop::where("status", "active")
                ->where("plan", "<=", 4)
                ->whereNotNull("sangyo_text1")
                ->where("sangyo_text1", "!=", "");
            if (!empty($areaIds)) {
                $q->whereIn("area_id", $areaIds);
            }
            return $q->orderByRaw("RAND()")
                ->limit(12)
                ->get()
                ->map(fn($shop) => [
                    "id"           => $shop->id,
                    "name"         => $shop->name,
                    "sangyo_text1" => $shop->sangyo_text1,
                    "sangyo_text2" => $shop->sangyo_text2,
                    "sangyo_text3" => $shop->sangyo_text3,
                ])
                ->all();
        });
        $sangyoShops = collect($sangyoShopsRaw)->map(fn($s) => (object) $s);


        // ジャンル別件数
        $shopTypeCounts = Cache::remember("area_top:shop_types:{$area_slug}", 1800, function () use ($area_slug, $areaModel, $prefModel) {
            $query = DB::table('shops')
                ->join('shop_types', 'shop_types.id', '=', 'shops.shop_type_id')
                ->where('shops.status', 'active')
                ->whereNotNull('shops.shop_type_id');

            if ($areaModel) {
                $ids = DB::table('areas')
                    ->where(fn($q) => $q->where('slug', $area_slug)->orWhere('parent_id', $areaModel->id))
                    ->pluck('id');
                $query->whereIn('shops.area_id', $ids);
            } elseif ($prefModel) {
                $ids = DB::table('areas')->where('prefecture_id', $prefModel->id)->pluck('id');
                $query->whereIn('shops.area_id', $ids);
            }

            return $query->selectRaw('shop_types.name, shop_types.slug, COUNT(shops.id) as cnt')
                ->groupBy('shop_types.id', 'shop_types.name', 'shop_types.slug')
                ->orderByDesc('cnt')
                ->get()
                ->map(fn($r) => ['name' => $r->name, 'slug' => $r->slug, 'cnt' => $r->cnt])
                ->all();
        });

        $totalShops = array_sum(array_column($shopTypeCounts, 'cnt'));
        $shopTypeCounts = collect($shopTypeCounts)->map(fn($t) => (object) $t);

        $noindex = $totalShops === 0;
        $status  = $totalShops === 0 ? 404 : 200;

        // 小エリア（都道府県ページのみ）
        $subAreas = collect();
        if ($prefModel) {
            $subAreasRaw = Cache::remember("pref:sub_areas_shops:{$area_slug}", 1800, function () use ($prefModel) {
                $areaIds = DB::table('areas')->where('prefecture_id', $prefModel->id)->pluck('id');
                $counts  = DB::table('shops')
                    ->where('status', 'active')->whereIn('area_id', $areaIds)
                    ->groupBy('area_id')->selectRaw('area_id, COUNT(*) as cnt')
                    ->pluck('cnt', 'area_id');
                return DB::table('areas')
                    ->where('prefecture_id', $prefModel->id)->whereNull('parent_id')
                    ->get(['id', 'name', 'slug'])
                    ->filter(fn($a) => ($counts[$a->id] ?? 0) > 0)
                    ->sortByDesc(fn($a) => $counts[$a->id] ?? 0)->values()
                    ->map(fn($a) => ['name' => $a->name, 'slug' => $a->slug, 'cnt' => $counts[$a->id] ?? 0])
                    ->all();
            });
            $subAreas = collect($subAreasRaw)->map(fn($a) => (object) $a);
        }

        return response()->view('area.top', compact(
            'area_slug', 'areaName', 'areaModel', 'prefModel',
            'pickupShops', 'featuredShops', 'workingCasts',
            'sangyoShops',
            'recentDiaries', 'recentCasts', 'comingSoonCasts', 'bannerShops',
            'shopTypeCounts', 'totalShops', 'noindex', 'subAreas'
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
