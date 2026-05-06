@extends('layouts.app')
@section('title', $title)
@section('description', $description)
@if(isset($slug))
@section('canonical', route('shop.pref_area', [$parentPref, $slug]) . '/')
@else
@section('canonical', route('shop.pref', $parentPref) . '/')
@endif

@push('head')
@php
$ld_breadcrumb = [
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => collect($breadcrumbs)->map(fn($b, $i) => array_filter([
        '@type'    => 'ListItem',
        'position' => $i + 1,
        'name'     => $b['name'],
        'item'     => $b['url'] ?? null,
    ]))->values()->all(),
];
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($ld_breadcrumb, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    {{-- パンくず --}}
    <nav class="text-xs text-[#6A6A7E] mb-4">
        @foreach($breadcrumbs as $crumb)
            @if(!$loop->last)
                <a href="{{ $crumb['url'] }}" class="hover:text-gold-400 transition">{{ $crumb['name'] }}</a>
                <span class="mx-1">&rsaquo;</span>
            @else
                <span class="text-[#B0AEAD]">{{ $crumb['name'] }}</span>
            @endif
        @endforeach
    </nav>

    <h1 class="text-2xl font-bold text-[#F0ECE4] mb-2 flex items-center gap-3">
        <span class="w-1 h-7 bg-deli-500 rounded-full inline-block"></span>
        {{ $title }}
    </h1>
    <p class="text-xs text-[#6A6A7E] mb-5">{{ $description }}</p>

    {{-- 配下エリアリンク（都道府県ページのみ表示） --}}
    @if($areas->isNotEmpty())
    <div class="mb-6">
        <p class="text-xs font-bold text-[#8A8A9E] mb-2 uppercase tracking-wider">エリアで絞り込む</p>
        <div class="flex flex-wrap gap-2">
            @foreach($areas as $area)
            <a href="{{ route('shop.pref_area', [$parentPref, $area->slug]) }}/"
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
            @php $bannerSrc = $shop->main_image ? \Illuminate\Support\Facades\Storage::url(str_replace('main.jpg','main_banner.jpg',$shop->main_image)) : $shop->shop_banner_url; @endphp
            @if($bannerSrc)
            <div class="relative overflow-hidden">
                <img src="{{ $bannerSrc }}" alt="{{ $shop->name }}"
                     class="w-full h-36 object-cover opacity-90 group-hover:opacity-100 group-hover:scale-105 transition duration-300 img-onerror-hide"
                     loading="lazy">
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
            <p>該当するエリアに店舗情報がありません</p>
            <a href="{{ route('shop.index') }}/" class="mt-3 inline-block text-xs text-deli-400 hover:text-deli-300 transition">店舗一覧に戻る</a>
        </div>
        @endforelse
    </div>

    <div class="[&_.pagination]:flex [&_.pagination]:gap-1 [&_a]:bg-surface-500 [&_a]:border [&_a]:border-surface-300 [&_a]:text-[#B0AEAD] [&_a:hover]:border-deli-500 [&_a:hover]:text-deli-400 [&_a]:px-3 [&_a]:py-1.5 [&_a]:rounded [&_a]:text-sm [&_span.current]:bg-deli-500 [&_span.current]:text-white [&_span.current]:border-deli-500 [&_span.current]:px-3 [&_span.current]:py-1.5 [&_span.current]:rounded [&_span.current]:text-sm">
        {{ $shops->links() }}
    </div>
</div>
@endsection
