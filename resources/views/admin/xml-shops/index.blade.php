@extends('layouts.admin')

@section('title', 'XML連携店舗')

@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-700">XML連携店舗</h1>
</div>

{{-- アカウントフィルタータブ --}}
<div class="flex gap-1 mb-4 border-b border-gray-200">
    <a href="{{ route('admin.xml-shops.index', array_filter(['source' => $source ?: null, 'keyword' => $keyword ?: null])) }}"
       class="{{ $filter === 'all' ? 'border-b-2 border-yellow-500 text-yellow-600 font-bold' : 'text-gray-500 hover:text-gray-700' }} px-4 py-2 text-sm transition -mb-px whitespace-nowrap">
        すべて
        <span class="ml-1 text-xs {{ $filter === 'all' ? 'text-yellow-500' : 'text-gray-400' }}">{{ number_format($totalCount) }}</span>
    </a>
    <a href="{{ route('admin.xml-shops.index', array_filter(['filter' => 'no_account', 'source' => $source ?: null, 'keyword' => $keyword ?: null])) }}"
       class="{{ $filter === 'no_account' ? 'border-b-2 border-yellow-500 text-yellow-600 font-bold' : 'text-gray-500 hover:text-gray-700' }} px-4 py-2 text-sm transition -mb-px whitespace-nowrap">
        アカウントなし
        <span class="ml-1 text-xs {{ $filter === 'no_account' ? 'text-yellow-500' : 'text-gray-400' }}">{{ number_format($noAccountCount) }}</span>
    </a>
</div>

{{-- 連携元・店舗名フィルタ --}}
<div class="flex flex-wrap items-center gap-3 mb-6">
    {{-- xml_source タブ --}}
    <div class="flex gap-1">
        <a href="{{ route('admin.xml-shops.index', array_filter(['filter' => $filter !== 'all' ? $filter : null, 'keyword' => $keyword ?: null])) }}"
           class="px-3 py-1.5 text-xs rounded-full border transition
               {{ $source === '' ? 'bg-gray-700 text-white border-gray-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
            すべての連携元
        </a>
        @foreach($sources as $src)
        <a href="{{ route('admin.xml-shops.index', array_filter(['filter' => $filter !== 'all' ? $filter : null, 'source' => $src, 'keyword' => $keyword ?: null])) }}"
           class="px-3 py-1.5 text-xs rounded-full border transition font-mono
               {{ $source === $src ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
            {{ $src }}
        </a>
        @endforeach
    </div>

    {{-- 店舗名検索 --}}
    <form method="GET" action="{{ route('admin.xml-shops.index') }}" class="flex gap-2 ml-auto">
        @if($filter !== 'all') <input type="hidden" name="filter" value="{{ $filter }}"> @endif
        @if($source !== '') <input type="hidden" name="source" value="{{ $source }}"> @endif
        <input type="text" name="keyword" value="{{ $keyword }}" placeholder="店舗名で絞り込み"
               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-business-500 w-52">
        <button type="submit" class="px-3 py-1.5 bg-gray-700 text-white text-sm rounded-lg hover:bg-gray-600 transition">検索</button>
        @if($keyword)
            <a href="{{ route('admin.xml-shops.index', array_filter(['filter' => $filter !== 'all' ? $filter : null, 'source' => $source ?: null])) }}"
               class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700">クリア</a>
        @endif
    </form>
</div>

@if($shops->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center text-gray-400">
        <p>該当する店舗はありません</p>
    </div>
@else
<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 w-8">ID</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">店舗名</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">業種</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">エリア</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">連携元</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">ステータス</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">アカウント</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($shops as $shop)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-xs text-gray-400">{{ $shop->id }}</td>
                <td class="px-4 py-3">
                    <a href="{{ route('admin.shops.show', $shop->id) }}" class="font-medium text-gray-800 hover:text-yellow-600 transition">
                        {{ $shop->name }}
                    </a>
                </td>
                <td class="px-4 py-3 text-gray-500">{{ $shop->genre?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-500">
                    {{ $shop->prefecture?->name }}{{ $shop->area ? '　' . $shop->area->name : '' }}
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-mono">{{ $shop->xml_source }}</span>
                </td>
                <td class="px-4 py-3">
                    @if($shop->status === 'active')
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">公開中</span>
                    @elseif($shop->status === 'pending')
                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded">審査待ち</span>
                    @else
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded">非公開</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if($shop->users_count > 0)
                        <div class="flex flex-col gap-0.5">
                            @foreach($shop->users as $user)
                                <span class="text-xs text-gray-700">{{ $user->name }}
                                    <span class="text-gray-400">{{ $user->email }}</span>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-xs text-red-400 font-medium">未登録</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $shops->links() }}
</div>
@endif

@endsection
