<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\ShopPlanApplication;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $year      = (int) ($request->query('year',  now()->year));
        $month     = (int) ($request->query('month', now()->month));
        $partnerId = $request->query('partner_id', '');

        $applications = $this->fetchApplications($year, $month, $partnerId);
        $rows         = $this->buildRows($applications);
        $summary      = $this->buildSummary($rows);

        $partners = Partner::orderBy('company_name')->get(['id', 'company_name', 'type']);

        return view('admin.billing.index', compact('year', 'month', 'partnerId', 'rows', 'summary', 'partners'));
    }

    public function downloadCsv(Request $request)
    {
        $year      = (int) ($request->query('year',  now()->year));
        $month     = (int) ($request->query('month', now()->month));
        $partnerId = $request->query('partner_id', '');

        $applications = $this->fetchApplications($year, $month, $partnerId);
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

    private function fetchApplications(int $year, int $month, string $partnerId = '')
    {
        return ShopPlanApplication::with(['shop', 'partner'])
            ->where('status', 'approved')
            ->whereYear('approved_at', $year)
            ->whereMonth('approved_at', $month)
            ->when($partnerId === 'direct', fn($q) => $q->whereNull('partner_id'))
            ->when($partnerId !== '' && $partnerId !== 'direct', fn($q) => $q->where('partner_id', (int) $partnerId))
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

    public function downloadInvoy(Request $request)
    {
        $year  = (int) ($request->query('year',  now()->year));
        $month = (int) ($request->query('month', now()->month));

        // パートナーごとに承認済み申請をグループ化（直接契約は除外）
        $applications = ShopPlanApplication::with(['shop.area', 'partner'])
            ->where('status', 'approved')
            ->whereYear('approved_at', $year)
            ->whereMonth('approved_at', $month)
            ->whereNotNull('partner_id')
            ->orderBy('partner_id')
            ->orderBy('approved_at')
            ->get();

        $grouped = $applications->groupBy('partner_id');

        $filename = sprintf('invoy_%04d%02d.csv', $year, $month);
        $issueDate = sprintf('%04d-%02d-01', $year, $month);
        $dueDate   = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->addDays(30)->format('Y-m-d');

        $bankInfo = implode("\n", [
            'りそな銀行　神楽坂支店',
            'カ）リスト　普通口座　1691936',
            '※お振込手数料は貴社ご負担にてお願い致します。',
        ]);

        $callback = function () use ($grouped, $year, $month, $issueDate, $dueDate, $bankInfo) {
            $fp = fopen('php://output', 'w');
            fputs($fp, "\xEF\xBB\xBF");

            $seq = 1;
            foreach ($grouped as $partnerId => $apps) {
                $partner = $apps->first()->partner;

                $invoiceNo = sprintf('%04d%02d01%03d', $year, $month, $seq++);
                $subject   = sprintf('%s %04d年%02d月度掲載分', $partner->company_name, $year, $month);

                $row = [
                    $invoiceNo,          // A 請求書番号
                    $issueDate,          // B 発行日
                    $dueDate,            // C お支払い期限
                    '',                  // D 和暦
                    $subject,            // E 件名
                    '高司 浩',            // F 請求元氏名
                    '株式会社リスト',      // G 請求元会社名
                    '',                  // H 部署
                    '104-0061',          // I 郵便番号
                    '東京都中央区銀座３丁目10番９号', // J 住所
                    'KEC銀座ビル701',     // K ビル名
                    '03-5206-6966',      // L TEL
                    '',                  // M FAX
                    'ad@list-company.net', // N メール
                    $partner->invoy_client_code ?? '', // O 取引先コード
                    '税別表示',           // P 消費税
                    '10%',               // Q 消費税率
                    '切捨て',            // R 端数処理
                    'なし',              // S 源泉徴収
                    $bankInfo,           // T 振込先
                    '',                  // U 備考
                    'スタンダード',        // V 帳票レイアウト
                ];

                foreach ($apps as $app) {
                    $shop      = $app->shop;
                    $area      = $shop?->area?->name ?? '';
                    $date      = $app->approved_at->format('n/j');
                    $planLabel = $app->plan_name
                        ?: sprintf('nightwork-list 広告掲載料 %s～', $app->approved_at->format('n月j日'));
                    $itemName  = sprintf('%s　%s　%s　%s（ID：%s）',
                        $date,
                        $shop?->name ?? '',
                        $area,
                        $planLabel,
                        $shop?->id ?? ''
                    );

                    $rate = $partner ? (float) $partner->effectiveCommissionRate() : 0;
                    $net  = (int) round($app->amount * (1 - $rate));

                    $row[] = $itemName;    // W 品目名
                    $row[] = 1;            // X 数量
                    $row[] = '式';         // Y 単位
                    $row[] = $net;         // Z 単価
                    $row[] = '適用しない'; // AA 軽減税率
                    $row[] = '適用しない'; // AB 非課税
                    $row[] = '適用しない'; // AC 源泉徴収
                }

                fputcsv($fp, $row);
            }

            fclose($fp);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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
