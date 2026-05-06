@extends('layouts.app')

@section('title', 'デリヘル情報サイト｜全国のデリヘル店・キャスト')
@section('description', '全国のデリヘル情報を掲載。デリヘル店のシステム・料金・在籍キャストのプロフィールが検索できる総合情報サイト「デリコン」。新着キャスト情報も随時更新中。')
@section('canonical', route('top') . '/')
@section('robots', 'index, follow')
@section('og_type', 'website')

@push('head')
@php
$ldWebsite = [
    '@context' => 'https://schema.org',
    '@type'    => 'WebSite',
    '@id'      => url('/') . '#website',
    'url'      => url('/') . '/',
    'name'     => 'デリコン',
    'description' => '全国のデリヘル情報サイト',
    'inLanguage'  => 'ja',
    'potentialAction' => [
        '@type'       => 'SearchAction',
        'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => url('/shops/') . '?q={search_term_string}'],
        'query-input' => 'required name=search_term_string',
    ],
];
$ldPage = [
    '@context'  => 'https://schema.org',
    '@type'     => 'WebPage',
    '@id'       => url('/') . '#webpage',
    'url'       => url('/') . '/',
    'name'      => 'デリヘル情報サイト｜全国のデリヘル店・キャスト - デリコン',
    'inLanguage'=> 'ja',
    'description' => '全国のデリヘル情報を掲載。デリヘル店のシステム・料金・在籍キャスト情報が検索できる総合情報サイト。',
    'isPartOf'  => ['@id' => url('/') . '#website'],
];
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($ldWebsite, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG) !!}</script>
<script type="application/ld+json" @nonce>{!! json_encode($ldPage, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG) !!}</script>
@endpush

@section('content')

{{-- ヒーロー --}}
<section class="bg-gray-900 text-white py-12 md:py-20">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h1 class="text-3xl md:text-5xl font-bold mb-3 tracking-tight leading-tight">
            全国<span class="text-red-400">デリヘル</span>情報サイト
        </h1>
        <p class="text-gray-300 text-base md:text-lg mb-2">
            デリヘル店のシステム・料金・在籍キャストを詳しく掲載
        </p>
        <p class="text-gray-400 text-sm mb-8">デリコン｜デリヘル・風俗総合情報</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-6">
            <a href="{{ route('shop.index') }}/"
               class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-10 rounded-xl text-lg transition shadow-lg">
                デリヘル店舗を探す
            </a>
            <a href="{{ route('cast.index') }}/"
               class="inline-block bg-pink-500 hover:bg-pink-600 text-white font-bold py-4 px-10 rounded-xl text-lg transition shadow-lg">
                キャストを探す
            </a>
        </div>
        <p class="text-xs text-gray-500">※本サイトは18歳以上の方を対象としています</p>
    </div>
</section>

{{-- 業種クイックフィルター --}}
@if($shopTypes->isNotEmpty())
<nav class="bg-gray-800 py-3" aria-label="デリヘル業種別">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
            <a href="{{ route('shop.index') }}/"
               class="flex-shrink-0 bg-red-600 text-white text-sm px-4 py-1.5 rounded-full whitespace-nowrap">
                全業種
            </a>
            @foreach($shopTypes as $type)
            <a href="{{ route('shop.index') }}/?type={{ $type->id }}"
               class="flex-shrink-0 bg-gray-700 hover:bg-red-600 text-white text-sm px-4 py-1.5 rounded-full transition whitespace-nowrap">
                {{ $type->name }}
            </a>
            @endforeach
        </div>
    </div>
</nav>
@endif

