<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BudgetCredited;
use App\Mail\PlanApplicationRejected;
use App\Models\ShopPlanApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PlanApplicationController extends Controller
{
    public function index()
    {
        $status = request('status', 'pending');

        $applications = ShopPlanApplication::with(['shop.partner', 'shop.users' => fn($q) => $q->wherePivot('role', 'owner')])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(30);

        $counts = [
            'pending'  => ShopPlanApplication::where('status', 'pending')->count(),
            'approved' => ShopPlanApplication::where('status', 'approved')->count(),
            'rejected' => ShopPlanApplication::where('status', 'rejected')->count(),
            'all'      => ShopPlanApplication::count(),
        ];

        return view('admin.plan-applications.index', compact('applications', 'status', 'counts'));
    }

    public function approve(Request $request, ShopPlanApplication $application)
    {
        abort_if($application->status !== 'pending', 422, 'すでに処理済みです');

        $shop     = $application->shop;
        $amount   = $application->amount;
        $bidPrice = $application->bid_price_requested;

        // 残高加算。XML連携中は bid_price をXMLが管理するので上書きしない
        $shop->increment('budget_balance', $amount);
        if (!$shop->isXmlActive()) {
            $shop->update(['bid_price' => $bidPrice]);
        }

        $application->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'plan_name'   => $request->input('plan_name') ?: null,
        ]);

        // 店舗オーナーへ通知
        $owner = $shop->users()->wherePivot('role', 'owner')->first();
        if ($owner) {
            Mail::to($owner->email)->queue(new BudgetCredited($shop->fresh(), $amount, $bidPrice));
        }

        // 代理店へ通知
        if ($shop->partner) {
            Mail::to($shop->partner->email)->queue(new BudgetCredited($shop->fresh(), $amount, $bidPrice));
        }

        return back()->with('success', "{$shop->name} の申し込みを承認しました（残高 +{$amount}円、入札単価 {$bidPrice}円）");
    }

    public function reject(Request $request, ShopPlanApplication $application)
    {
        abort_if($application->status !== 'pending', 422, 'すでに処理済みです');

        $note = $request->input('note');

        $application->update([
            'status' => 'rejected',
            'note'   => $note,
        ]);

        $shop  = $application->shop;
        $mailable = new PlanApplicationRejected(
            $shop,
            $application->amount,
            $application->bid_price_requested,
            $note ?: null,
        );

        // 店舗オーナーへ通知
        $owner = $shop->users()->wherePivot('role', 'owner')->first();
        if ($owner) {
            Mail::to($owner->email)->queue($mailable);
        }

        // 代理店へ通知
        if ($shop->partner) {
            Mail::to($shop->partner->email)->queue($mailable);
        }

        return back()->with('success', "{$shop->name} の申し込みを却下しました");
    }
}
