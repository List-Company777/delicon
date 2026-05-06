@extends('layouts.app')

@section('title', $shop->name)
@section('description',
    ($shop->catche ?: $shop->name . 'の詳細情報。') .
    ($shop->price_60 ? '60分¥' . number_format($shop->price_60) . '〜。' : '') .
    'キャスト・システム・料金などをご紹介。'
)
@section('canonical', route('shop.show', $shop->id) . '/')
@if($shop->banner_url)
@section('ogp_image', $shop->banner_url)
@section('twitter_card', 'summary_large_image')
@endif

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    {{-- パンくず --}}
    <nav class="text-xs text-[#6A6A7E] mb-4">
        <a href="{{ route('top') }}/" class="hover:text-gold-400 transition">ホーム</a> &rsaquo;
        <a href="{{ route('shop.index') }}/" class="hover:text-gold-400 transition">店舗一覧</a> &rsaquo;
        <span class="text-[#B0AEAD]">{{ $shop->name }}</span>
    </nav>

    {{-- 店舗バナー（5:2 比率） --}}
    @if($shop->banner_url)
    <div class="relative w-full aspect-[5/2] rounded-xl overflow-hidden mb-6 bg-surface-600">
        <img src="{{ $shop->banner_url }}" alt="{{ $shop->name }}"
             class="absolute inset-0 w-full h-full object-cover img-onerror-hide">
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- メイン情報 --}}
        <div class="md:col-span-2">

            {{-- 店舗ヘッダー --}}
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5 mb-4">
                <div class="flex gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap gap-1 mb-2">
                            @if($shop->shopType)
                            <span class="text-xs bg-deli-500/20 text-deli-400 px-2 py-0.5 rounded-full border border-deli-500/30">{{ $shop->shopType->name }}</span>
                            @endif
                            @if($shop->shopType2)
                            <span class="text-xs bg-surface-400 text-[#8A8A9E] px-2 py-0.5 rounded-full">{{ $shop->shopType2->name }}</span>
                            @endif
                        </div>
                        <h1 class="text-xl font-bold text-[#F0ECE4] mb-1">{{ $shop->name }}</h1>
                        @if($shop->catche)
                        <p class="text-sm text-deli-400 font-medium">{{ $shop->catche }}</p>
                        @endif
                    </div>
                </div>
                @if($shop->base)
                <div class="mt-4 text-sm text-[#C8C4BC] leading-relaxed border-t border-surface-300 pt-4">
                    <p>{!! nl2br(e($shop->base)) !!}</p>
                </div>
                @endif
            </div>

            {{-- 基本情報テーブル --}}
            @if($shop->system_text || $shop->price_60 || $shop->open_time || $shop->address)
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5 mb-4">
                <h2 class="font-bold text-[#E8E4DC] mb-4 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-deli-500 rounded-full inline-block"></span>基本情報・システム
                </h2>
                <table class="w-full text-sm">
                    @if($shop->price_60)
                    <tr class="border-b border-surface-300">
                        <th class="py-2.5 pr-3 text-left text-[#6A6A7E] font-normal w-32">60分料金</th>
                        <td class="py-2.5 text-gold-400 font-bold">¥{{ number_format($shop->price_60) }}〜</td>
                    </tr>
                    @endif
                    @if($shop->price_90)
                    <tr class="border-b border-surface-300">
                        <th class="py-2.5 pr-3 text-left text-[#6A6A7E] font-normal w-32">90分料金</th>
                        <td class="py-2.5 text-[#C8C4BC]">¥{{ number_format($shop->price_90) }}〜</td>
                    </tr>
                    @endif
                    @if($shop->price_120)
                    <tr class="border-b border-surface-300">
                        <th class="py-2.5 pr-3 text-left text-[#6A6A7E] font-normal w-32">120分料金</th>
                        <td class="py-2.5 text-[#C8C4BC]">¥{{ number_format($shop->price_120) }}〜</td>
                    </tr>
                    @endif
                    @if($shop->price_high)
                    <tr class="border-b border-surface-300">
                        <th class="py-2.5 pr-3 text-left text-[#6A6A7E] font-normal w-32">高級コース</th>
                        <td class="py-2.5 text-[#C8C4BC]">¥{{ number_format($shop->price_high) }}〜</td>
                    </tr>
                    @endif
                    @if($shop->open_time || $shop->close_time || $shop->all_time)
                    <tr class="border-b border-surface-300">
                        <th class="py-2.5 pr-3 text-left text-[#6A6A7E] font-normal">営業時間</th>
                        <td class="py-2.5 text-[#C8C4BC]">
                            @if($shop->all_time) <span class="text-green-400 font-semibold">24時間営業</span>
                            @else {{ $shop->open_time }}{{ $shop->close_time ? ' 〜 ' . $shop->close_time : '' }} @endif
                        </td>
                    </tr>
                    @endif
                    @if($shop->rest_day)
                    <tr class="border-b border-surface-300">
                        <th class="py-2.5 pr-3 text-left text-[#6A6A7E] font-normal">定休日</th>
                        <td class="py-2.5 text-[#C8C4BC]">{{ $shop->rest_day }}</td>
                    </tr>
                    @endif
                    @if($shop->eigyo_area)
                    <tr class="border-b border-surface-300">
                        <th class="py-2.5 pr-3 text-left text-[#6A6A7E] font-normal">営業エリア</th>
                        <td class="py-2.5 text-[#C8C4BC]">{{ $shop->eigyo_area }}</td>
                    </tr>
                    @endif
                    @if($shop->eigyo_space)
                    <tr class="border-b border-surface-300">
                        <th class="py-2.5 pr-3 text-left text-[#6A6A7E] font-normal">プレイスペース</th>
                        <td class="py-2.5 text-[#C8C4BC]">{{ $shop->eigyo_space }}</td>
                    </tr>
                    @endif
                    @if($shop->address)
                    <tr class="border-b border-surface-300">
                        <th class="py-2.5 pr-3 text-left text-[#6A6A7E] font-normal">住所</th>
                        <td class="py-2.5 text-[#C8C4BC]">{{ $shop->address }}</td>
                    </tr>
                    @endif
                    @if($shop->tel)
                    <tr>
                        <th class="py-2.5 pr-3 text-left text-[#6A6A7E] font-normal">電話番号</th>
                        <td class="py-2.5"><a href="tel:{{ $shop->tel }}" class="text-gold-400 hover:underline font-medium">{{ $shop->tel }}</a></td>
                    </tr>
                    @endif
                </table>

                @if($shop->system_text)
                <div class="mt-4 bg-surface-400 rounded-lg p-4 text-sm text-[#C8C4BC] leading-relaxed">
                    <p class="font-semibold text-[#E8E4DC] mb-2">システム詳細</p>
                    <p>{!! nl2br(e($shop->system_text)) !!}</p>
                </div>
                @endif
            </div>
            @endif

            {{-- クーポン --}}
            @if($shop->coupon)
            <div class="bg-gold-400/10 border border-gold-400/30 rounded-xl p-4 mb-4">
                <h2 class="font-bold text-gold-400 mb-2 text-sm flex items-center gap-2">
                    <span>★</span> クーポン・特典
                </h2>
                <p class="text-sm text-[#C8C4BC] leading-relaxed">{!! nl2br(e($shop->coupon)) !!}</p>
            </div>
            @endif

            {{-- タグ --}}
            @if(!empty($shop->tags))
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-4 mb-4">
                <p class="text-xs text-[#6A6A7E] mb-2">特色・タグ</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($shop->tags as $tag)
                    <span class="text-xs bg-surface-400 text-[#B0AEAD] border border-surface-300 px-3 py-1 rounded-full">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 口コミセクション --}}
            @php
                $approvedReviews = $shop->reviews()->where('status','approved')->with('user:id,name')->orderByDesc('created_at')->take(5)->get();
            @endphp
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5 mb-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-[#E8E4DC] text-sm flex items-center gap-2">
                        <span class="w-1 h-4 bg-gold-400 rounded-full inline-block"></span>
                        口コミ <span class="text-[#6A6A7E] font-normal">({{ $approvedReviews->count() }}件)</span>
                    </h2>
                    @auth
                    <a href="{{ route('review.create') }}?shop_id={{ $shop->id }}" class="text-xs bg-deli-500 hover:bg-deli-400 text-white px-3 py-1.5 rounded-lg transition">口コミを書く</a>
                    @else
                    <a href="{{ route('visitor.register') }}?redirect={{ urlencode(route('review.create') . '?shop_id=' . $shop->id) }}" class="text-xs border border-deli-500 text-deli-400 hover:bg-deli-500 hover:text-white px-3 py-1.5 rounded-lg transition">口コミを書く（要登録）</a>
                    @endauth
                </div>
                @if($approvedReviews->isEmpty())
                <p class="text-sm text-[#6A6A7E] text-center py-4">まだ口コミがありません。最初の口コミを投稿してみましょう。</p>
                @else
                <div class="space-y-4">
                    @foreach($approvedReviews as $review)
                    <div class="border-b border-surface-300 pb-4 last:border-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-amber-400 text-sm">{{ str_repeat('★', $review->rating) }}<span class="text-[#3A3A4E]">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                            <span class="text-xs text-[#6A6A7E]">{{ $review->user->name }}</span>
                            <span class="text-xs text-[#4A4A5E]">{{ $review->created_at->format('Y/m/d') }}</span>
                        </div>
                        @if($review->title)
                        <p class="text-sm font-semibold text-[#E8E4DC] mb-1">{{ $review->title }}</p>
                        @endif
                        <p class="text-sm text-[#B0AEAD] leading-relaxed">{{ $review->body }}</p>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- キャスト一覧 --}}
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5 mb-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-[#E8E4DC] text-sm flex items-center gap-2">
                        <span class="w-1 h-4 bg-deli-500 rounded-full inline-block"></span>
                        在籍キャスト <span class="text-[#6A6A7E] font-normal">({{ $casts->total() }}名)</span>
                    </h2>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @forelse($casts as $cast)
                    @php
                        $isWorking = $cast->working_date && $cast->working_date->isToday();
                        $isNew     = $cast->isNew();
                    @endphp
                    <a href="{{ route('cast.show', $cast->id) }}/" class="group text-center">
                        <div class="relative aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 mb-1.5 border {{ $isWorking ? 'border-emerald-500/60' : 'border-surface-300 group-hover:border-deli-500' }} transition">
                            <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                                 class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                 loading="lazy">
                            @if($isWorking)
                            <span class="absolute top-1.5 left-1.5 text-[10px] font-bold bg-emerald-500 text-white px-1.5 py-0.5 rounded-full leading-none">待機中</span>
                            @elseif($isNew)
                            <span class="absolute top-1.5 left-1.5 text-[10px] font-bold bg-gold-500 text-surface-800 px-1.5 py-0.5 rounded-full leading-none">NEW</span>
                            @endif
                        </div>
                        <p class="text-xs font-medium text-[#D8D4CC] group-hover:text-gold-400 transition truncate">{{ $cast->name }}</p>
                        @if($cast->age)
                        <p class="text-xs text-[#6A6A7E]">{{ $cast->age }}歳</p>
                        @endif
                    </a>
                    @empty
                    <p class="col-span-3 text-sm text-[#6A6A7E] py-4 text-center">キャスト情報がありません</p>
                    @endforelse
                </div>
                @if($casts->hasPages())
                <div class="mt-4">{{ $casts->links() }}</div>
                @endif
            </div>

            {{-- ニュース --}}
            @if($news->count() > 0)
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <h2 class="font-bold text-[#E8E4DC] mb-3 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-surface-200 rounded-full inline-block"></span>店舗からのお知らせ
                </h2>
                <div class="space-y-3">
                    @foreach($news as $item)
                    <div class="border-b border-surface-300 pb-3 last:border-0">
                        <p class="text-xs text-[#4A4A5E] mb-1 flex items-center gap-1.5">
                            @if($item->is_pinned)<span class="text-gold-400 text-xs" title="必ず表示">📌</span>@endif
                            {{ $item->created_at?->format('Y/m/d') }}
                        </p>
                        <p class="text-sm text-[#C8C4BC] leading-relaxed">{!! nl2br(e($item->body)) !!}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- サイドバー --}}
        <div class="space-y-4">
            @if($shop->tel)
            <div class="bg-deli-500 rounded-xl p-5 text-center">
                <p class="text-white text-xs mb-1 opacity-80">お電話でのご予約</p>
                <a href="tel:{{ $shop->tel }}" class="block text-white text-xl font-bold tracking-widest hover:opacity-90 transition">{{ $shop->tel }}</a>
            </div>
            @endif

            {{-- 本日の出勤 --}}
            @php
                $workingCasts = \App\Models\Cast::where('shop_id', $shop->id)
                    ->where('status','active')
                    ->whereDate('working_date', today())
                    ->orderByDesc('is_recommended')
                    ->take(6)->get();
            @endphp
            @if($workingCasts->isNotEmpty())
            <div class="bg-surface-500 border border-deli-500/40 rounded-xl p-4">
                <p class="text-xs font-bold text-deli-400 mb-3 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 bg-deli-500 rounded-full inline-block animate-pulse"></span>
                    本日の出勤
                </p>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($workingCasts as $cast)
                    <a href="{{ route('cast.show', $cast->id) }}/" class="group text-center">
                        <div class="aspect-square overflow-hidden rounded-lg bg-surface-400 mb-1">
                            <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                                 class="img-onerror-cast w-full h-full object-cover">
                        </div>
                        <p class="text-[10px] text-[#C8C4BC] group-hover:text-gold-400 truncate">{{ $cast->name }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
            @else
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-4 text-center">
                <p class="text-xs text-[#6A6A7E] font-medium mb-1">本日の出勤</p>
                <p class="text-xs text-[#4A4A5E]">出勤情報は準備中です</p>
            </div>
            @endif

            {{-- 店舗情報サマリー --}}
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-4 text-sm space-y-2">
                @if($shop->price_60)
                <div class="flex justify-between">
                    <span class="text-[#6A6A7E]">60分〜</span>
                    <span class="text-gold-400 font-bold">¥{{ number_format($shop->price_60) }}</span>
                </div>
                @endif
                @if($shop->open_time || $shop->all_time)
                <div class="flex justify-between">
                    <span class="text-[#6A6A7E]">営業</span>
                    <span class="text-[#C8C4BC]">{{ $shop->all_time ? '24時間' : $shop->open_time . '〜' }}</span>
                </div>
                @endif
                @if($shop->area)
                <div class="flex justify-between">
                    <span class="text-[#6A6A7E]">エリア</span>
                    <span class="text-[#C8C4BC]">{{ $shop->area->name }}</span>
                </div>
                @endif
            </div>

            {{-- 利用エリア --}}
            @if($shop->eigyo_area || $shop->area || $shop->prefecture)
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-4">
                <p class="text-xs font-bold text-[#E8E4DC] mb-3 flex items-center gap-2">
                    <span class="w-1 h-4 bg-surface-200 rounded-full inline-block"></span>利用エリア
                </p>
                @if($shop->eigyo_area)
                <p class="text-xs text-[#B0AEAD] leading-relaxed mb-3 whitespace-pre-wrap">{{ $shop->eigyo_area }}</p>
                @endif
                <div class="space-y-1.5 text-xs">
                    @if($shop->area)
                    <a href="{{ route('shop.region', $shop->area->slug) }}/"
                       class="flex items-center justify-between text-deli-400 hover:text-deli-300 transition">
                        <span>{{ $shop->area->name }}の店舗一覧</span>
                        <span class="opacity-60">&rsaquo;</span>
                    </a>
                    @endif
                    @if($shop->prefecture && $shop->prefecture->slug)
                    <a href="{{ route('shop.region', $shop->prefecture->slug) }}/"
                       class="flex items-center justify-between text-[#8A8A9E] hover:text-deli-400 transition">
                        <span>{{ $shop->prefecture->name }}の店舗一覧</span>
                        <span class="opacity-60">&rsaquo;</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
@push('head')
@php
    $ld_breadcrumb = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type'=>'ListItem','position'=>1,'name'=>'ホーム','item'=>route('top').'/'],
            ['@type'=>'ListItem','position'=>2,'name'=>'店舗一覧','item'=>route('shop.index').'/'],
            ['@type'=>'ListItem','position'=>3,'name'=>$shop->name,'item'=>route('shop.show',$shop->id).'/'],
        ],
    ];
    $ld_local = [
        '@context'    => 'https://schema.org',
        '@type'       => 'LocalBusiness',
        'name'        => $shop->name,
        'telephone'   => $shop->tel ?? null,
        'address'     => $shop->address ? [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $shop->address,
            'addressLocality' => $shop->address_locality ?? null,
            'addressRegion'   => optional($shop->prefecture)->name,
            'postalCode'      => $shop->postal_code ?? null,
            'addressCountry'  => 'JP',
        ] : null,
        'image'       => $shop->banner_url ? [$shop->banner_url] : null,
        'openingHours' => $shop->all_time ? ['Mo-Su 00:00-23:59'] : null,
    ];
    // null を取り除く
    $ld_local = array_filter($ld_local, fn($v) => $v !== null);
    if (isset($ld_local['address'])) {
        $ld_local['address'] = array_filter($ld_local['address'], fn($v) => $v !== null);
    }
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($ld_breadcrumb, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) !!}</script>
<script type="application/ld+json" @nonce>{!! json_encode($ld_local, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) !!}</script>
@endpush
