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
        $unchecked   = Shop::where('is_banner_plan', 1)->where('status', '!=', 'inactive')->whereNull('banner_checked_at')->count();

        // 未適用:
        //   banner_ok=1（両方あり）→ プラン3候補（全プラン対象）
        //   banner_ok=4（deliconのみ）かつ plan=5 → プラン4候補
        $unapplied = Shop::where('is_banner_plan', 0)->where('status', '!=', 'inactive')
            ->where(function ($q) {
                $q->whereIn('banner_ok', [1, 2, 3])
                  ->orWhere(fn($q2) => $q2->where('banner_ok', 4)->where('plan', 5));
            })->count();

        $base = Shop::where('status', '!=', 'inactive')
            ->with(['externalUrls' => fn($q) => $q->where('url_type', 'website'), 'prefecture', 'area']);

        $shops = match($tab) {
            'ng'        => (clone $base)->where('is_banner_plan', 1)->where('banner_ok', 0)->whereNotNull('banner_checked_at')->orderBy('name')->paginate(50),
            'broken'    => (clone $base)->where('is_banner_plan', 1)->where('banner_ok', 2)->orderBy('name')->paginate(50),
            'manual'    => (clone $base)->where('is_banner_plan', 1)->where('banner_ok', 3)->orderBy('name')->paginate(50),
            'unapplied' => (clone $base)->where('is_banner_plan', 0)
                ->where(function ($q) {
                    $q->whereIn('banner_ok', [1, 2, 3])
                      ->orWhere(fn($q2) => $q2->where('banner_ok', 4)->where('plan', 5));
                })->orderBy('name')->paginate(50),
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
        // banner_ok=1/2/3（両方あり）→ プラン3 + バナープラン
        if (in_array($shop->banner_ok, [1, 2, 3])) {
            $shop->update([
                'plan'           => 3,
                'is_banner_plan' => true,
                'plan3_since'    => $shop->plan3_since ?? now(),
            ]);
            return back()->with('success', "「{$shop->name}」をプラン3（バナープラン）に適用しました。");
        }

        // banner_ok=4（deliconのみ）かつ plan=5 → プラン4（無料上位）
        if ($shop->banner_ok === 4 && $shop->plan === 5) {
            $shop->update([
                'plan'        => 4,
                'plan4_since' => $shop->plan4_since ?? now(),
            ]);
            return back()->with('success', "「{$shop->name}」をプラン4（無料上位）に適用しました。");
        }

        return back()->with('error', '適用条件を満たしていません。');
    }
}
