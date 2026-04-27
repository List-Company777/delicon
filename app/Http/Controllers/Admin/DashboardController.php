<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleTopic;
use App\Models\ArticleVideo;
use App\Models\Partner;
use App\Models\SearchKeyword;
use App\Models\SearchPageView;
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

        $pendingShops            = Shop::where('status', 'pending')->count();
        $pendingPlanApplications = ShopPlanApplication::where('status', 'pending')->count();

        $kpi = [
            'shops'         => Shop::count(),
            'paid'          => ShopPlanApplication::where('status', 'approved')->count(),
            'articles'      => Article::where('is_published', true)->count(),
            'pv_this_month' => SearchPageView::whereYear('date', now()->year)
                                              ->whereMonth('date', now()->month)
                                              ->sum('count'),
        ];

        $articleStats = [
            'published' => Article::where('is_published', true)->count(),
            'draft'     => Article::where('is_published', false)->count(),
            'topics'    => ArticleTopic::where('status', 'approved')->count(),
            'video'     => ArticleVideo::where('status', 'done')->count(),
        ];

        $partnerCount = Partner::count();

        $recentShops = Shop::latest()->take(5)->get(['id', 'name', 'status', 'created_at']);

        $recentApplications = ShopPlanApplication::with('shop')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'stats', 'pendingShops', 'pendingPlanApplications',
            'kpi', 'articleStats', 'partnerCount',
            'recentShops', 'recentApplications'
        ));
    }
}
