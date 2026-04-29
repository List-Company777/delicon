<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Manage\BaseController;
use App\Models\Application;
use App\Models\ShopPlanApplication;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $ownedShops = $user->shops()->wherePivot('role', 'owner')->get();

        $shop = $this->getShop();

        $pendingApplication = $shop
            ? ShopPlanApplication::where('shop_id', $shop->id)
                ->where('status', 'pending')
                ->latest()
                ->first()
            : null;

        // 未読返信があるスレッド（応募者からの未読メッセージ）
        $unreadThreads = $shop
            ? Application::where('shop_id', $shop->id)
                ->whereHas('messages', fn($q) => $q->where('sender', 'applicant')->whereNull('read_at'))
                ->with(['messages' => fn($q) => $q->where('sender', 'applicant')->whereNull('read_at')->latest()->limit(1)])
                ->latest()
                ->get()
            : collect();

        return view('manage.dashboard', compact('shop', 'pendingApplication', 'ownedShops', 'unreadThreads'));
    }

    public function switchShop(Request $request, int $shopId)
    {
        $user = $request->user();
        $shop = $user->shops()->wherePivot('role', 'owner')->where('shops.id', $shopId)->first();
        abort_if(! $shop, 403);
        session(['managing_shop_id' => $shopId]);
        return redirect()->route('manage.dashboard');
    }

    public function apply(Request $request)
    {
        $shop = $this->getShop();

        abort_if(! $shop, 404);
        abort_if($shop->status !== 'inactive', 403);

        $missing = [];
        if (! $shop->postal_code)      $missing[] = '郵便番号';
        if (! $shop->address_locality) $missing[] = '市区町村';
        if (! $shop->address)          $missing[] = '番地・建物名';
        if ($missing) {
            return back()->withErrors(['apply' => '掲載申請には ' . implode('・', $missing) . ' の入力が必要です。店舗情報ページから入力してください。']);
        }

        $shop->update(['status' => 'pending']);

        return back()->with('applied', true);
    }

    public function applyPlan(Request $request)
    {
        $shop = $this->getShop();

        abort_if(! $shop, 404);
        abort_if($shop->status !== 'active', 403);

        $validated = $request->validate([
            'amount'            => ['required', 'integer', 'min:1000', 'max:999999'],
            'bid_price_requested' => ['required', 'integer', 'min:30', 'max:9990'],
        ]);

        // 既に pending の申し込みがある場合は拒否
        $existing = ShopPlanApplication::where('shop_id', $shop->id)
            ->where('status', 'pending')
            ->exists();
        abort_if($existing, 422, '審査中の申し込みがすでにあります');

        ShopPlanApplication::create([
            'shop_id'             => $shop->id,
            'amount'              => $validated['amount'],
            'bid_price_requested' => $validated['bid_price_requested'],
            'status'              => 'pending',
        ]);

        return back()->with('plan_applied', true);
    }

    public function updateBidPrice(Request $request)
    {
        $shop = $this->getShop();

        abort_if(! $shop, 404);
        abort_if(! $shop->hasBudget(), 403, '予算残高がありません');

        $validated = $request->validate([
            'bid_price' => ['required', 'integer', 'min:30', 'max:9990'],
        ]);

        // 入札単価を上げると残高が入札単価を下回る可能性があるのでチェック
        if ($shop->budget_balance < $validated['bid_price']) {
            return back()->withErrors(['bid_price' => '残高（' . number_format($shop->budget_balance) . '円）を超える入札単価は設定できません']);
        }

        $shop->update(['bid_price' => $validated['bid_price']]);

        return back()->with('bid_price_updated', true);
    }
}
