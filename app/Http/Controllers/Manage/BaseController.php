<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Shop;

class BaseController extends Controller
{
    protected function getShop(): ?Shop
    {
        $user = auth()->user();

        // 代理店ユーザーが代理操作中の場合：セッションの shop を使う
        if ($user->isPartner() && session()->has('acting_shop_id')) {
            $shopId = session('acting_shop_id');
            return Shop::with(['detail', 'genre', 'area', 'prefecture', 'jobs.jobType', 'setPrices'])
                ->where('id', $shopId)
                ->where('partner_id', $user->partner_id)
                ->first();
        }

        // 複数店舗対応：セッションで選択中の店舗を優先
        $managingId = session('managing_shop_id');
        if ($managingId) {
            $shop = $user->shops()
                ->with(['detail', 'genre', 'area', 'prefecture', 'jobs.jobType', 'setPrices'])
                ->wherePivot('role', 'owner')
                ->where('shops.id', $managingId)
                ->first();
            if ($shop) return $shop;
        }

        // セッションなし or 無効 → 最初の店舗でセッション初期化
        $shop = $user->shops()
            ->with(['detail', 'genre', 'area', 'prefecture', 'jobs.jobType', 'setPrices'])
            ->wherePivot('role', 'owner')
            ->first();
        if ($shop) session(['managing_shop_id' => $shop->id]);
        return $shop;
    }

    protected function shopOrFail(): Shop
    {
        $shop = $this->getShop();
        abort_if(! $shop, 404, '店舗が登録されていません');
        return $shop;
    }
}
