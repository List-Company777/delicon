<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerCommission;
use App\Models\Shop;
use App\Models\ShopPlanApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::withCount('shops')
            ->with(['commissions' => fn($q) => $q->where('status', 'pending')])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.partners.index', compact('partners'));
    }

    public function create()
    {
        return view('admin.partners.form', ['partner' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['referral_code'] = $data['referral_code'] ?: strtoupper(Str::random(8));

        $partner = Partner::create($data);

        // ログインユーザーを自動作成（同メールのユーザーが未存在の場合）
        $successMsg = 'パートナーを登録しました';
        if (!empty($data['email']) && !User::where('email', $data['email'])->exists()) {
            $password = Str::random(10) . '!1';
            User::create([
                'name'              => $data['contact_name'] ?? $data['company_name'],
                'email'             => $data['email'],
                'password'          => Hash::make($password),
                'role'              => 'agency',
                'partner_id'        => $partner->id,
                'email_verified_at' => now(),
            ]);
            $successMsg .= "（ログインパスワード: {$password}）";
        }

        return redirect()->route('admin.partners.index')->with('success', $successMsg);
    }

    public function edit(Partner $partner)
    {
        return view('admin.partners.form', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $data = $this->validated($request, $partner->id);
        $partner->update($data);

        return redirect()->route('admin.partners.index')->with('success', 'パートナー情報を更新しました');
    }

    public function show(Partner $partner)
    {
        $shops       = Shop::where('partner_id', $partner->id)->with('genre', 'area')->get();
        $commissions = PartnerCommission::where('partner_id', $partner->id)
            ->with('shop')
            ->orderByDesc('created_at')
            ->paginate(30);

        $totalPending = $partner->pendingAmount();
        $totalPaid    = (int) $partner->commissions()->where('status', 'paid')->sum('commission_amount');

        $managedActiveCount = $partner->isManagement() ? $shops->where('status', 'active')->count() : 0;
        $calculatedRate     = $partner->isManagement() ? $partner->calculatedManagementRate() : 0.0;
        $effectiveRate      = $partner->effectiveCommissionRate();

        return view('admin.partners.show', compact(
            'partner', 'shops', 'commissions', 'totalPending', 'totalPaid',
            'managedActiveCount', 'calculatedRate', 'effectiveRate'
        ));
    }

    /** 手数料を支払済みにまとめてマーク */
    public function markPaid(Request $request, Partner $partner)
    {
        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);

        PartnerCommission::where('partner_id', $partner->id)
            ->whereIn('id', $request->ids)
            ->where('status', 'pending')
            ->update(['status' => 'paid', 'paid_at' => now()]);

        return back()->with('success', '支払済みに更新しました');
    }

    /** 手数料を手動で記録 */
    public function addCommission(Request $request, Partner $partner)
    {
        $data = $request->validate([
            'shop_id'      => ['required', 'exists:shops,id'],
            'base_amount'  => ['required', 'integer', 'min:1'],
            'description'  => ['nullable', 'string', 'max:200'],
            'period_start' => ['nullable', 'date'],
            'period_end'   => ['nullable', 'date', 'after_or_equal:period_start'],
        ]);

        $shop = Shop::findOrFail($data['shop_id']);
        // 紹介店舗かチェック
        abort_if($shop->partner_id !== $partner->id, 403);

        $rate             = (float) $partner->commission_rate;
        $commissionAmount = (int) round($data['base_amount'] * $rate);

        PartnerCommission::create([
            'partner_id'        => $partner->id,
            'shop_id'           => $data['shop_id'],
            'base_amount'       => $data['base_amount'],
            'rate'              => $rate,
            'commission_amount' => $commissionAmount,
            'description'       => $data['description'] ?? null,
            'period_start'      => $data['period_start'] ?? null,
            'period_end'        => $data['period_end'] ?? null,
            'status'            => 'pending',
        ]);

        return back()->with('success', '手数料を記録しました（' . number_format($commissionAmount) . '円）');
    }

    /** パートナーログインユーザーを作成 */
    public function createUser(Request $request, Partner $partner)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'role'              => 'agency',
            'partner_id'        => $partner->id,
            'email_verified_at' => now(),
        ]);

        return back()->with('success', 'ログインユーザーを作成しました');
    }

    /** 管理代行代理店：月別請求CSVダウンロード */
    public function downloadCsv(Request $request, Partner $partner)
    {
        abort_if(! $partner->isManagement(), 403);

        $year  = (int) ($request->query('year',  now()->year));
        $month = (int) ($request->query('month', now()->month));

        $applications = ShopPlanApplication::where('partner_id', $partner->id)
            ->where('status', 'approved')
            ->whereYear('approved_at', $year)
            ->whereMonth('approved_at', $month)
            ->with('shop')
            ->orderBy('approved_at')
            ->get();

        $discount = $partner->effectiveCommissionRate();
        $filename = sprintf('請求明細_%s_%04d%02d.csv', $partner->company_name, $year, $month);

        $rows   = [];
        $rows[] = ['承認日', '店舗ID', '店舗名', '申請金額(円)', '割引率', '割引後金額(税抜)(円)', '消費税(円)', '請求金額(税込)(円)', '希望入札単価'];

        foreach ($applications as $app) {
            $discounted = (int) round($app->amount * (1 - $discount));
            $tax        = (int) round($discounted * 0.1);
            $total      = $discounted + $tax;
            $rows[]     = [
                $app->approved_at->format('Y/m/d'),
                $app->shop_id,
                $app->shop?->name ?? '',
                $app->amount,
                number_format($discount * 100, 1) . '%',
                $discounted,
                $tax,
                $total,
                $app->bid_price_requested,
            ];
        }

        $grandDiscounted = (int) round($applications->sum('amount') * (1 - $discount));
        $grandTax        = (int) round($grandDiscounted * 0.1);
        $rows[]          = ['合計', '', '', $applications->sum('amount'), '', $grandDiscounted, $grandTax, $grandDiscounted + $grandTax, ''];

        $callback = function () use ($rows) {
            $fp = fopen('php://output', 'w');
            // BOM for Excel
            fputs($fp, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($fp, $row);
            }
            fclose($fp);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $isManagement = $request->type === 'management';

        $data = $request->validate([
            'type'                     => ['required', 'in:referral,management'],
            'company_name'             => ['required', 'string', 'max:100'],
            'contact_name'             => ['nullable', 'string', 'max:50'],
            'email'                    => ['required', 'email', 'max:255', 'unique:partners,email' . ($ignoreId ? ",{$ignoreId}" : '')],
            'tel'                      => ['nullable', 'string', 'max:20'],
            'referral_code'            => ['nullable', 'string', 'max:20', 'alpha_num', 'unique:partners,referral_code' . ($ignoreId ? ",{$ignoreId}" : '')],
            'commission_rate'          => $isManagement ? ['nullable', 'numeric', 'min:0', 'max:1'] : ['required', 'numeric', 'min:0', 'max:1'],
            'commission_rate_override' => $isManagement ? ['nullable', 'numeric', 'min:0', 'max:1'] : [],
            'bank_info'                => ['nullable', 'string', 'max:500'],
            'invoice_number'           => ['nullable', 'string', 'max:20', 'regex:/^T\d{13}$/'],
            'status'                   => ['required', 'in:active,inactive'],
            'notes'                    => ['nullable', 'string', 'max:1000'],
        ]);

        if ($isManagement && !isset($data['commission_rate'])) {
            $data['commission_rate'] = 0;
        }

        return $data;
    }
}
