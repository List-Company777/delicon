<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanSlotLimit;
use App\Models\Shop;
use App\Models\ShopPlanApplication;
use Illuminate\Http\Request;

class PlanApplicationController extends Controller
{
    private array $planLabels = [
        1 => 'VIP',
        2 => 'ミドル',
        3 => 'ベーシック',
        4 => '無料上位',
        5 => '無料',
    ];

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $applications = ShopPlanApplication::with(['shop.partner', 'shop.users'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $counts = [
            'pending'  => ShopPlanApplication::where('status', 'pending')->count(),
            'approved' => ShopPlanApplication::where('status', 'approved')->count(),
            'rejected' => ShopPlanApplication::where('status', 'rejected')->count(),
            'all'      => ShopPlanApplication::count(),
        ];

        $planLabels = [
            1 => ['label' => 'VIP',        'color' => 'bg-yellow-100 text-yellow-700'],
            2 => ['label' => 'ミドル',     'color' => 'bg-purple-100 text-purple-700'],
            3 => ['label' => 'ベーシック',  'color' => 'bg-blue-100 text-blue-700'],
            4 => ['label' => '無料上位',   'color' => 'bg-green-100 text-green-700'],
            5 => ['label' => '無料',       'color' => 'bg-gray-100 text-gray-500'],
        ];

        return view('admin.plan-applications.index', compact('applications', 'counts', 'status', 'planLabels'));
    }

    public function approve(Request $request, ShopPlanApplication $application)
    {
        abort_if($application->status !== 'pending', 422);

        $request->validate([
            'plan'           => ['nullable', 'integer', 'min:1', 'max:5'],
            'is_banner_plan' => ['nullable', 'boolean'],
            'plan_name'      => ['nullable', 'string', 'max:255'],
        ]);

        $shop    = $application->shop;
        $newPlan = $request->filled('plan') ? (int) $request->plan : ($application->plan ?? 3);
        $today   = now()->toDateString();

        // VIP限定枠チェック（force_approve がなければ警告を返す）
        if ($newPlan === 1 && ! $request->boolean('force_approve')) {
            $prefId    = $shop->prefecture_id;
            $slotLimit = $prefId ? PlanSlotLimit::where('prefecture_id', $prefId)->value('max_slots') : null;
            $slotLimit = $slotLimit ?? 5; // デフォルト5枠
            $current   = Shop::where('plan', 1)
                ->where('prefecture_id', $prefId)
                ->where('id', '!=', $shop->id)
                ->count();
            if ($current >= $slotLimit) {
                return back()
                    ->withInput()
                    ->with('slot_warning', [
                        'app_id'    => $application->id,
                        'current'   => $current,
                        'max'       => $slotLimit,
                        'pref_name' => $shop->prefecture?->prefecture ?? '該当都道府県',
                    ]);
            }
        }

        // 掲載期間を確定（申し込み時の expires_on を使用、なければ翌月末）
        $expiresOn = $application->expires_on
            ?? now()->addMonthNoOverflow()->endOfMonth()->toDateString();

        $appType = $application->application_type ?? 'new';

        if ($appType === 'new') {
            // 新規：即時掲載開始
            $oldPlan         = (int) $shop->plan;
            $planSinceKey    = "plan{$newPlan}_since";
            $oldPlanSinceKey = "plan{$oldPlan}_since";
            $isUpgrade       = $newPlan < $oldPlan;
            $isDowngrade     = $newPlan > $oldPlan;

            $newPlanSince = $isUpgrade
                ? $today
                : ($shop->$planSinceKey ?? $shop->$oldPlanSinceKey ?? $today);

            $updates = [
                'plan'            => $newPlan,
                'is_banner_plan'  => $newPlan === 3 ? (bool) $request->is_banner_plan : false,
                'paid_since'      => $shop->paid_since ?? $today,
                $planSinceKey     => $newPlanSince,
                'plan_expires_on' => $expiresOn,
            ];
            if ($isDowngrade && $oldPlan <= 4) {
                $updates[$oldPlanSinceKey] = null;
            }
            $shop->update($updates);

            $effectiveDate = $today;
            $msg = "「{$shop->name}」を承認しました。即時掲載開始（{$this->planLabels[$newPlan]}、{$expiresOn}まで）";
        } else {
            // 継続：翌月1日に plans:monthly-process が適用するため shop は変更しない
            $effectiveDate = $application->effective_date
                ?? now()->addMonthNoOverflow()->startOfMonth()->toDateString();

            $msg = "「{$shop->name}」の継続申し込みを承認しました（{$this->planLabels[$newPlan]}、{$effectiveDate}〜{$expiresOn}）";
        }

        $application->update([
            'status'         => 'approved',
            'plan'           => $newPlan,
            'effective_date' => $effectiveDate,
            'expires_on'     => $expiresOn,
            'plan_name'      => $request->plan_name ?: ($this->planLabels[$newPlan] ?? ''),
            'approved_at'    => now(),
        ]);

        return back()->with('success', $msg);
    }

    public function reject(Request $request, ShopPlanApplication $application)
    {
        abort_if($application->status !== 'pending', 422);

        $application->update([
            'status' => 'rejected',
            'note'   => $request->input('note'),
        ]);

        return back()->with('success', '申し込みを却下しました');
    }
}
