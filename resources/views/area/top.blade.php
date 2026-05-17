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
@if($featuredShops->isNotEmpty() && !$noindex)
@php
    $ld_list = [
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => "{$areaName}のデリヘル・風俗店舗",
        'url'             => url("/{$area_slug}/") . '/',
        'numberOfItems'   => $totalShops,
        'itemListElement' => $featuredShops->map(fn($shop, $i) => [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'url'      => route('shop.show', $shop->id) . '/',
            'name'     => $shop->name,
        ])->values()->all(),
    ];
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($ld_list, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) !!}</script>
@endif
@php
    $lcpImg = null;
    if ($pickupShops->isNotEmpty()) {
        $lcpImg = $pickupShops->first()->main_image_url ?? null;
    }
    if (!$lcpImg && $featuredShops->isNotEmpty()) {
        $ff = $featuredShops->first();
        if ($ff->main_image) $lcpImg = \Illuminate\Support\Facades\Storage::url($ff->main_image);
    }
@endphp
@if($lcpImg)
<link rel="preload" as="image" href="{{ $lcpImg }}" fetchpriority="high">
@endif
@endpush

@section('content')


{{-- ヘッダー --}}
<div class="bg-surface-800 border-b border-surface-400">
    <div class="max-w-6xl mx-auto px-4 py-4">
        <div class="flex items-baseline gap-3">
            <h1 class="text-xl font-bold text-[#E8E4DC]">{{ $pageTitle }}</h1>
            <span class="text-sm text-[#B0AEAD]">{{ number_format($totalShops) }}件掲載</span>
        </div>
        <nav aria-label="パンくずリスト" class="text-xs text-[#8A8A9E] mt-1.5">
            <ol class="flex flex-wrap items-center gap-1 list-none m-0 p-0">
            <li><a href="{{ route('top') }}/" class="hover:text-gold-400 transition">TOP</a></li>
            <li aria-hidden="true">›</li>
            @if($area_slug !== 'all')
            <li><a href="{{ route('area.top', ['area_slug' => 'all']) }}/" class="hover:text-gold-400 transition">全国</a></li>
            <li aria-hidden="true">›</li>
            <li aria-current="page"><span>{{ $areaName }}</span></li>
            @else
            <li aria-current="page"><span>全国</span></li>
            @endif
            </ol>
        </nav>
    </div>
</div>

{{-- ① ピックアップ店舗（plan 1-2 横並び大カード） --}}
@if($pickupShops->isNotEmpty())
<section class="bg-surface-700 border-b border-surface-500">
    <div class="max-w-6xl mx-auto px-4 py-5">
        <h2 class="text-sm font-bold text-[#E8E4DC] flex items-center gap-2 mb-3">
            <span aria-hidden="true" class="w-1 h-4 bg-gold-400 rounded-full inline-block"></span>
            ピックアップ
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @foreach($pickupShops as $loop_shop => $shop)
            <a href="{{ route('shop.show', $shop->id) }}/"
               class="group flex bg-surface-600 border border-surface-400 hover:border-gold-400 rounded-xl overflow-hidden transition">
                <div class="w-28 flex-shrink-0 overflow-hidden">
                    <img src="{{ $shop->main_image_url }}" alt="{{ $shop->name }}"
                         @if($loop->first) fetchpriority="high" loading="eager" @else loading="lazy" @endif
                         class="img-onerror-hide w-full h-full object-cover group-hover:scale-105 transition duration-300">
                </div>
                <div class="p-3 flex flex-col min-w-0">
                    @if($shop->shop_type_name)
                    <span class="text-[10px] text-deli-400 font-medium">{{ $shop->shop_type_name }}</span>
                    @endif
                    <p class="text-sm font-bold text-[#E8E4DC] group-hover:text-gold-400 transition line-clamp-1 mt-0.5">{{ $shop->name }}</p>
                    @if($shop->catche)
                    <p class="text-xs text-[#8A8A9E] line-clamp-2 mt-1">{{ $shop->catche }}</p>
                    @endif
                    <div class="flex items-center gap-3 mt-auto pt-1 flex-wrap">
                        @if($shop->price_60)
                        <span class="text-xs text-gold-400 font-medium">60分¥{{ number_format($shop->price_60) }}〜</span>
                        @endif
                        <span class="text-xs text-[#8A8A9E]">{{ $shop->cast_count }}名在籍</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ⑥ バナー広告（横3列） --}}
