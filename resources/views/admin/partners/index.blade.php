@extends('layouts.admin')
@section('title', 'パートナー管理')
@section('content')
<div class="bg-gray-800 text-white py-4">
    <div class="max-w-6xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold">Admin › パートナー管理</h1>
        <a href="{{ route('admin.partners.create') }}/" class="bg-white text-gray-800 text-sm font-bold px-4 py-1.5 rounded hover:bg-gray-100">＋ 新規登録</a>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-8">
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">会社名 / 担当者</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">種別</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">紹介コード</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">率</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">管理店舗数</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">当月（未払い）</th>
                    <th class="text-center px-4 py-3 text-gray-500 font-medium">ステータス</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php $now = now(); @endphp
                @forelse($partners as $p)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $p->company_name }}</p>
                        @if($p->contact_name)<p class="text-xs text-gray-400">{{ $p->contact_name }}</p>@endif
                    </td>
                    <td class="px-4 py-3">
                        @if($p->isManagement())
                            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full whitespace-nowrap">管理代行</span>
                        @else
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full whitespace-nowrap">紹介</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-mono text-gray-600">{{ $p->referral_code }}</td>
                    <td class="px-4 py-3 text-right font-medium">{{ $p->commissionRatePercent() }}%</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ $p->shops_count }}店舗</td>
                    <td class="px-4 py-3 text-right">
                        @if($p->isManagement())
                            @php $billing = $p->billingAmountForMonth($now->year, $now->month); @endphp
                            <span class="{{ $billing > 0 ? 'text-purple-700 font-bold' : 'text-gray-400' }}">
                                ¥{{ number_format($billing) }}
                            </span>
                        @else
                            @php $pending = $p->pendingAmount(); @endphp
                            <span class="{{ $pending > 0 ? 'text-red-600 font-bold' : 'text-gray-400' }}">
                                ¥{{ number_format($pending) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($p->status === 'active')
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">有効</span>
                        @else
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">無効</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                        <a href="{{ route('admin.partners.show', $p) }}/" class="text-xs text-business-700 hover:underline">詳細</a>
                        <a href="{{ route('admin.partners.edit', $p) }}/" class="text-xs text-gray-500 hover:underline">編集</a>
                        @if($p->isManagement())
                            <a href="{{ route('admin.partners.downloadCsv', $p) }}/" class="text-xs text-purple-600 hover:underline">CSV</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">パートナーはまだ登録されていません</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
