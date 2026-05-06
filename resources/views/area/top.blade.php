@extends('layouts.app')

@php
    $suffix    = 'デリヘル・風俗';
    $pageTitle = $area_slug === 'all'
        ? "全国の{$suffix}情報"
        : "{$areaName}の{$suffix}情報";
@endphp

@section('title', $pageTitle)
@section('description', "{$areaName}のデリヘル・風俗店舗{$totalShops}件を掲載。エリア・ジャンル・キャストタイプから探せるデリヘル情報サイト。")
@section('robots', $noindex ? 'noindex,follow' : 'index,follow')
@section('canonical', url("/{$area_slug}/") . '/')
@push('head')
@php
    $bc = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type'=>'ListItem','position'=>1,'name'=>'ホーム','item'=>route('top').'/'],
        ],
    ];
    if ($area_slug !== 'all') {
        $bc['itemListElement'][] = ['@type'=>'ListItem','position'=>2,'name'=>'全国','item'=>route('area.top',['area_slug'=>'all']).'/'];
        $bc['itemListElement'][] = ['@type'=>'ListItem','position'=>3,'name'=>$areaName,'item'=>url("/{$area_slug}/").'/'];
    } else {
        $bc['itemListElement'][] = ['@type'=>'ListItem','position'=>2,'name'=>'全国','item'=>route('area.top',['area_slug'=>'all']).'/'];
    }
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($bc, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) !!}</script>
@endpush

@section('content')

