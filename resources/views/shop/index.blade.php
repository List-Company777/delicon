@extends('layouts.app')
@section('title', '店舗一覧')
@section('description', '全国のデリヘル・風俗店を一覧で掲載。店舗のシステム・料金・在籍キャストを詳しく紹介。')
@section('canonical', route('shop.index') . '/')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    <h1 class="text-2xl font-bold text-[#F0ECE4] mb-6 flex items-center gap-3">
        <span class="w-1 h-7 bg-deli-500 rounded-full inline-block"></span>
        デリヘル店舗一覧
    </h1>

    <form method="get" action="{{ route('shop.index') }}/"
          class="bg-surface-600 border border-surface-400 rounded-xl p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-[#7A7A8E] mb-1">店舗タイプ</label>
            <select name="type" class="bg-surface-500 border border-surface-300 text-[#D8D4CC] rounded px-3 py-1.5 text-sm focus:border-deli-500 outline-none">
                <option value="">すべて</option>
                @foreach($shopTypes as $st)
                    <option value="{{ $st->id }}" @selected(request('type') == $st->id) class="bg-surface-500">{{ $st->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-[#7A7A8E] mb-1">キーワード</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="店舗名・エリア"
                   class="bg-surface-500 border border-surface-300 text-[#D8D4CC] placeholder-surface-100 rounded px-3 py-1.5 text-sm focus:border-deli-500 outline-none w-48">
        </div>
        <button type="submit" class="bg-deli-500 hover:bg-deli-400 text-white px-5 py-1.5 rounded text-sm transition font-medium">検索</button>
        @if(request()->hasAny(['type','q']))
        <a href="{{ route('shop.index') }}/" class="text-xs text-[#6A6A7E] hover:text-[#B0AEAD] self-center">リセット</a>
        @endif
    </form>

    {{-- エリアで探す --}}
    @if($areas->isNotEmpty())
    <div class="mb-6">
        <p class="text-xs font-bold text-[#8A8A9E] mb-2 uppercase tracking-wider">エリアで探す</p>
        <div class="flex flex-wrap gap-2">
            @foreach($areas as $area)
            <a href="{{ route('shop.index') }}?area_id={{ $area->id }}"
               class="text-xs bg-surface-500 border border-surface-300 hover:border-deli-500 text-[#C8C4BC] hover:text-deli-400 px-3 py-1.5 rounded-full transition">
                {{ $area->name }}
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <p class="text-xs text-[#6A6A7E] mb-5">{{ $shops->total() }}件</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @forelse($shops as $shop)
        <a href="{{ route('shop.show', $shop->id) }}/"
           class="bg-surface-500 border border-surface-300 hover:border-deli-500 rounded-xl overflow-hidden transition group">
            @if($shop->shop_file_name)
            <div class="relative overflow-hidden">
                <img src="{{ $shop->banner_url }}" alt="{{ $shop->name }}"
                     class="w-full h-36 object-cover opacity-90 group-hover:opacity-100 group-hover:scale-105 transition duration-300"
                     loading="lazy" class="img-onerror-hide">
                <div class="absolute inset-0 bg-gradient-to-t from-surface-900/60 to-transparent"></div>
            </div>
            @else
            <div class="w-full h-36 bg-surface-400 flex items-center justify-center text-[#5A5A7E] text-xs">no image</div>
            @endif
            <div class="p-4">
                <div class="flex items-start gap-2 mb-1">
                    @if($shop->shopType)
                    <span class="text-xs bg-deli-500/20 text-deli-400 border border-deli-500/30 px-2 py-0.5 rounded shrink-0">{{ $shop->shopType->name }}</span>
                    @endif
                    <h2 class="font-bold text-sm text-[#E8E4DC] group-hover:text-gold-400 transition line-clamp-1">{{ $shop->name }}</h2>
                </div>
                @if($shop->catche)
                <p class="text-xs text-[#8A8A9E] mt-1 line-clamp-2">{{ $shop->catche }}</p>
                @endif
                <div class="mt-3 flex items-center justify-between text-xs">
                    <span class="text-[#6A6A7E]">在籍: {{ $shop->cast_members_count }}名</span>
                    @if($shop->price_60)
                    <span class="text-gold-400 font-medium">60分 ¥{{ number_format($shop->price_60) }}〜</span>
                    @endif
                </div>
            </div>
        </a>
        @empty
        <div class="col-span-3 text-center py-16 text-[#6A6A7E]">
            <p>該当する店舗が見つかりませんでした</p>
        </div>
        @endforelse
    </div>

    <div class="[&_.pagination]:flex [&_.pagination]:gap-1 [&_a]:bg-surface-500 [&_a]:border [&_a]:border-surface-300 [&_a]:text-[#B0AEAD] [&_a:hover]:border-deli-500 [&_a:hover]:text-deli-400 [&_a]:px-3 [&_a]:py-1.5 [&_a]:rounded [&_a]:text-sm [&_span.current]:bg-deli-500 [&_span.current]:text-white [&_span.current]:border-deli-500 [&_span.current]:px-3 [&_span.current]:py-1.5 [&_span.current]:rounded [&_span.current]:text-sm">
        {{ $shops->appends(request()->query())->links() }}
    </div>
</div>
@endsection
