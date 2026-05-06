@extends('layouts.admin')
@section('title', $partner->company_name . ' - パートナー詳細')
@section('content')
<div class="bg-gray-800 text-white py-4">
    <div class="max-w-6xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold">Admin › {{ $partner->company_name }}</h1>
        <a href="{{ route('admin.partners.edit', $partner) }}/" class="bg-white text-gray-800 text-sm font-bold px-4 py-1.5 rounded hover:bg-gray-100">編集</a>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-8 space-y-8">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- サマリー --}}
    @php $now = now(); @endphp
    @if($partner->isManagement())
    @php
        $nextTierCount  = (intdiv($managedActiveCount, 100) + 1) * 100;
        $toNextTier     = $nextTierCount - $managedActiveCount;
        $isOverridden   = $partner->commission_rate_override !== null;
        $calcRatePct    = number_format($calculatedRate * 100, 2);
        $effectRatePct  = number_format($effectiveRate * 100, 2);
    @endphp
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">種別</p>
            <p class="text-lg font-bold text-purple-700">管理代行代理店</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">掲載中店舗数</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($managedActiveCount) }}<span class="text-sm font-normal text-gray-400 ml-1">件</span></p>
            @if(!$isOverridden && $calculatedRate < 0.30)
                <p class="text-xs text-gray-400 mt-1">次のティアまであと {{ $toNextTier }} 件</p>
            @endif
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">適用割引率</p>
            <p class="text-2xl font-bold text-gray-800">{{ $effectRatePct }}%</p>
            @if($isOverridden)
                <p class="text-xs text-orange-500 mt-1">オーバーライド中（自動: {{ $calcRatePct }}%）</p>
            @else
                <p class="text-xs text-gray-400 mt-1">自動計算</p>
            @endif
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">当月請求額（税込）</p>
            <p class="text-2xl font-bold text-purple-700">¥{{ number_format($partner->billingAmountForMonth($now->year, $now->month)) }}</p>
        </div>
    </div>
    @else
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">種別</p>
            <p class="text-lg font-bold text-blue-700">紹介代理店</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">未払い手数料</p>
            <p class="text-2xl font-bold text-red-600">¥{{ number_format($totalPending) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs text-gray-400 mb-1">累計支払済み</p>
            <p class="text-2xl font-bold text-gray-800">¥{{ number_format($totalPaid) }}</p>
        </div>
    </div>
    @endif

    {{-- パートナー情報 --}}
    <div class="bg-white rounded-xl shadow-sm p-5 text-sm space-y-2">
        <div class="flex gap-4"><span class="text-gray-400 w-28">紹介URL</span><span class="font-mono text-blue-700">{{ url('/register?ref=' . $partner->referral_code) }}</span></div>
        @if($partner->invoice_number)
        <div class="flex gap-4"><span class="text-gray-400 w-28">インボイス番号</span><span class="font-mono">{{ $partner->invoice_number }}</span></div>
        @endif
        <div class="flex gap-4"><span class="text-gray-400 w-28">メール</span><span>{{ $partner->email }}</span></div>
        @if($partner->tel)<div class="flex gap-4"><span class="text-gray-400 w-28">電話</span><span>{{ $partner->tel }}</span></div>@endif
        @if($partner->bank_info)<div class="flex gap-4"><span class="text-gray-400 w-28">振込先</span><span class="whitespace-pre-line">{{ $partner->bank_info }}</span></div>@endif
        @if($partner->notes)<div class="flex gap-4"><span class="text-gray-400 w-28">メモ</span><span class="whitespace-pre-line">{{ $partner->notes }}</span></div>@endif
    </div>

    {{-- ログインユーザー管理 --}}
    <div>
        <h2 class="text-base font-bold text-gray-700 mb-3">ログインアカウント</h2>
        <div class="bg-white rounded-xl shadow-sm p-5 text-sm">
            @php $partnerUsers = \App\Models\User::where('partner_id', $partner->id)->get(); @endphp
            @if($partnerUsers->isNotEmpty())
            <div class="mb-4 divide-y divide-gray-100">
                @foreach($partnerUsers as $pu)
                <div class="py-2 flex items-center justify-between">
                    <div>
                        <span class="font-medium text-gray-700">{{ $pu->name }}</span>
                        <span class="text-gray-400 ml-2">{{ $pu->email }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
            <form action="{{ route('admin.partners.createUser', $partner) }}/" method="POST" class="flex flex-wrap gap-3 items-end border-t border-gray-100 pt-4">
                @csrf
                <div>
                    <label class="block text-xs text-gray-400 mb-1">担当者名 <span class="text-red-400">*</span></label>
                    <input type="text" name="name" placeholder="例：田中 太郎"
                           class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">メールアドレス <span class="text-red-400">*</span></label>
                    <input type="email" name="email" placeholder="partner@example.com"
                           class="w-56 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">パスワード <span class="text-red-400">*</span></label>
                    <input type="text" name="password" placeholder="8文字以上"
                           class="w-40 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </div>
                <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white text-sm px-4 py-1.5 rounded transition">
                    アカウントを作成
                </button>
            </form>
        </div>
    </div>

    {{-- 紹介店舗一覧 --}}
    <div>
        <h2 class="text-base font-bold text-gray-700 mb-3">紹介店舗（{{ $shops->count() }}件）</h2>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-500 font-medium">店舗名</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-medium">業種</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-medium">エリア</th>
                        <th class="text-center px-4 py-3 text-gray-500 font-medium">ステータス</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($shops as $shop)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $shop->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $shop->genre?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $shop->area?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($shop->status === 'active')
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">掲載中</span>
                            @elseif($shop->status === 'pending')
                                <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">審査中</span>
                            @else
                                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">非公開</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">紹介店舗はまだありません</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 管理代行：月別CSVダウンロード + 申請一覧 --}}
    @if($partner->isManagement())
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-bold text-gray-700">掲載申請記録（管理代行請求根拠）</h2>
            <form method="GET" action="{{ route('admin.partners.downloadCsv', $partner) }}/" class="flex items-center gap-2">
                <select name="year" class="border border-gray-300 rounded px-2 py-1 text-sm">
                    @for($y = now()->year; $y >= now()->year - 1; $y--)
                        <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}年</option>
                    @endfor
                </select>
                <select name="month" class="border border-gray-300 rounded px-2 py-1 text-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ $m }}月</option>
                    @endfor
                </select>
                <button type="submit" class="bg-purple-700 hover:bg-purple-600 text-white text-sm px-4 py-1.5 rounded transition">CSVダウンロード</button>
            </form>
        </div>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-500 font-medium">申請日</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-medium">店舗名</th>
                        <th class="text-right px-4 py-3 text-gray-500 font-medium">申請金額</th>
                        <th class="text-right px-4 py-3 text-gray-500 font-medium">請求額(税込)</th>
                        <th class="text-center px-4 py-3 text-gray-500 font-medium">状態</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-medium">承認日</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $recentApps = $partner->planApplications()->with('shop')->orderByDesc('created_at')->limit(50)->get();
                        $discount = $effectiveRate;
                    @endphp
                    @forelse($recentApps as $app)
                    @php
                        $billing = $app->status === 'approved'
                            ? (int) round($app->amount * (1 - $discount) * 1.1)
                            : null;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $app->created_at->format('Y/m/d') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $app->shop?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">¥{{ number_format($app->amount) }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $billing ? 'text-purple-700' : 'text-gray-300' }}">
                            {{ $billing ? '¥' . number_format($billing) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($app->status === 'approved')
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">承認済</span>
                            @elseif($app->status === 'pending')
                                <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">審査中</span>
                            @else
                                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">却下</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $app->approved_at?->format('Y/m/d') ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">申請記録はありません</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- 手数料記録 + 手動追加フォーム（紹介代理店のみ） --}}
    @if($partner->isReferral())
    <div>
        <h2 class="text-base font-bold text-gray-700 mb-3">手数料記録</h2>

        {{-- 手動追加 --}}
        <div class="bg-white rounded-xl shadow-sm p-5 mb-4 text-sm">
            <p class="font-medium text-gray-700 mb-3">手数料を手動で記録</p>
            <form action="{{ route('admin.partners.addCommission', $partner) }}/" method="POST" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs text-gray-400 mb-1">対象店舗 <span class="text-red-400">*</span></label>
                    <select name="shop_id" required class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                        <option value="">選択</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">売上金額（円）<span class="text-red-400">*</span></label>
                    <input type="number" name="base_amount" min="1" placeholder="例：30000"
                           class="w-36 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">対象期間（開始）</label>
                    <input type="date" name="period_start" class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">対象期間（終了）</label>
                    <input type="date" name="period_end" class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-xs text-gray-400 mb-1">内容メモ</label>
                    <input type="text" name="description" placeholder="例：2026年5月分掲載料"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </div>
                <div>
                    <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white text-sm px-4 py-1.5 rounded transition">
                        記録（手数料 {{ $partner->commissionRatePercent() }}%）
                    </button>
                </div>
            </form>
        </div>

        {{-- 一覧 + 支払済みマーク --}}
        <form action="{{ route('admin.partners.markPaid', $partner) }}/" method="POST" class="bg-white rounded-xl shadow-sm overflow-hidden">
            @csrf
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 w-8"></th>
                        <th class="text-left px-4 py-3 text-gray-500 font-medium">店舗</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-medium">内容</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-medium">期間</th>
                        <th class="text-right px-4 py-3 text-gray-500 font-medium">売上</th>
                        <th class="text-right px-4 py-3 text-gray-500 font-medium">手数料</th>
                        <th class="text-center px-4 py-3 text-gray-500 font-medium">状態</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($commissions as $c)
                    <tr class="{{ $c->status === 'pending' ? '' : 'bg-gray-50 opacity-60' }}">
                        <td class="px-4 py-3">
                            @if($c->status === 'pending')
                                <input type="checkbox" name="ids[]" value="{{ $c->id }}">
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $c->shop->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $c->description ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            @if($c->period_start)
                                {{ $c->period_start->format('Y/m/d') }}〜{{ $c->period_end?->format('Y/m/d') }}
                            @else —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700">¥{{ number_format($c->base_amount) }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $c->status === 'pending' ? 'text-red-600' : 'text-gray-400' }}">
                            ¥{{ number_format($c->commission_amount) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($c->status === 'paid')
                                <span class="text-xs text-gray-400">支払済 {{ $c->paid_at?->format('m/d') }}</span>
                            @else
                                <span class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded-full">未払い</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">手数料記録はありません</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($commissions->where('status', 'pending')->count() > 0)
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-400">チェックしたものを支払済みにします</p>
                <button type="submit" class="bg-green-700 hover:bg-green-600 text-white text-sm px-4 py-1.5 rounded transition">支払済みにする</button>
            </div>
            @endif
        </form>
        {{ $commissions->links() }}
    </div>
    @endif
</div>
@endsection
