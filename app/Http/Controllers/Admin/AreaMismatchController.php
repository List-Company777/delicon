<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Prefecture;
use App\Models\Shop;
use Illuminate\Http\Request;

class AreaMismatchController extends Controller
{
    public function index()
    {
        // ① 店名ベースのエリア不一致
        // 条件: 店名に何らかのエリア名が含まれているが、割り当てエリア名は店名に含まれていない
        $areas = Area::with('prefecture')->orderByRaw('LENGTH(name) DESC')->get();

        $shops = Shop::where('status', '!=', 'inactive')
            ->where('area_name_ok', 0)
            ->with(['area.prefecture', 'prefecture'])
            ->orderBy('name')
            ->get();

        $areaMismatches = collect();
        foreach ($shops as $shop) {
            $assignedAreaName = $shop->area ? $shop->area->name : null;

            // 割り当てエリア名が店名に含まれているならスキップ
            if ($assignedAreaName && mb_strpos($shop->name, $assignedAreaName) !== false) {
                continue;
            }

            // 店名に含まれる最初のエリア名を探す
            foreach ($areas as $area) {
                if (mb_strpos($shop->name, $area->name) !== false) {
                    if ($shop->area_id !== $area->id) {
                        $areaMismatches->push([
                            'shop'           => $shop,
                            'suggested_area' => $area,
                        ]);
                    }
                    break;
                }
            }
        }

        // ② 住所ベースの都道府県不一致
        $prefectures = Prefecture::all();

        $activeShops = Shop::where('status', '!=', 'inactive')
            ->where('area_name_ok', 0)
            ->whereNotNull('address')
            ->where('address', '!=', '')
            ->with(['area', 'prefecture'])
            ->get();

        $prefMismatches = collect();
        foreach ($activeShops as $shop) {
            foreach ($prefectures as $pref) {
                if (mb_strpos($shop->address, $pref->prefecture) === 0) {
                    if ($shop->prefecture_id !== $pref->id) {
                        $prefMismatches->push([
                            'shop'             => $shop,
                            'suggested_pref'   => $pref,
                        ]);
                    }
                    break;
                }
            }
        }

        return view('admin.area-mismatch.index', [
            'areaMismatches' => $areaMismatches,
            'prefMismatches' => $prefMismatches,
        ]);
    }

    public function apply(Shop $shop, Request $request)
    {
        $area = Area::findOrFail((int) $request->input('area_id'));
        $shop->update([
            'area_id'       => $area->id,
            'prefecture_id' => $area->prefecture_id,
        ]);
        return back()->with('success', "「{$shop->name}」のエリアを「{$area->name}」に変更しました。");
    }

    public function applyPref(Shop $shop, Request $request)
    {
        $pref = Prefecture::findOrFail((int) $request->input('prefecture_id'));
        $shop->update([
            'prefecture_id' => $pref->id,
            'area_id'       => null,
        ]);
        return back()->with('success', "「{$shop->name}」の都道府県を「{$pref->prefecture}」に変更しました（エリアはリセット）。");
    }

    public function dismiss(Shop $shop)
    {
        $shop->update(['area_name_ok' => 1]);
        return back()->with('success', "「{$shop->name}」を無視リストに登録しました。");
    }
}
