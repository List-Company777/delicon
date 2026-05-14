<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;

class BannerCheckController extends Controller
{
    public function index()
    {
        $shops = Shop::with(['detail', 'area.prefecture'])
            ->where(function ($q) {
                $q->where(fn($q2) => $q2->where('plan', 3)->where('is_banner_plan', true))
                  ->orWhere('plan', 4);
            })
            ->orderByRaw('banner_checked_at IS NOT NULL ASC, banner_checked_at ASC, id ASC')
            ->get();

        return view('admin.banner-check.index', compact('shops'));
    }

    public function check(int $id)
    {
        Shop::findOrFail($id)->update(['banner_checked_at' => now()]);
        return back()->with('success', 'チェック済みにしました');
    }
}
