@extends('layouts.admin')
@section('title', sprintf('月次取引明細 %d年%d月', $year, $month))
@section('content')
<div class="bg-gray-800 text-white py-4">
    <div class="max-w-6xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold">Admin › 月次取引明細</h1>
        <form method="GET" action="{{ route('admin.billing.index') }}/" class="flex items-center gap-2 flex-wrap">
            <select name="year" class="bg-gray-700 text-white border border-gray-600 rounded px-2 py-1 text-sm">
                @for($y = now()->year; $y >= now()->year - 1; $y--)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}年</option>
                @endfor
            </select>
            <select name="month" class="bg-gray-700 text-white border border-gray-600 rounded px-2 py-1 text-sm">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ $m }}月</option>
                @endfor
            </select>
            <select name="partner_id" class="bg-gray-700 text-white border border-gray-600 rounded px-2 py-1 text-sm">
                <option value="">代理店：すべて</option>
                <option value="direct" {{ $partnerId === 'direct' ? 'selected' : '' }}>直接のみ</option>
                @foreach($partners as $p)
                    <option value="{{ $p->id }}" {{ $partnerId == $p->id ? 'selected' : '' }}>
                        [{{ $p->type === 'management' ? '管理' : '紹介' }}] {{ $p->company_name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="bg-yellow-400 text-gray-900 text-sm font-bold px-3 py-1 rounded hover:bg-yellow-300 transition">表示</button>
        </form>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-8 space-y-6">

    {{-- サマリーカード --}}
    @php
        $grandTotal = array_sum(array_column($rows, 'total'));
        $grandAmount = array_sum(array_column($rows, 'amount'));
    @endphp
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-400 mb-1">直接入金</p>
            <p class="text-xl font-bold text-gray-800">¥{{ number_format($summary['direct']['total']) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $summary['direct']['count'] }}件</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-400 mb-1">紹介代理店経由</p>
            <p class="text-xl font-bold text-gray-800">¥{{ number_format($summary['referral']['total']) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $summary['referral']['count'] }}件（手数料は別払い）</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-400 mb-1">管理代行代理店経由</p>
            <p class="text-xl font-bold text-purple-700">¥{{ number_format($summary['management']['total']) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $summary['management']['count'] }}件（税込請求額）</p>
        </div>
        <div class="bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-300 mb-1">当月合計収入（税込）</p>
            <p class="text-xl font-bold text-yellow-400">¥{{ number_format($grandTotal) }}</p>
            <p class="text-xs text-gray-400 mt-1">予算追加合計 ¥{{ number_format($grandAmount) }}</p>
        </div>
    </div>

    {{-- CSVダウンロード --}}
    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.billing.csv', array_filter(['year' => $year, 'month' => $month, 'partner_id' => $partnerId ?: null])) }}/"
           class="bg-green-700 hover:bg-green-600 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
            CSVダウンロード（{{ $year }}年{{ $month }}月）
        </a>
        <a href="{{ route('admin.billing.invoy-csv', ['year' => $year, 'month' => $month]) }}/"
           class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
            invoy用CSVダウンロード（{{ $year }}年{{ $month }}月）
        </a>
    </div>

    {{-- 明細テーブル --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">承認日</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">店舗名</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">区分</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">代理店名</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">予算追加額</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">請求金額(税込)</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">備考</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($rows as $r)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $r['approved_at'] }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $r['shop_name'] }}</td>
                    <td class="px-4 py-3">
                        @if($r['type'] === 'management')
                            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full whitespace-nowrap">管理代行</span>
                        @elseif($r['type'] === 'referral')
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full whitespace-nowrap">紹介</span>
                        @else
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full whitespace-nowrap">直接</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $r['partner_name'] }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">¥{{ number_format($r['amount']) }}</td>
                    <td class="px-4 py-3 text-right font-medium
                        @if($r['type'] === 'management') text-purple-700
                        @elseif($r['type'] === 'referral') text-blue-700
                        @else text-gray-800 @endif">
                        ¥{{ number_format($r['total']) }}
                        @if($r['tax'] > 0)
                            <span class="text-xs text-gray-400 font-normal">（税{{ number_format($r['tax']) }}）</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400">{{ $r['note'] }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">{{ $year }}年{{ $month }}月の承認済み申請はありません</td></tr>
                @endforelse
            </tbody>
            @if(count($rows) > 0)
            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                <tr>
                    <td colspan="4" class="px-4 py-3 text-sm font-bold text-gray-700">合計 {{ count($rows) }}件</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-700">¥{{ number_format($grandAmount) }}</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-800">¥{{ number_format($grandTotal) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

</div>
@endsection
