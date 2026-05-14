@extends('layouts.app')

@section('title', $shop->name)
@section('description',
    ($shop->catche ?: $shop->name . 'の詳細情報。') .
    ($shop->price_60 ? '60分¥' . number_format($shop->price_60) . '〜。' : '') .
    'キャスト・システム・料金などをご紹介。'
)
@section('canonical', route('shop.show', $shop->id) . '/')
@if($noindex)
@section('robots', 'noindex,follow')
@endif
@if($shop->main_image_url)
@section('ogp_image', $shop->main_image_url)
@section('twitter_card', 'summary_large_image')
@endif

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 pb-20 md:pb-8">

    {{-- パンくず --}}
    <nav class="text-xs text-[#8A8A9E] mb-4">
        <a href="{{ route('top') }}/" class="hover:text-gold-400 transition">ホーム</a> &rsaquo;
        <a href="{{ route('shop.list', ['area_slug' => 'all']) }}/" class="hover:text-gold-400 transition">店舗一覧</a> &rsaquo;
        @if($shop->prefecture)
        <a href="{{ route('shop.list', ['area_slug' => $shop->prefecture->slug]) }}/" class="hover:text-gold-400 transition">{{ $shop->prefecture->name }}</a> &rsaquo;
        @endif
        @if($shop->area)
        <a href="{{ route('shop.list', ['area_slug' => $shop->area->slug]) }}/" class="hover:text-gold-400 transition">{{ $shop->area->name }}</a> &rsaquo;
        @endif
        <span class="text-[#B0AEAD]">{{ $shop->name }}</span>
    </nav>

    {{-- 店舗メイン画像 --}}
    @if($shop->main_image_url)
    <div class="relative w-full aspect-[4/3] rounded-xl overflow-hidden mb-6 bg-surface-600">
        <img src="{{ $shop->main_image_url }}" alt="{{ $shop->name }}"
             fetchpriority="high"
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
                            <span class="text-xs bg-surface-400 text-[#B0AEAD] px-2 py-0.5 rounded-full">{{ $shop->shopType2->name }}</span>
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
                        <th scope="row" class="py-2.5 pr-3 text-left text-[#8A8A9E] font-normal w-32">60分料金</th>
                        <td class="py-2.5 text-gold-400 font-bold">¥{{ number_format($shop->price_60) }}〜</td>
                    </tr>
                    @endif
                    @if($shop->price_90)
                    <tr class="border-b border-surface-300">
                        <th scope="row" class="py-2.5 pr-3 text-left text-[#8A8A9E] font-normal w-32">90分料金</th>
                        <td class="py-2.5 text-[#C8C4BC]">¥{{ number_format($shop->price_90) }}〜</td>
                    </tr>
                    @endif
                    @if($shop->price_120)
                    <tr class="border-b border-surface-300">
                        <th scope="row" class="py-2.5 pr-3 text-left text-[#8A8A9E] font-normal w-32">120分料金</th>
                        <td class="py-2.5 text-[#C8C4BC]">¥{{ number_format($shop->price_120) }}〜</td>
                    </tr>
                    @endif
                    @if($shop->price_high)
                    <tr class="border-b border-surface-300">
                        <th scope="row" class="py-2.5 pr-3 text-left text-[#8A8A9E] font-normal w-32">高級コース</th>
                        <td class="py-2.5 text-[#C8C4BC]">¥{{ number_format($shop->price_high) }}〜</td>
                    </tr>
                    @endif
                    @if($shop->open_time || $shop->close_time || $shop->all_time)
                    <tr class="border-b border-surface-300">
                        <th scope="row" class="py-2.5 pr-3 text-left text-[#8A8A9E] font-normal">営業時間</th>
                        <td class="py-2.5 text-[#C8C4BC]">
                            @if($shop->all_time) <span class="text-green-400 font-semibold">24時間営業</span>
                            @else {{ $shop->open_time }}{{ $shop->close_time ? ' 〜 ' . $shop->close_time : '' }} @endif
                        </td>
                    </tr>
                    @endif
                    @if($shop->rest_day)
                    <tr class="border-b border-surface-300">
                        <th scope="row" class="py-2.5 pr-3 text-left text-[#8A8A9E] font-normal">定休日</th>
                        <td class="py-2.5 text-[#C8C4BC]">{{ $shop->rest_day }}</td>
                    </tr>
                    @endif
                    @if($shop->eigyo_area)
                    <tr class="border-b border-surface-300">
                        <th scope="row" class="py-2.5 pr-3 text-left text-[#8A8A9E] font-normal">営業エリア</th>
                        <td class="py-2.5 text-[#C8C4BC]">{{ $shop->eigyo_area }}</td>
                    </tr>
                    @endif
                    @if($shop->eigyo_space)
                    <tr class="border-b border-surface-300">
                        <th scope="row" class="py-2.5 pr-3 text-left text-[#8A8A9E] font-normal">プレイスペース</th>
                        <td class="py-2.5 text-[#C8C4BC]">{{ $shop->eigyo_space }}</td>
                    </tr>
                    @endif
                    @if($shop->address)
                    <tr class="border-b border-surface-300">
                        <th scope="row" class="py-2.5 pr-3 text-left text-[#8A8A9E] font-normal">住所</th>
                        <td class="py-2.5 text-[#C8C4BC]">{{ $shop->address }}</td>
                    </tr>
                    @endif
                    @if($shop->tel)
                    <tr>
                        <th scope="row" class="py-2.5 pr-3 text-left text-[#8A8A9E] font-normal">電話番号</th>
                        <td class="py-2.5"><a href="tel:{{ $shop->tel }}" rel="nofollow" class="text-gold-400 hover:underline font-medium">{{ $shop->tel }}</a></td>
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
                <p class="text-xs text-[#8A8A9E] mb-2">特色・タグ</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($shop->tags as $tag)
                    <span class="text-xs bg-surface-400 text-[#B0AEAD] border border-surface-300 px-3 py-1 rounded-full">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 口コミセクション（在籍キャストへの口コミ） --}}
            @php
                $castIds = $shop->castMembers()->pluck('id');
                $approvedReviews = \App\Models\CastReview::whereIn('cast_id', $castIds)
                    ->where('is_approved', true)
                    ->with('cast:id,name')
                    ->orderByDesc('created_at')
                    ->take(5)
                    ->get();
            @endphp
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5 mb-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-[#E8E4DC] text-sm flex items-center gap-2">
                        <span class="w-1 h-4 bg-gold-400 rounded-full inline-block"></span>
                        口コミ <span class="text-[#8A8A9E] font-normal">({{ $approvedReviews->count() }}件)</span>
                    </h2>
                </div>
                @if($approvedReviews->isEmpty())
                <p class="text-sm text-[#8A8A9E] text-center py-4">まだ口コミがありません。</p>
                @else
                <div class="space-y-4">
                    @foreach($approvedReviews as $review)
                    <div class="border-b border-surface-300 pb-4 last:border-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <span class="text-amber-400 text-sm">{{ str_repeat('★', $review->rating) }}<span class="text-[#3A3A4E]">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                            <span class="text-xs font-medium text-[#C8C4BC]">{{ $review->cast?->name }}</span>
                            <span class="text-xs text-[#8A8A9E]">{{ $review->nickname ?? '匿名' }}</span>
                            <span class="text-xs text-[#4A4A5E]">{{ $review->created_at->format('Y/m/d') }}</span>
                        </div>
                        <p class="text-sm text-[#B0AEAD] leading-relaxed">{{ $review->body }}</p>
                        @if($review->shop_reply)
                        <div class="mt-2 bg-surface-600 border border-surface-400 rounded-lg px-3 py-2">
                            <p class="text-xs text-[#6A6A7E] mb-0.5">店舗からの返信</p>
                            <p class="text-xs text-[#8A8A9E] leading-relaxed whitespace-pre-wrap">{{ $review->shop_reply }}</p>
                        </div>
                        @endif
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
                        在籍キャスト <span class="text-[#8A8A9E] font-normal">({{ $casts->total() }}名)</span>
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
                            <span class="absolute top-1.5 left-1.5 text-[10px] font-bold bg-emerald-500 text-white px-1.5 py-0.5 rounded-full leading-none">本日出勤</span>
                            @elseif($isNew)
                            <span class="absolute top-1.5 left-1.5 text-[10px] font-bold bg-gold-500 text-surface-800 px-1.5 py-0.5 rounded-full leading-none">NEW</span>
                            @endif
                        </div>
                        <p class="text-xs font-medium text-[#D8D4CC] group-hover:text-gold-400 transition truncate">{{ $cast->name }}</p>
                        @if($cast->age)
                        <p class="text-xs text-[#8A8A9E]">{{ $cast->age }}歳</p>
                        @endif
                    </a>
                    @empty
                    <p class="col-span-3 text-sm text-[#8A8A9E] py-4 text-center">キャスト情報がありません</p>
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
            <div class="hidden md:block bg-deli-500 rounded-xl p-5 text-center">
                <a href="tel:{{ $shop->tel }}" rel="nofollow" class="block text-white text-base font-bold hover:opacity-90 transition">この店舗に問い合わせする</a>
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
                <p class="text-xs text-[#8A8A9E] font-medium mb-1">本日の出勤</p>
                <p class="text-xs text-[#4A4A5E]">出勤情報は準備中です</p>
            </div>
            @endif

            {{-- 店舗情報サマリー --}}
            <div class="hidden md:block bg-surface-500 border border-surface-300 rounded-xl p-4 text-sm space-y-2">
                @if($shop->price_60)
                <div class="flex justify-between">
                    <span class="text-[#8A8A9E]">60分〜</span>
                    <span class="text-gold-400 font-bold">¥{{ number_format($shop->price_60) }}</span>
                </div>
                @endif
                @if($shop->open_time || $shop->all_time)
                <div class="flex justify-between">
                    <span class="text-[#8A8A9E]">営業</span>
                    <span class="text-[#C8C4BC]">{{ $shop->all_time ? '24時間' : $shop->open_time . '〜' }}</span>
                </div>
                @endif
                @if($shop->area)
                <div class="flex justify-between">
                    <span class="text-[#8A8A9E]">エリア</span>
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
                    <a href="{{ route('shop.list', ['area_slug' => $shop->area->slug]) }}/"
                       class="flex items-center justify-between text-deli-400 hover:text-deli-300 transition">
                        <span>{{ $shop->area->name }}の店舗一覧</span>
                        <span class="opacity-60">&rsaquo;</span>
                    </a>
                    @endif
                    @if($shop->prefecture && $shop->prefecture->slug)
                    <a href="{{ route('shop.list', ['area_slug' => $shop->prefecture->slug]) }}/"
                       class="flex items-center justify-between text-[#B0AEAD] hover:text-deli-400 transition">
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

{{-- 近隣有料掲載店（無料店ページのみ） --}}
@if($nearbyPaidShops->isNotEmpty())
<div class="max-w-5xl mx-auto px-4 pb-10">
    <h2 class="text-sm font-bold text-[#8A8A9E] mb-4 flex items-center gap-2">
        <span class="w-1 h-4 bg-deli-500 rounded-full inline-block"></span>
        このエリアのおすすめ店舗
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach($nearbyPaidShops as $ps)
        @php
            $psThumb    = $ps->main_image ? \App\Services\ImageService::thumbWebpPath($ps->main_image) : null;
            $psThumbJpg = $ps->main_image ? \App\Services\ImageService::thumbJpgPath($ps->main_image) : null;
        @endphp
        <a href="{{ route('shop.show', $ps->id) }}/"
           class="bg-surface-500 border border-surface-300 rounded-xl overflow-hidden hover:border-deli-500 transition group block">
            @if($psThumb)
            <picture>
                <source srcset="{{ Storage::url($psThumb) }}" type="image/webp">
                <img src="{{ Storage::url($psThumbJpg) }}" alt="{{ $ps->name }}"
                     class="w-full aspect-video object-cover group-hover:opacity-90 transition" loading="lazy" width="224" height="126">
            </picture>
            @endif
            <div class="p-3">
                <p class="text-sm font-bold text-[#E8E4DC] truncate group-hover:text-deli-400 transition">{{ $ps->name }}</p>
                @if($ps->area)
                <p class="text-xs text-[#8A8A9E] mt-0.5">{{ $ps->area->name }}</p>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

{{-- フロートバー（スマホのみ） --}}
<div class="md:hidden fixed bottom-0 left-0 right-0 z-50 p-3 bg-surface-900/95 backdrop-blur border-t border-surface-500">
    <div class="flex gap-2">

        {{-- 新人通知ボタン --}}
        <button id="notify-btn"
                data-shop="{{ $shop->id }}"
                data-subscribed="{{ $isSubscribed ? 'true' : 'false' }}"
                class="flex-1 flex items-center justify-center gap-1.5 py-3 rounded-xl border text-xs font-medium transition
                       {{ $isSubscribed
                           ? 'bg-deli-500/20 border-deli-500 text-deli-400'
                           : 'bg-surface-700 border-surface-400 text-[#B0AEAD]' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span id="notify-label">{{ $isSubscribed ? '通知登録済み' : '新人通知を受け取る' }}</span>
        </button>

        {{-- 電話ボタン --}}
        @if($shop->tel)
        <a href="tel:{{ $shop->tel }}"
           rel="nofollow"
           class="flex-1 flex items-center justify-center gap-1.5 py-3 rounded-xl bg-deli-500 hover:bg-deli-600 active:bg-deli-700 text-white text-xs font-bold transition">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            このお店に電話する
        </a>
        @else
        <div class="flex-1"></div>
        @endif

    </div>
</div>

@push('scripts')
<script @nonce>
(function() {
    var btn = document.getElementById('notify-btn');
    if (!btn) return;
    @auth
    btn.addEventListener('click', function() {
        fetch('/shops/' + btn.dataset.shop + '/notify/', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            }
        }).then(function(r){ return r.json(); }).then(function(data) {
            btn.dataset.subscribed = data.subscribed ? 'true' : 'false';
            document.getElementById('notify-label').textContent = data.subscribed ? '通知登録済み' : '新人通知を受け取る';
            if (data.subscribed) {
                btn.classList.add('bg-deli-500/20','border-deli-500','text-deli-400');
                btn.classList.remove('bg-surface-700','border-surface-400','text-[#B0AEAD]');
            } else {
                btn.classList.remove('bg-deli-500/20','border-deli-500','text-deli-400');
                btn.classList.add('bg-surface-700','border-surface-400','text-[#B0AEAD]');
            }
        });
    });
    @else
    btn.addEventListener('click', function() {
        document.getElementById('auth-modal').classList.remove('hidden');
    });
    document.getElementById('auth-modal-close').addEventListener('click', function() {
        document.getElementById('auth-modal').classList.add('hidden');
    });
    document.getElementById('auth-modal-backdrop').addEventListener('click', function() {
        document.getElementById('auth-modal').classList.add('hidden');
    });
    @endauth
})();
</script>
@endpush

{{-- 非ログイン通知モーダル --}}
<div id="auth-modal" class="hidden fixed inset-0 z-[200] flex items-center justify-center p-4">
    <div id="auth-modal-backdrop" class="absolute inset-0 bg-black/60"></div>
    <div class="relative bg-surface-700 border border-surface-400 rounded-2xl p-6 mx-4 max-w-sm w-full shadow-xl">
        <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-deli-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="text-[#E8E4DC] font-bold text-sm">ログインが必要です</p>
        </div>
        <p class="text-[#B0AEAD] text-sm mb-5">出勤通知を受け取るにはログインが必要です。</p>
        <div class="flex gap-2">
            <button id="auth-modal-close" class="flex-1 py-2.5 rounded-xl border border-surface-400 text-[#B0AEAD] text-sm transition hover:border-surface-300">閉じる</button>
            <a href="{{ route('login') }}/" class="flex-1 py-2.5 rounded-xl bg-deli-500 hover:bg-deli-400 text-white text-sm font-bold text-center transition">ログインする</a>
        </div>
    </div>
</div>
@endsection
@push('head')
@php
    $ld_breadcrumb = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => (function() use ($shop) {
            $items = [
                ['name'=>'ホーム',   'item'=>route('top').'/'],
                ['name'=>'店舗一覧','item'=>route('shop.list',['area_slug'=>'all']).'/'],
            ];
            if ($shop->prefecture) $items[] = ['name'=>$shop->prefecture->name,'item'=>route('shop.list',['area_slug'=>$shop->prefecture->slug]).'/'];
            if ($shop->area)       $items[] = ['name'=>$shop->area->name,      'item'=>route('shop.list',['area_slug'=>$shop->area->slug]).'/'];
            $items[] = ['name'=>$shop->name,'item'=>route('shop.show',$shop->id).'/'];
            return array_map(fn($item,$i)=>array_merge(['@type'=>'ListItem','position'=>$i+1],$item),array_values($items),array_keys($items));
        })(),
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
        'image'       => $shop->main_image_url ? [$shop->main_image_url] : null,
        'openingHours' => $shop->all_time ? ['Mo-Su 00:00-23:59'] : null,
    ];
    // null を取り除く
    $ld_local = array_filter($ld_local, fn($v) => $v !== null);
    if (isset($ld_local['address'])) {
        $ld_local['address'] = array_filter($ld_local['address'], fn($v) => $v !== null);
    }

    $castIdsForRating = $shop->castMembers()->pluck('id');
    $ratingStats = \Illuminate\Support\Facades\DB::table('cast_reviews')
        ->whereIn('cast_id', $castIdsForRating)
        ->where('is_approved', true)
        ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')
        ->first();
    if ($ratingStats && $ratingStats->cnt > 0) {
        $ld_local['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => round($ratingStats->avg_rating, 1),
            'bestRating'  => 5,
            'worstRating' => 1,
            'ratingCount' => (int) $ratingStats->cnt,
        ];
    }
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($ld_breadcrumb, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) !!}</script>
<script type="application/ld+json" @nonce>{!! json_encode($ld_local, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) !!}</script>
@endpush