{{-- おすすめデリヘル店舗 --}}
<section class="max-w-6xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl md:text-2xl font-bold border-l-4 border-red-600 pl-3">
            おすすめデリヘル店舗
        </h2>
        <a href="{{ route('shop.index') }}/" class="text-sm text-red-600 hover:underline">すべて見る &rsaquo;</a>
    </div>
    @if($recommendedShops->isNotEmpty())
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($recommendedShops as $shop)
        <a href="{{ route('shop.show', $shop->id) }}/"
           class="bg-white rounded-xl shadow hover:shadow-md overflow-hidden transition group">
            <div class="relative aspect-video bg-gray-200 overflow-hidden">
                @if($shop->shop_file_name)
                <img src="/img/{{ ltrim($shop->shop_file_name, '/') }}"
                     alt="{{ $shop->name }}のデリヘル情報"
                     loading="lazy"
                     onerror="this.style.display='none'"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            </div>
            <div class="p-3">
                @if($shop->shop_type_name)
                <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">{{ $shop->shop_type_name }}</span>
                @endif
                <h3 class="font-bold text-sm text-gray-900 mt-1 group-hover:text-red-600 transition line-clamp-1">
                    {{ $shop->name }}
                </h3>
                @if($shop->catche)
                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $shop->catche }}</p>
                @endif
                <div class="flex items-center justify-between mt-2 text-xs text-gray-400">
                    <span>在籍{{ $shop->cast_count }}名</span>
                    @if($shop->price_60)
                    <span class="text-red-500 font-medium">60分¥{{ number_format($shop->price_60) }}〜</span>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif
    <div class="mt-6 text-center">
        <a href="{{ route('shop.index') }}/"
           class="inline-block border-2 border-red-600 text-red-600 hover:bg-red-600 hover:text-white font-bold px-10 py-3 rounded-xl transition">
            デリヘル店舗をもっと見る
        </a>
    </div>
</section>

