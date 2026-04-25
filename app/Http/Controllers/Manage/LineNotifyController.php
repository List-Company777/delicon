<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class LineNotifyController extends Controller
{
    public function remove(): RedirectResponse
    {
        $shop = auth()->user()->shops()->first();

        if ($shop) {
            $shop->update(['line_notify_user_id' => null]);
        }

        return redirect()->route('manage.dashboard')->with('line_notify_removed', true);
    }
}
