<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SearchKeyword;
use App\Models\Shop;
use App\Models\ShopPlanApplication;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'new'      => SearchKeyword::where('normalization_status', 'new')->count(),
            'mapped'   => SearchKeyword::where('normalization_status', 'mapped')->count(),
            'excluded' => SearchKeyword::where('normalization_status', 'excluded')->count(),
        ];

        $pendingShops        = Shop::where('status', 'pending')->count();
        $pendingPlanApplications = ShopPlanApplication::where('status', 'pending')->count();

        return view('admin.dashboard', compact('stats', 'pendingShops', 'pendingPlanApplications'));
    }
}
