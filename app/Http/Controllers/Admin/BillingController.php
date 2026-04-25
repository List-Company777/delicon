<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopPlanApplication;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $year  = (int) ($request->query('year',  now()->year));
        $month = (int) ($request->query('month', now()->month));

        $applications = $this->fetchApplications($year, $month);
        $rows         = $this->buildRows($applications);
        $summary      = $this->buildSummary($rows);

        return view('admin.billing.index', compact('year', 'month', 'rows', 'summary'));
    }

    public function downloadCsv(Request $request)
    {
        $year  = (int) ($request->query('year',  now()->year));
        $month = (int) ($request->query('month', now()->month));

        $applications = $this->fetchApplications($year, $month);
        $rows         = $this->buildRows($applications);

        $filename = sprintf('月次取引明細_%04d%02d.csv', $year, $month);

        $callback = function () use ($rows, $year, $month) {
            $fp = fopen('php://output', 'w');
            fputs($fp, "\xEF\xBB\xBF");

            fputcsv($fp, ['承認日', '店舗ID', '店舗名', '区分', '代理店名', '予算追加額(円)', '請求金額(税抜)(円)', '消費税(円)', '請求金額(税込)(円)', '備考']);

            foreach ($rows as $r) {
                fputcsv($fp, [
                    $r['approved_at'],
                    $r['shop_id'],
                    $r['shop_name'],
                    $r['type_label'],
                    $r['partner_name'],
                    $r['amount'],
                    $r['net'],
                    $r['tax'],
                    $r['total'],
                    $r['note'],
                ]);
            }

            // 合計行
            fputcsv($fp, ['合計', '', '', '', '',
                array_sum(array_column($rows, 'amount')),
                array_sum(array_column($rows, 'net')),
                array_sum(array_column($rows, 'tax')),
                array_sum(array_column($rows, 'total')),
                '',
            ]);

            fclose($fp);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function fetchApplications(int $year, int $month)
    {
        return ShopPlanApplication::with(['shop', 'partner'])
            ->where('status', 'approved')
            ->whereYear('approved_at', $year)
            ->whereMonth('approved_at', $month)
            ->orderBy('approved_at')
            ->get();
    }

    private function buildRows($applications): array
    {
        $rows = [];

        foreach ($applications as $app) {
            $partner = $app->partner;

            if (! $partner) {
                $typeLabel   = '直接';
                $partnerName = '—';
                $rate        = 0;
                $isManagement = false;
            } elseif ($partner->isManagement()) {
                $typeLabel    = '管理代行代理店';
                $partnerName  = $partner->company_name;
                $rate         = (float) $partner->commission_rate;
                $isManagement = true;
            } else {
                $typeLabel    = '紹介代理店';
                $partnerName  = $partner->company_name;
                $rate         = (float) $partner->commission_rate;
                $isManagement = false;
            }

            if ($isManagement) {
                $net  = (int) round($app->amount * (1 - $rate));
                $tax  = (int) round($net * 0.1);
                $total = $net + $tax;
                $note = sprintf('割引%s%% → ¥%s OFF', number_format($rate * 100, 1), number_format($app->amount - $net));
            } else {
                // 直接・紹介とも消費税10%を加算
                $net   = $app->amount;
                $tax   = (int) round($app->amount * 0.1);
                $total = $net + $tax;
                $note  = $rate > 0
                    ? sprintf('手数料%s%% → 後払い ¥%s', number_format($rate * 100, 1), number_format((int) round($app->amount * $rate)))
                    : '';
            }

            $rows[] = [
                'approved_at'  => $app->approved_at->format('Y/m/d'),
                'shop_id'      => $app->shop_id,
                'shop_name'    => $app->shop?->name ?? '—',
                'type_label'   => $typeLabel,
                'partner_name' => $partnerName,
                'amount'       => $app->amount,
                'net'          => $net,
                'tax'          => $tax,
                'total'        => $total,
                'note'         => $note,
                'type'         => $partner ? $partner->type : 'direct',
            ];
        }

        return $rows;
    }

    private function buildSummary(array $rows): array
    {
        $summary = [
            'direct'     => ['count' => 0, 'amount' => 0, 'total' => 0],
            'referral'   => ['count' => 0, 'amount' => 0, 'total' => 0],
            'management' => ['count' => 0, 'amount' => 0, 'total' => 0],
        ];

        foreach ($rows as $r) {
            $key = match ($r['type']) {
                'management' => 'management',
                'referral'   => 'referral',
                default      => 'direct',
            };
            $summary[$key]['count']++;
            $summary[$key]['amount'] += $r['amount'];
            $summary[$key]['total']  += $r['total'];
        }

        return $summary;
    }
}
