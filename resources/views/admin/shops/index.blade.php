@extends('layouts.admin')

@section('title', '店舗審査')

@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-700">店舗審査</h1>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
        {{ session('success') }}
    </div>
@endif

{{-- タブ --}}
<div class="flex gap-1 mb-6 border-b border-gray-200">
    @foreach(['pending' => '申請中', 'active' => '掲載中', 'inactive' => '非公開', 'all' => 'すべて'] as $s => $label)
    <a href="{{ route('admin.shops.index', ['status' => $s]) }}/"
       class="{{ $status === $s
           ? 'border-b-2 border-yellow-500 text-yellow-600 font-bold'
           : 'text-gray-500 hover:text-gray-700' }}
          px-4 py-2 text-sm transition -mb-px whitespace-nowrap">
        {{ $label }}
        <span class="ml-1 text-xs {{ $status === $s ? 'text-yellow-500' : 'text-gray-400' }}">
            {{ number_format($counts[$s]) }}
        </span>
    </a>
    @endforeach
</div>

{{-- 店舗名検索 --}}
<form method="GET" action="{{ route('admin.shops.index') }}/" class="mb-4 flex gap-2">
    <input type="hidden" name="status" value="{{ $status }}">
    <input type="text" name="keyword" value="{{ $keyword }}" placeholder="店舗名で絞り込み"
           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500 w-64">
    <button type="submit" class="px-4 py-2 bg-gray-700 text-white text-sm rounded-lg hover:bg-gray-600 transition">検索</button>
    @if($keyword)
        <a href="{{ route('admin.shops.index', ['status' => $status]) }}/" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">クリア</a>
    @endif
</form>

@if($shops->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center text-gray-400">
        <p>該当する店舗はありません</p>
    </div>
@else
<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-10">ID</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">店舗名</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">業種</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">エリア</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">担当者</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-28">更新日</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-20">状態</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-32">入札単価</th>
                <th class="px-4 py-3 w-36"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($shops as $shop)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $shop->id }}</td>
                <td class="px-4 py-3 font-medium">
                    <a href="{{ route('admin.shops.show', $shop->id) }}/" class="text-gray-800 hover:text-business-700 hover:underline">{{ $shop->name }}</a>
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $shop->genre?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $shop->area?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-xs">
                    @php $owner = $shop->users->first(); @endphp
                    @if($owner)
                        <p class="text-gray-700">{{ $owner->name }}</p>
                        <p class="text-gray-400">{{ $owner->email }}</p>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-gray-400">
                    {{ $shop->updated_at->format('Y/m/d H:i') }}
                </td>
                <td class="px-4 py-3">
                    @if($shop->status === 'pending')
                        <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 border border-yellow-200 rounded-full">申請中</span>
                    @elseif($shop->status === 'active')
                        <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 border border-green-200 rounded-full">掲載中</span>
                    @else
                        <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 border border-gray-200 rounded-full">非公開</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if($shop->status === 'active')
                        <form action="{{ route('admin.shops.updateBidPrice', $shop->id) }}/" method="POST" class="flex items-center gap-1">
                            @csrf @method('PATCH')
                            <input type="number" name="bid_price" value="{{ $shop->bid_price }}"
                                   min="10" max="9990" step="10"
                                   class="w-20 border border-gray-300 rounded px-2 py-1 text-xs text-right focus:outline-none focus:border-business-500">
                            <span class="text-xs text-gray-400">円</span>
                            <button type="submit" class="text-xs text-business-700 hover:underline px-1">更新</button>
                        </form>
                    @else
                        <span class="text-xs text-gray-400">無料（10円）</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex gap-2">
                        @if($shop->status !== 'active')
                            <form action="{{ route('admin.shops.approve', $shop->id) }}/" method="POST">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1 bg-green-500 hover:bg-green-400 text-white text-xs rounded transition font-medium">
                                    承認
                                </button>
                            </form>
                        @endif
                        @if($shop->status !== 'inactive')
                            <form action="{{ route('admin.shops.reject', $shop->id) }}/" method="POST">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-600 text-xs rounded transition"
                                        onclick="return confirm('「{{ $shop->name }}」を非公開にしますか？')">
                                    却下
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($shops->hasPages())
<div class="mt-6">
    {{ $shops->appends(request()->query())->links() }}
</div>
@endif
@endif

@endsection
