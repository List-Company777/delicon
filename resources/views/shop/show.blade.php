@extends('layouts.app')

@section('title', $shop->name . ' | デリコン')
@section('description',
    ($shop->catche ?: $shop->name . 'の詳細情報。') .
    ($shop->price_60 ? '60分¥' . number_format($shop->price_60) . '〜。' : '') .
    'キャスト・システム・料金などをご紹介。'
)
@section('canonical', route('shop.show', $shop->id) . '/')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    {{-- パンくず --}}
    <nav class="text-xs text-gray-500 mb-4">
        <a href="{{ route('top') }}/" class="hover:text-red-600">ホーム</a> &rsaquo;
        <a href="{{ route('shop.index') }}/" class="hover:text-red-600">店舗一覧</a> &rsaquo;
        <span>{{ $shop->name }}</span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- メイン情報 --}}
        <div class="md:col-span-2">
            {{-- 店舗ヘッダー --}}
            <div class="bg-white rounded-lg shadow p-5 mb-5">
                <div class="flex gap-4">
                    @if($shop->shop_file_name)
                    <img src="/img/{{ ltrim($shop->shop_file_name, '/') }}" alt="{{ $shop->name }}"
                         class="w-32 h-24 object-cover rounded shrink-0" onerror="this.style.display='none'">
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap gap-1 mb-2">
                            @if($shop->shopType)
                            <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">{{ $shop->shopType->name }}</span>
                            @endif
                            @if($shop->shopType2)
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $shop->shopType2->name }}</span>
                            @endif
                        </div>
                        <h1 class="text-xl font-bold text-gray-900 mb-1">{{ $shop->name }}</h1>
                        @if($shop->catche)
                        <p class="text-sm text-red-600 font-medium">{{ $shop->catche }}</p>
                        @endif
                    </div>
                </div>

                @if($shop->base)
                <div class="mt-4 text-sm text-gray-700 leading-relaxed">
                    <p>{!! nl2br(e($shop->base)) !!}</p>
                </div>
                @endif
            </div>

            {{-- システム --}}
            @if($shop->system_text || $shop->price_60 || $shop->open_time)
            <div class="bg-white rounded-lg shadow p-5 mb-5">
                <h2 class="font-bold text-gray-800 mb-3 text-sm">基本情報・システム</h2>
                <table class="w-full text-sm">
                    @if($shop->price_60)
                    <tr class="border-b"><th class="py-2 pr-3 text-left text-gray-500 w-32">60分料金</th><td class="py-2 text-red-600 font-medium">¥{{ number_format($shop->price_60) }}〜</td></tr>
                    @endif
                    @if($shop->price_90)
                    <tr class="border-b"><th class="py-2 pr-3 text-left text-gray-500 w-32">90分料金</th><td class="py-2">¥{{ number_format($shop->price_90) }}〜</td></tr>
                    @endif
                    @if($shop->price_120)
                    <tr class="border-b"><th class="py-2 pr-3 text-left text-gray-500 w-32">120分料金</th><td class="py-2">¥{{ number_format($shop->price_120) }}〜</td></tr>
                    @endif
                    @if($shop->open_time || $shop->close_time)
                    <tr class="border-b"><th class="py-2 pr-3 text-left text-gray-500">営業時間</th><td class="py-2">
                        @if($shop->all_time) 24時間
                        @else {{ $shop->open_time }} 〜 {{ $shop->close_time }} @endif
                    </td></tr>
                    @endif
                    @if($shop->rest_day)
                    <tr class="border-b"><th class="py-2 pr-3 text-left text-gray-500">定休日</th><td class="py-2">{{ $shop->rest_day }}</td></tr>
                    @endif
                    @if($shop->eigyo_area)
                    <tr class="border-b"><th class="py-2 pr-3 text-left text-gray-500">営業エリア</th><td class="py-2">{{ $shop->eigyo_area }}</td></tr>
                    @endif
                    @if($shop->address)
                    <tr class="border-b"><th class="py-2 pr-3 text-left text-gray-500">住所</th><td class="py-2">{{ $shop->address }}</td></tr>
                    @endif
                    @if($shop->tel)
                    <tr><th class="py-2 pr-3 text-left text-gray-500">電話番号</th><td class="py-2"><a href="tel:{{ $shop->tel }}" class="text-blue-600">{{ $shop->tel }}</a></td></tr>
                    @endif
                </table>

                @if($shop->system_text)
                <div class="mt-4 bg-gray-50 rounded p-3 text-sm text-gray-700 leading-relaxed">
                    <p class="font-medium text-gray-800 mb-2">システム詳細</p>
                    <p>{!! nl2br(e($shop->system_text)) !!}</p>
                </div>
                @endif
            </div>
            @endif

            {{-- クーポン --}}
            @if($shop->coupon)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-5">
                <h2 class="font-bold text-yellow-800 mb-2 text-sm">クーポン・特典</h2>
                <p class="text-sm text-yellow-900">{!! nl2br(e($shop->coupon)) !!}</p>
            </div>
            @endif

            {{-- キャスト一覧 --}}
            <div class="bg-white rounded-lg shadow p-5 mb-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-800">在籍キャスト <span class="text-gray-500 font-normal text-sm">({{ $casts->total() }}名)</span></h2>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @forelse($casts as $cast)
                    <a href="{{ route('cast.show', $cast->id) }}/" class="group text-center">
                        <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                             class="w-full aspect-[3/4] object-cover rounded mb-1 group-hover:opacity-90 transition"
                             loading="lazy" onerror="this.src='/img/no-cast.jpg'">
                        <p class="text-xs font-medium group-hover:text-red-600 transition">{{ $cast->name }}</p>
                        @if($cast->castType)
                        <p class="text-xs text-gray-400">{{ $cast->castType->name }}</p>
                        @endif
                        @if($cast->age)
                        <p class="text-xs text-gray-500">{{ $cast->age }}歳</p>
                        @endif
                    </a>
                    @empty
                    <p class="col-span-3 text-sm text-gray-400 py-4">キャスト情報がありません</p>
                    @endforelse
                </div>
                @if($casts->hasPages())
                <div class="mt-4">{{ $casts->links() }}</div>
                @endif
            </div>

            {{-- ニュース --}}
            @if($news->count() > 0)
            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="font-bold text-gray-800 mb-3">店舗からのお知らせ</h2>
                <div class="space-y-3">
                    @foreach($news as $item)
                    <div class="border-b pb-3 last:border-0">
                        <p class="text-xs text-gray-400 mb-1">{{ $item->created_at?->format('Y/m/d') }}</p>
                        <p class="text-sm text-gray-700">{!! nl2br(e($item->body)) !!}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- サイドバー --}}
        <div>
            <div class="bg-red-600 text-white rounded-lg p-4 text-center mb-4">
                @if($shop->tel)
                <a href="tel:{{ $shop->tel }}" class="block text-xl font-bold mb-1">{{ $shop->tel }}</a>
                <p class="text-xs opacity-80">お電話でのご予約</p>
                @else
                <p class="text-sm">お問い合わせは店舗HPへ</p>
                @endif
            </div>

            {{-- 本日の出勤キャスト（近日対応予定） --}}
            <div class="bg-white rounded-lg shadow p-4 text-sm text-gray-500 text-center">
                <p class="font-medium text-gray-700 mb-2">本日の出勤キャスト</p>
                <p class="text-xs">スケジュール機能は近日公開予定</p>
            </div>
        </div>

    </div>
</div>
@endsection