{{-- ヘッダー --}}
<div class="bg-surface-800 border-b border-surface-400">
    <div class="max-w-6xl mx-auto px-4 py-4">
        <div class="flex items-baseline gap-3">
            <h1 class="text-xl font-bold text-[#E8E4DC]">{{ $pageTitle }}</h1>
            <span class="text-sm text-[#8A8A9E]">{{ number_format($totalShops) }}件掲載</span>
        </div>
        <nav class="text-xs text-[#6A6A7E] mt-1.5 flex items-center gap-1 flex-wrap">
            <a href="{{ route('top') }}/" class="hover:text-gold-400 transition">TOP</a>
            <span>›</span>
            @if($area_slug !== 'all')
            <a href="{{ route('area.top', ['area_slug' => 'all']) }}/" class="hover:text-gold-400 transition">全国</a>
            <span>›</span>
            <span>{{ $areaName }}</span>
            @else
            <span>全国</span>
            @endif
        </nav>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-6 space-y-10">

    {{-- 有料店舗バナーグリッド --}}
    @if($featuredShops->isNotEmpty())
    <section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-[#E8E4DC] flex items-center gap-2">
                <span class="w-1 h-5 bg-deli-500 rounded-full inline-block"></span>
                掲載店舗
            </h2>
            <a href="{{ route('shop.list', ['area_slug' => $area_slug]) }}/" class="text-xs text-deli-400 hover:text-deli-300 transition">
                すべて見る →
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($featuredShops as $shop)
            <a href="{{ route('shop.show', $shop->id) }}/"
               class="bg-surface-500 border border-surface-300 hover:border-deli-500 rounded-xl overflow-hidden transition group block">
                <div class="relative aspect-[5/2] bg-gradient-to-br from-surface-400 to-surface-600 overflow-hidden">
                    @if($shop->banner_url)
                    <img src="{{ $shop->banner_url }}"
                         alt="{{ $shop->name }}のデリヘル情報"
                         loading="lazy"
                         class="img-onerror-hide absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-300 opacity-90 group-hover:opacity-100">
                    @else
                    <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-gold-400 text-2xl opacity-30">✦</span>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-surface-900/80 via-transparent to-transparent"></div>
                    @if($shop->shopType)
                    <span class="absolute top-2 left-2 bg-deli-500/90 text-white text-xs px-2 py-0.5 rounded-full">{{ $shop->shopType->name }}</span>
                    @endif
                    <p class="absolute bottom-2 left-2 right-2 text-[#E8E4DC] text-xs font-bold line-clamp-1 drop-shadow-md">{{ $shop->name }}</p>
                </div>
                <div class="p-3">
                    <p class="font-bold text-sm text-[#E8E4DC] group-hover:text-gold-400 transition line-clamp-1">{{ $shop->name }}</p>
                    @if($shop->price_60)
                    <p class="text-xs text-gold-400 font-medium mt-1">60分¥{{ number_format($shop->price_60) }}〜</p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ジャンルから探す --}}
    @if($shopTypeCounts->isNotEmpty())
    <section>
        <h2 class="text-base font-bold text-[#E8E4DC] flex items-center gap-2 mb-4">
            <span class="w-1 h-5 bg-gold-400 rounded-full inline-block"></span>
            ジャンルから探す
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach($shopTypeCounts as $type)
            <a href="{{ route('shop.list.filter', ['area_slug' => $area_slug, 'filter_slug' => $type->slug]) }}/"
               class="bg-surface-600 hover:bg-surface-500 border border-surface-400 hover:border-deli-400 rounded-lg px-4 py-3 transition group">
                <p class="text-sm text-[#E8E4DC] group-hover:text-gold-400 transition font-medium">{{ $type->name }}</p>
                <p class="text-xs text-[#6A6A7E] mt-0.5">{{ number_format($type->cnt) }}件</p>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- タイプで探す --}}
    <section>
        <h2 class="text-base font-bold text-[#E8E4DC] flex items-center gap-2 mb-4">
            <span class="w-1 h-5 bg-surface-200 rounded-full inline-block"></span>
            タイプで探す
        </h2>
        <div class="flex flex-wrap gap-2">
            @foreach([
                ['slug'=>'kirei',    'name'=>'キレイ系'],
                ['slug'=>'kawaii',   'name'=>'カワイイ系'],
                ['slug'=>'sexy',     'name'=>'セクシー系'],
                ['slug'=>'jukujo',   'name'=>'熟女系'],
                ['slug'=>'hitozuma', 'name'=>'人妻系'],
                ['slug'=>'model',    'name'=>'モデル系'],
                ['slug'=>'rori',     'name'=>'ロリ系'],
                ['slug'=>'gal',      'name'=>'ギャル系'],
                ['slug'=>'oneesan',  'name'=>'お姉さん系'],
                ['slug'=>'iyashi',   'name'=>'癒し系'],
            ] as $type)
            <a href="{{ route('girl.list.type', ['area_slug' => $area_slug, 'type_slug' => $type['slug']]) }}/"
               class="px-3 py-1.5 rounded-full text-xs border border-surface-400 text-[#8A8A9E] hover:border-deli-400 hover:text-deli-400 transition">
                {{ $type['name'] }}
            </a>
            @endforeach
        </div>
    </section>

    {{-- クイックリンク --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('shop.list', ['area_slug' => $area_slug]) }}/"
           class="flex items-center justify-between bg-surface-600 hover:bg-surface-500 border border-surface-400 hover:border-deli-400 rounded-xl px-5 py-4 transition group">
            <div>
                <p class="text-sm font-bold text-[#E8E4DC] group-hover:text-gold-400 transition">店舗一覧</p>
                <p class="text-xs text-[#6A6A7E] mt-0.5">{{ $areaName }}のデリヘル・風俗店 {{ number_format($totalShops) }}件</p>
            </div>
            <span class="text-[#6A6A7E] text-lg">›</span>
        </a>
        <a href="{{ route('girl.list', ['area_slug' => $area_slug]) }}/"
           class="flex items-center justify-between bg-surface-600 hover:bg-surface-500 border border-surface-400 hover:border-deli-400 rounded-xl px-5 py-4 transition group">
            <div>
                <p class="text-sm font-bold text-deli-400 group-hover:text-deli-300 transition">キャスト一覧</p>
                <p class="text-xs text-[#6A6A7E] mt-0.5">{{ $areaName }}の在籍キャストを探す</p>
            </div>
            <span class="text-[#6A6A7E] text-lg">›</span>
        </a>
    </section>

</div>
@endsection
