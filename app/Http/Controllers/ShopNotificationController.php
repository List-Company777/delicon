<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopNotification;

class ShopNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function toggle(Shop $shop)
    {
        $user  = auth()->user();
        $exists = ShopNotification::where('user_id', $user->id)->where('shop_id', $shop->id)->first();

        if ($exists) {
            $exists->delete();
            $subscribed = false;
        } else {
            ShopNotification::create(['user_id' => $user->id, 'shop_id' => $shop->id]);
            $subscribed = true;
        }

        return response()->json(['subscribed' => $subscribed]);
    }
}
