@extends('layouts.app')

@php
    $suffix = 'デリヘル・風俗';
    if ($areaName && $jobTypeName) {
        $baseTitle = "{$areaName}の{$jobTypeName}店舗一覧";
    } elseif ($areaName) {
        $baseTitle = "{$areaName}の{$suffix}店舗一覧";
    } elseif ($jobTypeName) {
        $baseTitle = "全国の{$jobTypeName}店舗一覧";
    } else {
        $baseTitle = "{$suffix}店舗一覧";
    }
    $currentPage = (int) request()->input('page', 1);
    $pageSuffix  = $currentPage > 1 ? "（{$currentPage}ページ目）" : '';
    $pageTitle   = $baseTitle . $pageSuffix;

    $totalStr = number_format($results->total());
    if ($areaName && $jobTypeName) {
        $pageDescription = "{$areaName}の{$jobTypeName}店舗{$totalStr}件を掲載。料金・システム・写真で比較できます。";
    } elseif ($areaName) {
        $pageDescription = "{$areaName}のデリヘル・風俗店舗{$totalStr}件を掲載。料金・システム・写真で比較できます。";
    } elseif ($jobTypeName) {
        $pageDescription = "全国の{$jobTypeName}店舗{$totalStr}件を掲載。料金・システム・写真で比較できます。";
    } else {
        $pageDescription = "全国のデリヘル・風俗店舗{$totalStr}件を掲載。料金・システム・写真で比較できます。";
    }
@endphp

@php
    $shopBase        = url("/{$area_slug}/shop-list" . ($job_slug !== 'all' ? "/{$job_slug}" : '')) . '/';
    $shopCanonical   = $ageRange ? $shopBase . '?age_range=' . $ageRange : $shopBase;
    $multiFilter     = $job_slug !== 'all' && $ageRange !== '';
    $shopNoindex     = $noindex || $multiFilter;
    // multi-filter時はage_rangeなしbaseへcanonical
    $shopCanonical   = $multiFilter ? $shopBase : $shopCanonical;
@endphp
@section('title', $pageTitle)
@section('description', $pageDescription)
@section('robots', $shopNoindex ? 'noindex,follow' : 'index,follow')
@section('canonical', $shopCanonical)

