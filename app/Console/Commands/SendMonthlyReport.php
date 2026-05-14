<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Job;
use App\Models\JobAccessLog;
use App\Models\SearchPageView;
use App\Models\Shop;
use App\Models\ShopAccessLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendMonthlyReport extends Command
{
    protected $signature   = 'report:monthly {--month= : 対象月 YYYY-MM（省略時は前月）}';
    protected $description = '月次クリック・PVレポートをメール送信する';

    private const TO = 'line@up-stage.info';

    public function handle(): int
    {
        $month = $this->option('month')
            ? Carbon::createFromFormat('Y-m', $this->option('month'))->startOfMonth()
            : now()->subMonth()->startOfMonth();

        $start  = $month->copy()->startOfMonth();
        $end    = $month->copy()->endOfMonth();
        $label  = $month->format('Y年n月');

        $prev1Start = $start->copy()->subMonth()->startOfMonth();
        $prev1End   = $prev1Start->copy()->endOfMonth();
        $prev1Label = $prev1Start->format('n月');

        $prev2Start = $start->copy()->subMonths(2)->startOfMonth();
        $prev2End   = $prev2Start->copy()->endOfMonth();
        $prev2Label = $prev2Start->format('n月');

        // ── クリック集計 ──────────────────────────────────────────
        $jobView  = fn(Carbon $s, Carbon $e) => JobAccessLog::where('type', 'view')
            ->whereBetween('created_at', [$s, $e])->count();
        $jobClick = fn(Carbon $s, Carbon $e) => JobAccessLog::where('type', 'click')
            ->whereBetween('created_at', [$s, $e])->count();

        // 営業ホットリンク（shop_detailsのis_hotlink=true の店舗）
        $shopHotlink = fn(Carbon $s, Carbon $e) => ShopAccessLog::join('shop_details', 'shop_details.shop_id', '=', 'shop_access_logs.shop_id')
            ->where('shop_details.is_hotlink', true)
            ->whereBetween('shop_access_logs.created_at', [$s, $e])
            ->count();

        // 営業通常クリック（ホットリンク以外）
        $shopNormal = fn(Carbon $s, Carbon $e) => ShopAccessLog::join('shop_details', 'shop_details.shop_id', '=', 'shop_access_logs.shop_id')
            ->where('shop_details.is_hotlink', false)
            ->whereBetween('shop_access_logs.created_at', [$s, $e])
            ->count();

        $data = [
            'cur'  => ['jv' => $jobView($start, $end),   'jc' => $jobClick($start, $end),   'sh' => $shopHotlink($start, $end),   'sn' => $shopNormal($start, $end)],
            'p1'   => ['jv' => $jobView($prev1Start, $prev1End), 'jc' => $jobClick($prev1Start, $prev1End), 'sh' => $shopHotlink($prev1Start, $prev1End), 'sn' => $shopNormal($prev1Start, $prev1End)],
            'p2'   => ['jv' => $jobView($prev2Start, $prev2End), 'jc' => $jobClick($prev2Start, $prev2End), 'sh' => $shopHotlink($prev2Start, $prev2End), 'sn' => $shopNormal($prev2Start, $prev2End)],
        ];

        $total = fn(array $d) => $d['jv'] + $d['jc'] + $d['sh'] + $d['sn'];

        // ── 検索ページ PVトップ10（source別） ────────────────────────
        $topSearchPages = SearchPageView::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('gender, area_slug, job_slug, source, SUM(count) as total')
            ->groupBy('gender', 'area_slug', 'job_slug', 'source')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // source別集計
        $pvBySource = SearchPageView::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('source, SUM(count) as total')
            ->groupBy('source')
            ->pluck('total', 'source');

        // ── サイト概況 ────────────────────────────────────────────
        $activeShops   = Shop::where('status', 'active')->count();
        $activeJobs    = Job::where('status', 'active')->count();
        $paidShops     = Shop::whereRaw('budget_balance >= bid_price')->where('status', 'active')->count();
        $applications  = Application::whereBetween('created_at', [$start, $end])->count();

        // ── メール本文生成 ─────────────────────────────────────────
        $body = $this->buildBody(
            $label, $data, $total, $prev1Label, $prev2Label,
            $topSearchPages, $pvBySource,
            $activeShops, $activeJobs, $paidShops, $applications
        );

        Mail::raw($body, fn($m) => $m
            ->to(self::TO)
            ->subject("【デリヘルリスト】{$label} 月次レポート")
            ->from(config('mail.from.address'), 'デリヘルリスト')
        );

        $this->info("Monthly report sent for {$label}");
        return self::SUCCESS;
    }

    private function buildBody(
        string $label, array $data, callable $total,
        string $prev1Label, string $prev2Label,
        $topSearchPages, $pvBySource,
        int $activeShops, int $activeJobs, int $paidShops, int $applications
    ): string {
        $curTotal  = $total($data['cur']);
        $p1Total   = $total($data['p1']);
        $p2Total   = $total($data['p2']);

        $diff1 = $curTotal - $p1Total;
        $diff2 = $curTotal - $p2Total;
        $pct1  = $p1Total > 0 ? round($diff1 / $p1Total * 100, 1) : null;
        $pct2  = $p2Total > 0 ? round($diff2 / $p2Total * 100, 1) : null;

        $sign  = fn($v) => $v >= 0 ? "+{$v}" : (string) $v;
        $pctFmt = fn($v) => $v !== null ? "（{$sign($v)}%）" : '';

        $lines = [];
        $lines[] = "【デリヘルリスト】{$label} 月次レポート";
        $lines[] = str_repeat('=', 50);
        $lines[] = '';

        // ── クリック集計 ──
        $lines[] = '■ クリック集計（課金対象アクセス）';
        $lines[] = '';
        $lines[] = sprintf("  今月  (%s) : %s 件", $label,       number_format($curTotal));
        $lines[] = sprintf("  先月  (%s) : %s 件  %s %s",
            $prev1Label, number_format($p1Total), $sign($diff1) . '件', $pctFmt($pct1));
        $lines[] = sprintf("  先々月(%s) : %s 件  %s %s",
            $prev2Label, number_format($p2Total), $sign($diff2) . '件', $pctFmt($pct2));
        $lines[] = '';

        // 内訳
        $lines[] = '  ┌── 今月の内訳 ──────────────────────────';
        $lines[] = sprintf("  │  求人ページ表示（PV）   : %s 件", number_format($data['cur']['jv']));
        $lines[] = sprintf("  │  求人ホットリンク       : %s 件", number_format($data['cur']['jc']));
        $lines[] = sprintf("  │  営業ページ表示         : %s 件", number_format($data['cur']['sn']));
        $lines[] = sprintf("  │  営業ホットリンク       : %s 件", number_format($data['cur']['sh']));
        $lines[] = '  └──────────────────────────────────────';
        $lines[] = '';

        // ── 月別推移（内訳） ──
        $lines[] = '■ 月別推移（内訳）';
        $lines[] = '';
        $lines[] = sprintf("  %-10s  求人PV    求人HL    営業PV    営業HL", '');
        foreach ([
            [$label,       $data['cur']],
            [$prev1Label . '（先月）', $data['p1']],
            [$prev2Label . '（先々月）', $data['p2']],
        ] as [$lbl, $d]) {
            $lines[] = sprintf("  %-12s  %6s    %6s    %6s    %6s",
                $lbl,
                number_format($d['jv']),
                number_format($d['jc']),
                number_format($d['sn']),
                number_format($d['sh'])
            );
        }
        $lines[] = '';

        // ── 検索ページ PV（source別） ──
        $srcLabels = ['organic' => '検索流入', 'internal' => 'サイト内', 'direct' => 'ダイレクト', 'other' => 'その他'];
        $pvTotal   = $pvBySource->sum();
        $lines[] = '■ 検索ページPV（流入元別）';
        $lines[] = '';
        if ($pvTotal === 0) {
            $lines[] = '  データなし';
        } else {
            foreach ($srcLabels as $key => $srcLabel) {
                $cnt  = $pvBySource->get($key, 0);
                $pct  = $pvTotal > 0 ? round($cnt / $pvTotal * 100, 1) : 0;
                $lines[] = sprintf("  %-12s : %s 件  (%s%%)", $srcLabel, number_format($cnt), $pct);
            }
        }
        $lines[] = '';

        // ── 検索ページ PVトップ10 ──
        $lines[] = '■ 今月の検索ページPVトップ10';
        $lines[] = '';
        if ($topSearchPages->isEmpty()) {
            $lines[] = '  データなし';
        } else {
            $i = 1;
            foreach ($topSearchPages as $row) {
                $path = "/{$row->gender}/{$row->area_slug}/{$row->job_slug}/";
                $src  = $srcLabels[$row->source] ?? $row->source;
                $lines[] = sprintf("  %2d. %-40s %s  %s 件", $i++, $path, $src, number_format($row->total));
            }
        }
        $lines[] = '';

        // ── サイト概況 ──
        $lines[] = '■ サイト概況（月末時点）';
        $lines[] = '';
        $lines[] = sprintf("  アクティブ店舗数   : %s 件", number_format($activeShops));
        $lines[] = sprintf("  うち有料掲載中     : %s 件", number_format($paidShops));
        $lines[] = sprintf("  アクティブ求人数   : %s 件", number_format($activeJobs));
        $lines[] = sprintf("  当月応募数         : %s 件", number_format($applications));
        $lines[] = '';
        $lines[] = str_repeat('-', 50);
        $lines[] = 'デリヘルリスト 自動レポート';
        $lines[] = 'https://delicon.jp/';

        return implode("\n", $lines);
    }
}
