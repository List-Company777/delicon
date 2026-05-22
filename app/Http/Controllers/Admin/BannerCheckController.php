<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;

class BannerCheckController extends Controller
{
    public function index()
    {
        $domain  = parse_url(config('app.url'), PHP_URL_HOST);
        $tab     = request('tab', 'ng');

        $totalBanner = Shop::where('is_banner_plan', 1)->where('status', '!=', 'inactive')->count();
        $okCount     = Shop::where('is_banner_plan', 1)->where('status', '!=', 'inactive')->where('banner_ok', 1)->count();
        $ngCount     = Shop::where('is_banner_plan', 1)->where('status', '!=', 'inactive')->where('banner_ok', 0)->count();
        $brokenCount = Shop::where('is_banner_plan', 1)->where('status', '!=', 'inactive')->where('banner_ok', 2)->count();
        $manualCount = Shop::where('is_banner_plan', 1)->where('status', '!=', 'inactive')->where('banner_ok', 3)->count();
        $unapplied   = Shop::where('is_banner_plan', 0)->where('status', '!=', 'inactive')->whereIn('banner_ok', [1, 2, 3])->count();
        $unchecked   = Shop::where('is_banner_plan', 1)->where('status', '!=', 'inactive')->whereNull('banner_checked_at')->count();

        $base = Shop::where('status', '!=', 'inactive')
            ->with(['externalUrls' => fn($q) => $q->where('url_type', 'website'), 'prefecture', 'area']);

        $shops = match($tab) {
            'ng'        => (clone $base)->where('is_banner_plan', 1)->where('banner_ok', 0)->whereNotNull('banner_checked_at')->orderBy('name')->paginate(50),
            'broken'    => (clone $base)->where('is_banner_plan', 1)->where('banner_ok', 2)->orderBy('name')->paginate(50),
            'manual'    => (clone $base)->where('is_banner_plan', 1)->where('banner_ok', 3)->orderBy('name')->paginate(50),
            'unapplied' => (clone $base)->where('is_banner_plan', 0)->whereIn('banner_ok', [1, 2, 3])->orderBy('name')->paginate(50),
            'ok'        => (clone $base)->where('is_banner_plan', 1)->where('banner_ok', 1)->orderBy('name')->paginate(50),
            default     => (clone $base)->where('is_banner_plan', 1)->whereNull('banner_checked_at')->orderBy('name')->paginate(50),
        };

        return view('admin.banner-check.index', compact(
            'shops', 'domain', 'tab',
            'totalBanner', 'okCount', 'ngCount', 'brokenCount', 'manualCount', 'unapplied', 'unchecked'
        ));
    }

    public function manualOk(Shop $shop)
    {
        $shop->update(['banner_ok' => 3, 'banner_checked_at' => now()]);
        return back()->with('success', "「{$shop->name}」を手動確認済みにしました。");
    }

    public function applyBanner(Shop $shop)
    {
        $shop->update(['is_banner_plan' => 1]);
        return back()->with('success', "「{$shop->name}」をバナープランに適用しました。");
    }
}
