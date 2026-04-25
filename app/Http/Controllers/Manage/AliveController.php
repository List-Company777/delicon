<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Shop;

class AliveController extends Controller
{
    public function confirm(string $token)
    {
        $shop = Shop::where('alive_check_token', $token)
            ->where('status', 'active')
            ->firstOrFail();

        $shop->update([
            'alive_confirmed_at' => now(),
            'alive_check_token'  => null, // 使い捨て
        ]);

        return view('manage.alive-confirmed', compact('shop'));
    }
}