{{-- 新着キャスト --}}
<section class="bg-gray-50 py-10">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl md:text-2xl font-bold border-l-4 border-pink-500 pl-3">
                新着デリヘルキャスト
            </h2>
            <a href="{{ route('cast.index') }}/" class="text-sm text-pink-600 hover:underline">すべて見る &rsaquo;</a>
        </div>
        @if($newCasts->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            @foreach($newCasts as $cast)
            @php
                $castImg = ($cast->img_file_name && !str_starts_with($cast->img_file_name, '/img/common/'))
                    ? '/img/girl/00/' . ltrim($cast->img_file_name, '/')
                    : '/img/no-cast.jpg';
            @endphp
            <a href="{{ route('cast.show', $cast->id) }}/"
               class="bg-white rounded-xl shadow hover:shadow-md overflow-hidden transition group">
                <div class="aspect-[3/4] overflow-hidden bg-gray-100">
                    <img src="{{ $castImg }}"
                         alt="{{ $cast->name }}（{{ $cast->shop_name ?? 'デリヘル' }}）のキャスト情報"
                         loading="lazy"
                         onerror="this.src='/img/no-cast.jpg'"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                </div>
                <div class="p-2">
                    <p class="font-bold text-xs text-gray-900 truncate">{{ $cast->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $cast->age ? $cast->age . '歳' : '' }}{{ ($cast->age && $cast->cup) ? '・' : '' }}{{ $cast->cup ? $cast->cup . 'カップ' : '' }}
                    </p>
                    @if($cast->shop_name)
                    <p class="text-xs text-gray-400 truncate mt-0.5">{{ $cast->shop_name }}</p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
        <div class="mt-6 text-center">
            <a href="{{ route('cast.index') }}/"
               class="inline-block border-2 border-pink-500 text-pink-600 hover:bg-pink-500 hover:text-white font-bold px-10 py-3 rounded-xl transition">
                キャストをもっと見る
            </a>
        </div>
        @endif
    </div>
</section>

{{-- 業種ランキング --}}
@if($popularKeywords->isNotEmpty())
<section class="max-w-6xl mx-auto px-4 py-10">
    <h2 class="text-xl md:text-2xl font-bold mb-6 border-l-4 border-gray-400 pl-3">
        デリヘルの業種から探す
    </h2>
    <div class="flex flex-wrap gap-3">
        @foreach($popularKeywords as $kw)
        <a href="{{ route('shop.index') }}/?type={{ $kw->id ?? '' }}"
           class="bg-white border border-gray-200 hover:border-red-400 hover:bg-red-50 text-gray-700 hover:text-red-700 rounded-full px-5 py-2 text-sm transition shadow-sm">
            {{ $kw->name }}
            @if(isset($kw->count) && $kw->count > 0)
            <span class="text-gray-400 text-xs ml-1">{{ $kw->count }}店</span>
            @endif
        </a>
        @endforeach
    </div>
</section>
@endif

{{-- デリヘルとは（SEOテキスト） --}}
<section class="bg-white border-t border-gray-100 py-12">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-xl font-bold text-gray-800 mb-4">デリヘルとは</h2>
        <div class="prose prose-sm text-gray-600 max-w-none space-y-3 leading-relaxed">
            <p>デリヘル（デリバリーヘルス）とは、派遣型の風俗サービスの一種で、キャストがお客様の指定する場所（ホテル・自宅など）に出張する形式の店舗です。店舗型ではないため比較的リーズナブルな料金設定が多く、全国各地で多くの店舗が営業しています。</p>
            <p>デリコンでは、全国のデリヘル・風俗店情報を掲載しています。各店舗のシステム・料金・在籍キャストのプロフィールをまとめて確認できます。ホテヘル・素人系・人妻・SMなど業種ごとに絞り込み検索も可能です。</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-8">
            <div class="bg-red-50 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-red-600 mb-1">{{ number_format(\App\Models\Shop::where('status','active')->count()) }}店舗</div>
                <p class="text-xs text-gray-600">掲載中のデリヘル店舗</p>
            </div>
            <div class="bg-pink-50 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-pink-600 mb-1">{{ number_format(\App\Models\Cast::where('status','active')->count()) }}名</div>
                <p class="text-xs text-gray-600">在籍キャスト数</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-gray-700 mb-1">全国</div>
                <p class="text-xs text-gray-600">47都道府県の情報を掲載</p>
            </div>
        </div>
    </div>
</section>

{{-- よくある質問 --}}
<section class="bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-xl font-bold text-gray-800 mb-6">よくある質問</h2>
        <div class="space-y-4">
            <details class="bg-white rounded-xl shadow-sm p-5 group">
                <summary class="font-medium text-gray-800 cursor-pointer list-none flex justify-between items-center">
                    <span>デリヘルの料金相場はどのくらいですか？</span>
                    <span class="text-gray-400 group-open:rotate-180 transition-transform">▼</span>
                </summary>
                <p class="mt-3 text-sm text-gray-600 leading-relaxed">デリヘルの料金は店舗・地域・コースによって異なりますが、一般的に60分コースで10,000円〜30,000円程度が相場です。素人系・人妻系などの業種や指名料の有無によっても変わります。各店舗の詳細ページでシステム・料金を確認できます。</p>
            </details>
            <details class="bg-white rounded-xl shadow-sm p-5 group">
                <summary class="font-medium text-gray-800 cursor-pointer list-none flex justify-between items-center">
                    <span>デリヘルはどんな業種がありますか？</span>
                    <span class="text-gray-400 group-open:rotate-180 transition-transform">▼</span>
                </summary>
                <p class="mt-3 text-sm text-gray-600 leading-relaxed">デリヘルにはホテヘル・素人系・人妻・熟女・SM・ニューハーフ・アロマエステ・イメクラなどさまざまな業種があります。デリコンでは業種ごとに絞り込み検索ができます。</p>
            </details>
            <details class="bg-white rounded-xl shadow-sm p-5 group">
                <summary class="font-medium text-gray-800 cursor-pointer list-none flex justify-between items-center">
                    <span>キャストの情報はどこで確認できますか？</span>
                    <span class="text-gray-400 group-open:rotate-180 transition-transform">▼</span>
                </summary>
                <p class="mt-3 text-sm text-gray-600 leading-relaxed">各店舗の詳細ページに在籍キャスト一覧が掲載されています。また「キャストを探す」ページでは、タイプ・年齢・カップサイズなどの条件でキャストを横断検索することができます。</p>
            </details>
        </div>
    </div>
</section>

@endsection
