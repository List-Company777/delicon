@extends('layouts.admin')

@section('title', '代理店移管')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-700">代理店移管</h1>
        <p class="text-sm text-gray-400 mt-0.5">未割り当て: {{ number_format($unassignedCount) }}件</p>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
        {{ session('success') }}
    </div>
@endif

{{-- フィルター --}}
<form method="GET" action="{{ route('admin.partner-transfer.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-400 mb-1">現在の代理店</label>
        <select name="partner_filter"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500 min-w-[200px]">
            <option value="all"        {{ $filterPartner === 'all'        ? 'selected' : '' }}>すべて</option>
            <option value="unassigned" {{ $filterPartner === 'unassigned' ? 'selected' : '' }}>未割り当て</option>
            @foreach($partners as $p)
            <option value="{{ $p->id }}" {{ $filterPartner == $p->id ? 'selected' : '' }}>
                {{ $p->company_name }}（{{ $p->type === 'management' ? '管理代行' : '紹介' }}）
            </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-400 mb-1">店舗名</label>
        <input type="text" name="keyword" value="{{ $keyword }}" placeholder="店舗名で絞り込み"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500 w-56">
    </div>
    <button type="submit" class="px-4 py-2 bg-gray-700 text-white text-sm rounded-lg hover:bg-gray-600 transition">絞り込み</button>
    @if($keyword || $filterPartner !== 'all')
        <a href="{{ route('admin.partner-transfer.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">クリア</a>
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
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-20">業種</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-24">エリア</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-16">状態</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-36">現在の代理店</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">移管先を選択して移管</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($shops as $shop)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $shop->id }}</td>
                <td class="px-4 py-3 font-medium">
                    <a href="{{ route('admin.shops.show', $shop->id) }}"
                       class="text-gray-800 hover:text-business-700 hover:underline">{{ $shop->name }}</a>
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $shop->genre?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">
                    {{ $shop->prefecture?->name ?? '' }}{{ ($shop->area?->name ? '・' . $shop->area->name : '') ?: '—' }}
                </td>
                <td class="px-4 py-3">
                    @php
                        $statusMap = ['active' => ['掲載中','green'], 'pending' => ['審査中','yellow'], 'inactive' => ['非公開','gray']];
                        [$statusLabel, $statusColor] = $statusMap[$shop->status] ?? ['—','gray'];
                    @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $statusColor === 'green' ? 'bg-green-100 text-green-700' : ($statusColor === 'yellow' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500') }}">
                        {{ $statusLabel }}
                    </span>
                </td>
                <td class="px-4 py-3 text-xs">
                    @if($shop->partner)
                        <span class="text-business-700 font-medium">{{ $shop->partner->company_name }}</span>
                        <span class="text-gray-400 ml-1">{{ $shop->partner->type === 'management' ? '管理代行' : '紹介' }}</span>
                    @else
                        <span class="text-gray-300">未割り当て</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('admin.shops.updatePartner', $shop->id) }}"
                          class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <select name="partner_id"
                                class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:border-business-500 min-w-[180px]">
                            <option value="">— 解除 —</option>
                            @foreach($partners as $p)
                            <option value="{{ $p->id }}" {{ $shop->partner_id == $p->id ? 'selected' : '' }}>
                                {{ $p->company_name }}（{{ $p->type === 'management' ? '管理代行' : '紹介' }}）
                            </option>
                            @endforeach
                        </select>
                        <button type="submit"
                                class="px-3 py-1.5 bg-business-700 hover:bg-business-600 text-white text-xs rounded-lg transition whitespace-nowrap">
                            移管
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ページネーション --}}
@if($shops->hasPages())
<div class="mt-4">
    {{ $shops->links() }}
</div>
@endif
@endif

@endsection
