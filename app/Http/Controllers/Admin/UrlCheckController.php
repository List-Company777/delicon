<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopExternalUrl;
use Illuminate\Http\Request;

class UrlCheckController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->input('status_filter', 'error');

        $query = ShopExternalUrl::with(['shop.prefecture', 'shop.area'])
            ->where('url_type', 'website')
            ->whereHas('shop', fn($q) => $q->where('status', '!=', 'inactive'))
            ->whereNotNull('url_checked_at');

        if ($statusFilter === 'error') {
            $query->where(function ($q) {
                $q->where('url_status', 0)
                  ->orWhere('url_status', '>=', 400);
            });
        } elseif ($statusFilter === 'ok') {
            $query->where('url_status', '>=', 200)->where('url_status', '<', 400);
        }

        $urls   = $query->orderBy('url_status')->orderBy('url_checked_at')->paginate(50);
        $total  = ShopExternalUrl::where('url_type', 'website')
                    ->whereHas('shop', fn($q) => $q->where('status', '!=', 'inactive'))
                    ->count();
        $checked = ShopExternalUrl::where('url_type', 'website')
                    ->whereNotNull('url_checked_at')->count();
        $errors  = ShopExternalUrl::where('url_type', 'website')
                    ->whereNotNull('url_checked_at')
                    ->where(function ($q) {
                        $q->where('url_status', 0)->orWhere('url_status', '>=', 400);
                    })->count();

        return view('admin.url-check.index', compact('urls', 'total', 'checked', 'errors', 'statusFilter'));
    }

    public function dismiss(ShopExternalUrl $shopExternalUrl)
    {
        $shopExternalUrl->update(['url_status' => 200, 'url_checked_at' => now()]);
        return back()->with('success', "「{$shopExternalUrl->shop->name}」のURLを正常扱いにしました。");
    }

    public function deactivate(ShopExternalUrl $shopExternalUrl)
    {
        $shopExternalUrl->shop->update(['status' => 'inactive']);
        return back()->with('success', "「{$shopExternalUrl->shop->name}」を非公開にしました。");
    }
}
