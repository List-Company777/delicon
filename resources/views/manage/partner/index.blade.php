@extends('layouts.app')
@section('title', '紹介店舗一覧')
@section('content')
<div class="bg-business-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <div>
            <p class="text-xs opacity-70">代理店ポータル</p>
            <h1 class="font-bold">{{ $partner->company_name }}</h1>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="text-sm opacity-70 hover:opacity-100">ログアウト</button>
        </form>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-8">
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold text-gray-800">紹介店舗一覧（{{ $shops->count() }}件）</h2>
        <div class="text-sm text-gray-500">
            手数料率：<span class="font-bold text-gray-800">{{ $partner->commissionRatePercent() }}%</span>
        </div>
    </div>

    @if($shops->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-12 text-center text-gray-400">
            <p class="mb-2">紹介店舗がまだありません</p>
            <p class="text-xs">紹介URL：<span class="font-mono text-gray-600">{{ url('/register?ref=' . $partner->referral_code) }}/</span></p>
        </div>
    @else
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">店舗名</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">業種</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">エリア</th>
                    <th class="text-center px-4 py-3 text-gray-500 font-medium">掲載状況</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium w-24">入札単価</th>
                    <th class="px-4 py-3 w-32"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($shops as $shop)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $shop->name }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $shop->genre?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $shop->area?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($shop->status === 'active')
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">掲載中</span>
                        @elseif($shop->status === 'pending')
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">審査中</span>
                        @else
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">非公開</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-sm text-gray-600">
                        {{ number_format($shop->bid_price) }}円
                    </td>
                    <td class="px-4 py-3 text-right">
                        <form action="{{ route('manage.partner.actAs', $shop->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="text-xs bg-business-700 hover:bg-business-600 text-white px-3 py-1.5 rounded transition">
                                管理画面を開く
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-4 bg-gray-50 rounded-lg text-xs text-gray-500">
        <p class="font-medium text-gray-600 mb-1">紹介URL</p>
        <p class="font-mono">{{ url('/register?ref=' . $partner->referral_code) }}/</p>
        <p class="mt-1">このURLから登録した店舗は自動的にあなたの紹介店舗として記録されます</p>
    </div>
    @endif
</div>
@endsection
