<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Manage\BaseController;
use App\Models\JobAccessLog;
use App\Models\ShopAccessLog;
use App\Models\ShopPlanApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaidPlanController extends BaseController
{
    public function index(Request $request)
    {
        $shop = $this->getShop();
        if (! $shop) {
            return redirect()->route('manage.dashboard');
        }

        $pendingApplication = ShopPlanApplication::where('shop_id', $shop->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $since = now()->subDays(29)->startOfDay();
        $jobIds = $shop->jobs()->pluck('id');

        // 過去30日間の日別クリック数（求人 + 店舗を合算）
        $jobClicksByDay = JobAccessLog::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->whereIn('job_id', $jobIds)
            ->where('type', 'view')
            ->where('created_at', '>=', $since)
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $shopClicksByDay = ShopAccessLog::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('shop_id', $shop->id)
            ->where('created_at', '>=', $since)
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // 日付ラベルと内訳を30日分整形（空日は0補完）
        $labels      = [];
        $jobCounts   = [];
        $shopCounts  = [];
        for ($i = 29; $i >= 0; $i--) {
            $date         = now()->subDays($i)->format('Y-m-d');
            $labels[]     = now()->subDays($i)->format('m/d');
            $jobCounts[]  = $jobClicksByDay->get($date)?->count ?? 0;
            $shopCounts[] = $shopClicksByDay->get($date)?->count ?? 0;
        }

        $totalJobClicks30d  = array_sum($jobCounts);
        $totalShopClicks30d = array_sum($shopCounts);

        $totalJobClicksMonth = JobAccessLog::whereIn('job_id', $jobIds)
            ->where('type', 'view')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalShopClicksMonth = ShopAccessLog::where('shop_id', $shop->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('manage.paid-plan.index', compact(
            'shop', 'pendingApplication',
            'labels', 'jobCounts', 'shopCounts',
            'totalJobClicks30d', 'totalShopClicks30d',
            'totalJobClicksMonth', 'totalShopClicksMonth'
        ));
    }

    public function applyPlan(Request $request)
    {
        $shop = $this->getShop();
        abort_if(! $shop, 404);
        abort_if($shop->status !== 'active', 403);

        $validated = $request->validate([
            'amount'              => ['required', 'integer', 'min:10000', 'max:999999'],
            'bid_price_requested' => ['required', 'integer', 'min:30', 'max:9990'],
        ], [
            'amount.required'              => '入金予定金額の入力は必須です。',
            'amount.integer'               => '入金予定金額は数値で入力してください。',
            'amount.min'                   => '入金予定金額は10,000円以上を入力してください。',
            'bid_price_requested.required' => '希望入札単価の入力は必須です。',
            'bid_price_requested.integer'  => '希望入札単価は数値で入力してください。',
            'bid_price_requested.min'      => '希望入札単価は30円以上を入力してください。',
            'bid_price_requested.max'      => '希望入札単価は9,990円以下を入力してください。',
        ]);

        $existing = ShopPlanApplication::where('shop_id', $shop->id)
            ->where('status', 'pending')
            ->exists();
        abort_if($existing, 422, '審査中の申し込みがすでにあります');

        ShopPlanApplication::create([
            'shop_id'             => $shop->id,
            'partner_id'          => $shop->partner_id,
            'amount'              => $validated['amount'],
            'bid_price_requested' => $validated['bid_price_requested'],
            'status'              => 'pending',
        ]);

        return redirect()->route('manage.paid-plan')->with('plan_applied', true);
    }

    public function updateBidPrice(Request $request)
    {
        $shop = $this->getShop();
        abort_if(! $shop, 404);
        abort_if(! $shop->hasBudget(), 403, '予算残高がありません');

        $validated = $request->validate([
            'bid_price' => ['required', 'integer', 'min:30', 'max:9990'],
        ]);

        if ($shop->budget_balance < $validated['bid_price']) {
            return back()->withErrors(['bid_price' => '残高（' . number_format($shop->budget_balance) . '円）を超える入札単価は設定できません']);
        }

        $shop->update(['bid_price' => $validated['bid_price']]);

        return redirect()->route('manage.paid-plan')->with('bid_price_updated', true);
    }
}