@push('head')
@php
    $sbItems = [['name' => 'ホーム', 'item' => route('top') . '/'], ['name' => '店舗一覧', 'item' => route('shop.list', ['area_slug' => 'all']) . '/']];
    if ($areaName) $sbItems[] = ['name' => $areaName, 'item' => route('shop.list', ['area_slug' => $area_slug]) . '/'];
    if ($jobTypeName) $sbItems[] = ['name' => $jobTypeName, 'item' => $shopBase];
    $sbSchema = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' =>
        array_map(fn($item, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $item['name'], 'item' => $item['item']], array_values($sbItems), array_keys($sbItems))];
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($sbSchema, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')

{{-- Header --}}
<div class="bg-surface-800 border-b border-surface-400">
    <div class="max-w-6xl mx-auto px-4 py-4">
        <div class="flex items-baseline gap-3">
            <h1 class="text-xl font-bold text-[#E8E4DC]">
                {{ $baseTitle }}
            </h1>
            <span class="text-sm text-[#B0AEAD]">{{ number_format($results->total()) }}件</span>
        </div>
        {{-- パンくず --}}
        <nav class="text-xs text-[#8A8A9E] mt-1.5 flex items-center gap-1 flex-wrap">
            <a href="{{ route('top') }}/" class="hover:text-gold-400 transition">TOP</a>
            <span>›</span>
            <a href="{{ route('shop.list', ['area_slug' => 'all']) }}/" class="hover:text-gold-400 transition">全国の店舗一覧</a>
            @if($areaName)
                <span>›</span>
                <a href="{{ route('shop.list', ['area_slug' => $area_slug]) }}/" class="hover:text-gold-400 transition">{{ $areaName }}</a>
            @endif
            @if($jobTypeName)
                <span>›</span>
                <span>{{ $jobTypeName }}</span>
            @endif
        </nav>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-6">

    {{-- 小エリア絞り込み（都道府県ページのみ） --}}
    @if(isset($subAreas) && $subAreas->isNotEmpty())
    <div class="mb-5">
        <p class="text-xs text-[#8A8A9E] mb-2">エリアで絞り込む</p>
        <div class="flex flex-wrap gap-1.5">
            @foreach($subAreas as $subArea)
            <a href="{{ route('shop.list', ['area_slug' => $subArea->slug]) }}/"
               class="px-3 py-1 rounded-full text-xs border border-surface-400 text-[#B0AEAD] hover:border-deli-400 hover:text-deli-400 transition whitespace-nowrap">
                {{ $subArea->name }}<span class="text-[#6A6A7E] ml-0.5">{{ number_format($subArea->cnt) }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 店種フィルター --}}
    <div class="flex flex-wrap gap-2 mb-5">
        <a href="{{ route('shop.list', ['area_slug' => $area_slug]) }}/"
           class="px-3 py-1 rounded-full text-xs border transition
                  {{ $job_slug === 'all'
                     ? 'bg-deli-500 border-deli-500 text-white'
                     : 'border-surface-300 text-[#B0AEAD] hover:border-deli-400 hover:text-deli-400' }}">
            すべて
        </a>
        @foreach($shopTypes as $type)
        <a href="{{ route('shop.list.filter', ['area_slug' => $area_slug, 'filter_slug' => $type->slug]) }}/"
           class="px-3 py-1 rounded-full text-xs border transition
                  {{ $job_slug === $type->slug
                     ? 'bg-deli-500 border-deli-500 text-white'
                     : 'border-surface-300 text-[#B0AEAD] hover:border-deli-400 hover:text-deli-400' }}">
            {{ $type->name }}
        </a>
        @endforeach
    </div>

    {{-- 年齢層フィルター --}}
    @php
        $ageGroups  = ['' => 'すべて', '18-19' => '10代', '20-24' => '20〜24歳', '25-34' => '25〜34歳', '35-44' => '35〜44歳', '45+' => '45歳〜'];
        $baseUrl    = url("/{$area_slug}/shop-list/" . ($job_slug !== 'all' ? "{$job_slug}/" : ''));
    @endphp
    <div class="flex flex-wrap items-center gap-2 mb-6">
        <span class="text-xs text-[#8A8A9E]">年齢層:</span>
        @foreach($ageGroups as $val => $label)
        <a href="{{ $baseUrl }}{{ $val ? '?age_range=' . $val : '' }}"
           class="px-3 py-1 rounded-full text-xs border transition
                  {{ $ageRange === $val
                     ? 'bg-surface-400 border-surface-300 text-[#E8E4DC]'
                     : 'border-surface-500 text-[#8A8A9E] hover:border-surface-300 hover:text-[#B0AEAD]' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    @if($results->isEmpty())
    <div class="text-center py-16 text-[#8A8A9E]">
        <p class="text-lg mb-4">条件に合う店舗が見つかりませんでした</p>
        <a href="{{ route('shop.list', ['area_slug' => 'all']) }}/" class="text-sm text-deli-400 hover:underline">← 全国の店舗一覧へ</a>
    </div>
    @else

    {{-- 有料店舗グリッド (plan 1-3) --}}
    @php $paidShops = $results->getCollection()->filter(fn($s) => $s->plan <= 3); @endphp
    @if($paidShops->isNotEmpty())
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
        @foreach($paidShops as $shop)
        <a href="{{ route('shop.show', $shop->id) }}/"
           class="bg-surface-500 border border-surface-300 hover:border-deli-500 rounded-xl overflow-hidden transition group block">
            <div class="relative h-40 bg-gradient-to-br from-surface-400 to-surface-600 overflow-hidden">
                @if($shop->main_image)
                <img src="{{ Storage::url($shop->main_image) }}"
                     alt="{{ $shop->name }}のデリヘル情報"
                     @if($loop->first) fetchpriority="high" @else loading="lazy" @endif
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
                <p class="font-bold text-sm text-[#E8E4DC] group-hover:text-gold-400 transition line-clamp-1">
                    {{ $shop->name }}
                </p>
                @if($shop->catche)
                <p class="text-xs text-[#B0AEAD] mt-0.5 line-clamp-2">{{ $shop->catche }}</p>
                @endif
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-[#8A8A9E]">在籍{{ $shop->active_cast_count }}名</span>
                    @if($shop->price_60)
                    <span class="text-gold-400 font-medium">60分¥{{ number_format($shop->price_60) }}〜</span>
                    @endif
                </div>
                @if($shop->area)
                <p class="text-xs text-[#8A8A9E] mt-1 truncate">{{ $shop->area->name }}</p>
                @endif
            </div>
        </a>
        @endforeach
    </div>
    @endif

    {{-- 無料店舗リスト (plan 4-5) --}}
    @php $freeShops = $results->getCollection()->filter(fn($s) => $s->plan >= 4); @endphp
    @if($freeShops->isNotEmpty())
    <div class="border-t border-surface-400 pt-4">
        <p class="text-xs text-[#8A8A9E] mb-3">その他の掲載店舗</p>
        <div class="divide-y divide-surface-500">
            @foreach($freeShops as $shop)
            @if($shop->plan === 4)
            {{-- お試しSP: リンクあり --}}
            <a href="{{ route('shop.show', $shop->id) }}/"
               class="flex items-center gap-3 py-2.5 hover:bg-surface-600 px-2 rounded transition group">
                <div class="w-2 h-2 rounded-full bg-surface-300 shrink-0"></div>
                <div class="flex-1 min-w-0">
                    <span class="text-sm text-[#B0AEAD] group-hover:text-gold-400 transition truncate block">{{ $shop->name }}</span>
                    <div class="flex items-center gap-2 mt-0.5">
                        @if($shop->shopType)
                        <span class="text-xs text-[#8A8A9E]">{{ $shop->shopType->name }}</span>
                        @endif
                        @if($shop->area)
                        <span class="text-xs text-[#8A8A9E]">{{ $shop->area->name }}</span>
                        @endif
                    </div>
                </div>
                @if($shop->active_cast_count > 0)
                <span class="text-xs text-[#8A8A9E] shrink-0">在籍{{ $shop->active_cast_count }}名</span>
                @endif
            </a>
            @else
            {{-- お試しコース: リンクなし --}}
            <div class="flex items-center gap-3 py-2.5 px-2">
                <div class="w-2 h-2 rounded-full bg-surface-500 shrink-0"></div>
                <div class="flex-1 min-w-0">
                    <span class="text-sm text-[#8A8A9E] truncate block">{{ $shop->name }}</span>
                    <div class="flex items-center gap-2 mt-0.5">
                        @if($shop->shopType)
                        <span class="text-xs text-[#4A4A5E]">{{ $shop->shopType->name }}</span>
                        @endif
                        @if($shop->area)
                        <span class="text-xs text-[#4A4A5E]">{{ $shop->area->name }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- ページネーション --}}
    @if($results->hasPages())
    <div class="mt-8 flex justify-center">
        {{ $results->links() }}
    </div>
    @endif

    @endif
</div>

@endsection