@if($bannerShops->isNotEmpty())
<aside class="bg-surface-700 border-b border-surface-500" aria-label="広告">
    <div class="max-w-6xl mx-auto px-4 py-5">
        <h2 class="text-sm font-bold text-[#E8E4DC] flex items-center gap-2 mb-3">
            <span aria-hidden="true" class="w-1 h-4 bg-surface-200 rounded-full inline-block"></span>
            広告
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @foreach($bannerShops as $shop)
            <a href="{{ route('shop.show', $shop->id) }}/"
               class="block hover:opacity-80 transition rounded overflow-hidden"
               title="{{ $shop->name }}">
                <picture>
                    @if($shop->banner_webp_url)
                    <source srcset="{{ $shop->banner_webp_url }}" type="image/webp">
                    @endif
                    <img src="{{ $shop->banner_url }}" alt="{{ $shop->name }}"
                         loading="lazy" width="468" height="60"
                         class="img-onerror-hide w-full h-auto">
                </picture>
            </a>
            @endforeach
        </div>
    </div>
</aside>
@endif

<div class="max-w-6xl mx-auto px-4 py-6 space-y-10">

    {{-- ⑤ 新人デビュー --}}
    @if($recentCasts->isNotEmpty())
    <section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-[#E8E4DC] flex items-center gap-2">
                <span aria-hidden="true" class="w-1 h-5 bg-gold-400 rounded-full inline-block"></span>
                新人デビュー
            </h2>
            <a href="{{ route('girl.list', ['area_slug' => $area_slug]) }}/" class="text-xs text-deli-400 hover:text-deli-300 transition">もっと見る →</a>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
            @foreach($recentCasts as $cast)
            <a href="{{ route('cast.show', $cast->id) }}/" class="group flex-1 min-w-0">
                <div class="relative aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 mb-1.5 border border-surface-300 group-hover:border-gold-400 transition">
                    <picture>
                        @if($cast->img_webp_url)
                        <source srcset="{{ $cast->img_webp_url }}" type="image/webp">
                        @endif
                        <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                             loading="lazy"
                             class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition duration-300 opacity-90 group-hover:opacity-100">
                    </picture>
                    <span class="absolute top-1.5 left-1.5 bg-gold-400 text-surface-800 text-[10px] font-bold px-1.5 py-0.5 rounded">NEW</span>
                </div>
                <p class="text-xs font-medium text-[#D8D4CC] group-hover:text-gold-400 transition truncate">{{ $cast->name }}</p>
                <p class="text-xs text-[#8A8A9E]">{{ $cast->join_date }}</p>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ③ 掲載店舗グリッド --}}
    @if($featuredShops->isNotEmpty())
    <section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-[#E8E4DC] flex items-center gap-2">
                <span aria-hidden="true" class="w-1 h-5 bg-deli-500 rounded-full inline-block"></span>
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
                    @if($shop->main_image)
                    <img src="{{ Storage::url($shop->main_image) }}"
                         alt="{{ $shop->name }}のデリヘル情報"
                         @if($loop->first) loading="eager" fetchpriority="high" @else loading="lazy" @endif
                         class="img-onerror-hide absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-300 opacity-90 group-hover:opacity-100">
                    @else
                    <span aria-hidden="true" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-gold-400 text-2xl opacity-30">✦</span>
                    @endif
                    <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-t from-surface-900/80 via-transparent to-transparent"></div>
                    @if($shop->shop_type_name)
                    <span class="absolute top-2 left-2 bg-deli-500/90 text-white text-xs px-2 py-0.5 rounded-full">{{ $shop->shop_type_name }}</span>
                    @endif
                    <p aria-hidden="true" class="absolute bottom-2 left-2 right-2 text-[#E8E4DC] text-xs font-bold line-clamp-1 drop-shadow-md">{{ $shop->name }}</p>
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

    {{-- ⑥ 本日出勤中（横一列） --}}
    @if($workingCasts->isNotEmpty())
    <section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-[#E8E4DC] flex items-center gap-2">
                <span aria-hidden="true" class="w-1 h-5 bg-deli-500 rounded-full inline-block"></span>
                本日の出勤
                <span class="text-xs font-normal text-deli-400 ml-1">{{ today()->format('m月d日') }}</span>
            </h2>
            <a href="{{ route('girl.list', ['area_slug' => $area_slug]) }}/" class="text-xs text-deli-400 hover:text-deli-300 transition">もっと見る →</a>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
            @foreach($workingCasts as $cast)
            <a href="{{ route('cast.show', $cast->id) }}/" class="group flex-1 min-w-0">
                <div class="relative aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 mb-1.5 border border-surface-300 group-hover:border-deli-500 transition">
                    <picture>
                        @if($cast->img_webp_url)
                        <source srcset="{{ $cast->img_webp_url }}" type="image/webp">
                        @endif
                        <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                             loading="lazy"
                             class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition duration-300 opacity-90 group-hover:opacity-100">
                    </picture>
                    <span class="absolute top-1.5 left-1.5 bg-deli-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">出勤中</span>
                </div>
                <p class="text-xs font-medium text-[#D8D4CC] group-hover:text-gold-400 transition truncate">{{ $cast->name }}</p>
                <p class="text-xs text-[#8A8A9E] truncate">{{ $cast->shop_name }}</p>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ④ 新着日記 --}}
    @if($recentDiaries->isNotEmpty())
    <section>
        <h2 class="text-base font-bold text-[#E8E4DC] flex items-center gap-2 mb-4">
            <span aria-hidden="true" class="w-1 h-5 bg-deli-400 rounded-full inline-block"></span>
            新着日記
        </h2>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
            @foreach($recentDiaries as $diary)
            <a href="{{ route('cast.show', $diary->cast_id) }}/" class="group min-w-0">
                <div class="relative aspect-square overflow-hidden rounded-lg bg-surface-400 mb-1.5 border border-surface-300 group-hover:border-deli-400 transition">
                    @if($diary->img_url)
                    <img src="{{ $diary->img_url }}" alt="{{ $diary->title }}"
                         loading="lazy"
                         class="img-onerror-hide w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    @else
                    <span aria-hidden="true" class="absolute inset-0 flex items-center justify-center text-2xl text-deli-500 opacity-30">✦</span>
                    @endif
                </div>
                <p class="text-xs font-medium text-[#D8D4CC] group-hover:text-gold-400 transition truncate">{{ $diary->title }}</p>
                <p class="text-xs text-[#8A8A9E] truncate">{{ $diary->cast_name }}</p>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- 入店予定 --}}
    @if($comingSoonCasts->isNotEmpty())
    <section>
        <h2 class="text-base font-bold text-[#E8E4DC] flex items-center gap-2 mb-4">
            <span aria-hidden="true" class="w-1 h-5 bg-deli-400 rounded-full inline-block"></span>
            入店予定
        </h2>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
            @foreach($comingSoonCasts as $cast)
            <a href="{{ route('cast.show', $cast->id) }}/" class="group flex-1 min-w-0">
                <div class="relative aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 mb-1.5 border border-surface-300 group-hover:border-deli-400 transition">
                    <picture>
                        @if($cast->img_webp_url)
                        <source srcset="{{ $cast->img_webp_url }}" type="image/webp">
                        @endif
                        <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                             loading="lazy"
                             class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition duration-300 opacity-90 group-hover:opacity-100">
                    </picture>
                    <span class="absolute top-1.5 left-1.5 bg-deli-400 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">予定</span>
                </div>
                <p class="text-xs font-medium text-[#D8D4CC] group-hover:text-gold-400 transition truncate">{{ $cast->name }}</p>
                <p class="text-xs text-deli-400">{{ $cast->new_since }}</p>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ジャンルから探す --}}
    @if($shopTypeCounts->isNotEmpty())
    <section>
        <h2 class="text-base font-bold text-[#E8E4DC] flex items-center gap-2 mb-4">
            <span aria-hidden="true" class="w-1 h-5 bg-gold-400 rounded-full inline-block"></span>
            ジャンルから探す
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach($shopTypeCounts as $type)
            <a href="{{ route('shop.list.filter', ['area_slug' => $area_slug, 'filter_slug' => $type->slug]) }}/"
               class="bg-surface-600 hover:bg-surface-500 border border-surface-400 hover:border-deli-400 rounded-lg px-4 py-3 transition group">
                <p class="text-sm text-[#E8E4DC] group-hover:text-gold-400 transition font-medium">{{ $type->name }}</p>
                <p class="text-xs text-[#8A8A9E] mt-0.5">{{ number_format($type->cnt) }}件</p>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- エリアで探す（都道府県ページのみ） --}}
    @if(isset($subAreas) && $subAreas->isNotEmpty())
    <nav aria-label="エリアで探す">
        <h2 class="text-base font-bold text-[#E8E4DC] flex items-center gap-2 mb-4">
            <span aria-hidden="true" class="w-1 h-5 bg-deli-400 rounded-full inline-block"></span>
            エリアで探す
        </h2>
        <ul class="flex flex-wrap gap-2">
            @foreach($subAreas as $subArea)
            <li><a href="{{ route('shop.list', ['area_slug' => $subArea->slug]) }}/"
               class="px-3 py-1.5 rounded-full text-xs border border-surface-400 text-[#B0AEAD] hover:border-deli-400 hover:text-deli-400 transition">
                {{ $subArea->name }}<span class="text-[#6A6A7E] ml-1">{{ number_format($subArea->cnt) }}</span>
            </a></li>
            @endforeach
        </ul>
    </nav>
    @endif

    {{-- タイプで探す --}}
    <nav aria-label="タイプで探す">
        <h2 class="text-base font-bold text-[#E8E4DC] flex items-center gap-2 mb-4">
            <span aria-hidden="true" class="w-1 h-5 bg-surface-200 rounded-full inline-block"></span>
            タイプで探す
        </h2>
        <ul class="flex flex-wrap gap-2">
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
            <li><a href="{{ route('girl.list.type', ['area_slug' => $area_slug, 'type_slug' => $type['slug']]) }}/"
               class="px-3 py-1.5 rounded-full text-xs border border-surface-400 text-[#B0AEAD] hover:border-deli-400 hover:text-deli-400 transition">
                {{ $type['name'] }}
            </a></li>
            @endforeach
        </ul>
    </nav>

    {{-- クイックリンク --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('shop.list', ['area_slug' => $area_slug]) }}/"
           class="flex items-center justify-between bg-surface-600 hover:bg-surface-500 border border-surface-400 hover:border-deli-400 rounded-xl px-5 py-4 transition group">
            <div>
                <p class="text-sm font-bold text-[#E8E4DC] group-hover:text-gold-400 transition">店舗一覧</p>
                <p class="text-xs text-[#8A8A9E] mt-0.5">{{ $areaName }}のデリヘル・風俗店 {{ number_format($totalShops) }}件</p>
            </div>
            <span aria-hidden="true" class="text-[#8A8A9E] text-lg">›</span>
        </a>
        <a href="{{ route('girl.list', ['area_slug' => $area_slug]) }}/"
           class="flex items-center justify-between bg-surface-600 hover:bg-surface-500 border border-surface-400 hover:border-deli-400 rounded-xl px-5 py-4 transition group">
            <div>
                <p class="text-sm font-bold text-deli-400 group-hover:text-deli-300 transition">キャスト一覧</p>
                <p class="text-xs text-[#8A8A9E] mt-0.5">{{ $areaName }}の在籍キャストを探す</p>
            </div>
            <span aria-hidden="true" class="text-[#8A8A9E] text-lg">›</span>
        </a>
    </section>

</div>

@endsection
