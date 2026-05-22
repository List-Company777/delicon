<?php

namespace App\Observers;

use App\Models\Shop;
use App\Services\IndexNowService;

class ShopObserver
{
    public function updated(Shop $shop): void
    {
        if ($shop->wasChanged('status') && $shop->status === 'active') {
            IndexNowService::ping(route('shop.show', $shop->id));
        }
    }
}
