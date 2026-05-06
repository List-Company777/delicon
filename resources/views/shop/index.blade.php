@extends('layouts.app')

@section('title', '店舗一覧')
@section('description', '全国のデリヘル・風俗店を一覧で掲載。店舗のシステム・料金・在籍キャストを詳しく紹介。')
@section('canonical', route('shop.index') . '/')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    <h1 class="text-2xl font-bold text-gray-800 mb-6">店舗一覧</h1>

    {{-- 絞り込みフォーム --}}
    <form method="get" action="{{ route('shop.index') }}/" class="bg-white rounded-lg shadow p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">店舗タイプ</label>
            <select name="type" class="border rounded px-2 py-1.5 text-sm">
                <option value="">すべて</option>
                @foreach($shopTypes as $st)
                    <option value="{{ $st->id }}" @selected(request('type') == $st->id)>{{ $st->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">キーワード</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="店舗名・エリア"
                   class="border rounded px-2 py-1.5 text-sm w-48">
        </div>
        <button type="submit" class="bg-red-600 text-white px-4 py-1.5 rounded text-sm hover:bg-red-700 transition">検索</button>
    </form>

    {{-- 件数 --}}
    <p class="text-sm text-gray-500 mb-4">{{ $shops->total() }}件</p>

    {{-- 店舗グリッド --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @forelse($shops as $shop)
        <a href="{{ route('shop.show', $shop->id) }}/" class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden group">
            @if($shop->shop_file_name)
            <img src="/img/{{ ltrim($shop->shop_file_name, '/') }}" alt="{{ $shop->name }}"
                 class="w-full h-36 object-cover" loading="lazy" onerror="this.style.display='none'">
            @else
            <div class="w-full h-36 bg-gray-100 flex items-center justify-center text-gray-400 text-sm">画像なし</div>
            @endif
            <div class="p-3">
                <div class="flex items-start gap-2">
                    @if($shop->shopType)
                    <span class="text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded shrink-0">{{ $shop->shopType->name }}</span>
                    @endif
                    <h2 class="font-bold text-sm group-hover:text-red-600 transition line-clamp-1">{{ $shop->name }}</h2>
                </div>
                @if($shop->catche)
                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $shop->catche }}</p>
                @endif
                <div class="mt-2 flex items-center justify-between text-xs text-gray-400">
                    <span>在籍: {{ $shop->cast_members_count }}名</span>
                    @if($shop->price_60)
                    <span class="text-red-600 font-medium">60分 ¥{{ number_format($shop->price_60) }}〜</span>
                    @endif
                </div>
            </div>
        </a>
        @empty
        <div class="col-span-3 text-center py-16 text-gray-400">
            <p>該当する店舗が見つかりませんでした</p>
        </div>
        @endforelse
    </div>

    {{ $shops->appends(request()->query())->links() }}
</div>
@endsection
