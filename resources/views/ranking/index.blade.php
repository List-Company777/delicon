@extends('layouts.app')
@section('title', $pageTitle . ' | デリヘルリスト')
@section('description', $pageDesc)
@section('canonical', $canonical)
@if($noindex)
@section('robots', 'noindex, nofollow')
@endif

@push('head')
@if($ranking->isNotEmpty() && !$noindex)
@php
    $ld_list = [
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => $pageTitle,
        'url'             => $canonical,
        'numberOfItems'   => $ranking->count(),
        'itemListElement' => $ranking->map(fn($cast, $i) => [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'url'      => route('cast.show', $cast->id) . '/',
            'name'     => $cast->name,
        ])->values()->all(),
    ];
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($ld_list, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) !!}</script>
@endif
@endpush

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    {{-- パンくず --}}
    <nav class="text-xs text-[#6A6A7E] mb-4 flex items-center gap-1">
        <a href="/" class="hover:text-deli-400">TOP</a>
        <span>/</span>
        <a href="{{ route('ranking.index') }}/" class="hover:text-deli-400">人気女性ランキング</a>
        @if($pageType === 'pref' || $pageType === 'area')
            <span>/</span>
            @if($pageType === 'area')
                <a href="{{ route('ranking.area', $prefModel->slug) }}/" class="hover:text-deli-400">{{ $prefModel->name }}</a>
                <span>/</span>
            @endif
            <span class="text-[#9A96A0]">{{ $pageTitle }}</span>
        @endif
    </nav>

    <div class="mb-6">
        <h1 class="text-2xl font-black text-[#F0ECE4] flex items-center gap-3">
            <span class="text-2xl">🏆</span> {{ $pageTitle }}
        </h1>
        <p class="text-xs text-[#6A6A7E] mt-1">電話・お気に入り・口コミ・閲覧数をもとに算出（直近7日間）</p>
    </div>

    @if($ranking->isEmpty())
    <p class="text-sm text-[#6A6A7E] mb-8">まだランキングデータがありません。</p>
    @else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 mb-10">
        @foreach($ranking as $i => $cast)
        @php
            $shop    = $cast->shop;
            $bodyStr = collect([
                $cast->bust ? "B{$cast->bust}" . ($cast->cup ? "({$cast->cup})" : '') : null,
                $cast->west ? "W{$cast->west}" : null,
                $cast->hip  ? "H{$cast->hip}"  : null,
            ])->filter()->implode(' ');
            $rank = $i + 1;
            $medalColor = match($rank) {
                1 => 'bg-amber-400 text-amber-900',
                2 => 'bg-gray-300 text-gray-700',
                3 => 'bg-amber-600 text-amber-100',
                default => 'bg-surface-400 text-[#9A96A0]',
            };
        @endphp
        <article class="bg-surface-600 border border-surface-400 hover:border-deli-400 rounded-xl overflow-hidden transition group relative">
            <a href="{{ route('cast.show', $cast) }}/" class="block">
                <div class="absolute top-1.5 left-1.5 z-10">
                    <span class="text-xs font-black px-1.5 py-0.5 rounded {{ $medalColor }}">
                        {{ $rank }}位
                    </span>
                </div>
                @if($cast->isNew())
                <div class="absolute top-1.5 right-1.5 z-10">
                    <span class="text-xs bg-pink-500 text-white font-bold px-1.5 py-0.5 rounded leading-tight">NEW</span>
                </div>
                @endif
                <div class="aspect-[3/4] overflow-hidden bg-surface-500">
                    <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                         loading="{{ $i < 5 ? 'eager' : 'lazy' }}"
                         class="img-onerror-cast w-full h-full object-cover object-top group-hover:scale-105 transition duration-300">
                </div>
                <div class="p-2">
                    <p class="text-sm font-bold text-[#E8E4DC] group-hover:text-gold-400 transition truncate">{{ $cast->name }}</p>
                    @if($cast->age || $cast->tall)
                    <p class="text-xs text-[#B0AEAD] mt-0.5">{{ $cast->age ? $cast->age.'歳' : '' }}{{ ($cast->age && $cast->tall) ? ' ' : '' }}{{ $cast->tall ? $cast->tall.'cm' : '' }}</p>
                    @endif
                    @if($bodyStr)
                    <p class="text-xs text-[#8A8A9E]">{{ $bodyStr }}</p>
                    @endif
                    @if($shop)
                    <p class="text-xs text-[#8A8A9E] mt-0.5 truncate">{{ $shop->name }}</p>
                    @endif
                </div>
            </a>
        </article>
        @endforeach
    </div>
    @endif

    {{-- ナビゲーションリンク --}}
    @if(!empty($navLinks))
    <section class="mt-4">
        <h2 class="text-sm font-bold text-[#9A96A0] mb-3">{{ $navLabel }}</h2>
        <div class="flex flex-wrap gap-2">
            @foreach($navLinks as $link)
            <a href="{{ route('ranking.area', $link['slug']) }}/"
               class="text-xs px-3 py-1.5 rounded-full bg-surface-600 border border-surface-400 text-[#B0AEAD] hover:border-deli-400 hover:text-deli-400 transition">
                {{ $link['name'] }}
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- 全国ページへの戻りリンク（都道府県・エリアページ） --}}
    @if($pageType !== 'all')
    <div class="mt-6 text-center">
        <a href="{{ route('ranking.index') }}/" class="text-xs text-[#6A6A7E] hover:text-deli-400 transition">
            ← 全国ランキングに戻る
        </a>
    </div>
    @endif

</div>
@endsection
