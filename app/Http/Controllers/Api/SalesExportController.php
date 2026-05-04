<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShopPlanApplication;
use Illuminate\Http\Request;

class SalesExportController extends Controller
{
    public function export(Request $request)
    {
        $token = $request->query('token');
        if ($token !== config('app.sales_export_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $month = $request->query('month'); // YYYY-MM
        if (! $month || ! preg_match('/^\d{4}-\d{2}$/', $month)) {
            return response()->json(['error' => 'Invalid month format. Use YYYY-MM.'], 422);
        }

        [$year, $mon] = explode('-', $month);

        $applications = ShopPlanApplication::with(['shop.area', 'partner'])
            ->where('status', 'approved')
            ->whereYear('approved_at', (int) $year)
            ->whereMonth('approved_at', (int) $mon)
            ->orderBy('approved_at')
            ->get();

        $records = $applications->map(function ($app) {
            $partner      = $app->partner;
            $partnerType  = $partner?->type ?? 'direct'; // direct / referral / management
            $rate         = $partner ? (float) $partner->effectiveCommissionRate() : 0.0;
            $listPrice    = (int) $app->amount;
            // 管理代行: 代理店への請求額（割引後）、紹介/直接: 弊社売上額
            $calcAmount   = $partnerType === 'management'
                ? (int) round($listPrice * (1 - $rate))
                : $listPrice;
            // 紹介代理店への支払い額
            $payoutAmount = $partnerType === 'referral'
                ? (int) round($listPrice * $rate)
                : 0;
            $agencyName   = $partner?->company_name ?? '直接';

            return [
                'record_id'          => $app->id,
                'shop_id'            => $app->shop_id,
                'shop_name'          => $app->shop?->name ?? '',
                'partner_type'       => $partnerType,
                'agency_name'        => $agencyName,
                'product_name'       => $app->plan_name ?: 'nightwork-list 広告掲載料',
                'list_price'         => $listPrice,
                'margin_rate'        => $rate,
                'calculated_amount'  => $calcAmount,
                'payout_amount'      => $payoutAmount,
                'contract_date'      => $app->approved_at->format('Y/m/d'),
            ];
        });

        return response()->json([
            'media'   => 'nightwork-list',
            'month'   => $month,
            'records' => $records,
        ]);
    }
}
