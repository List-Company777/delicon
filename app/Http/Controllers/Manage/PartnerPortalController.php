<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class PartnerPortalController extends Controller
{
    /** 紹介店舗一覧 */
    public function index()
    {
        $partner = auth()->user()->partner;
        abort_if(! $partner, 403);

        $shops = Shop::where('partner_id', $partner->id)
            ->with(['genre', 'area', 'detail'])
            ->orderByDesc('bid_price')
            ->orderBy('name')
            ->get();

        return view('manage.partner.index', compact('partner', 'shops'));
    }

    /** 代理操作開始 */
    public function actAs(int $shopId)
    {
        $partner = auth()->user()->partner;
        abort_if(! $partner, 403);

        $shop = Shop::where('id', $shopId)
            ->where('partner_id', $partner->id)
            ->firstOrFail();

        session(['acting_shop_id' => $shop->id]);

        return redirect()->route('manage.dashboard')
            ->with('success', "「{$shop->name}」の代理操作を開始しました");
    }

    /** 代理操作終了 */
    public function stopActing()
    {
        session()->forget('acting_shop_id');
        return redirect()->route('manage.partner.index');
    }

}
