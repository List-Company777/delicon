@extends('layouts.app')
@section('title', 'マイページ')
@section('robots', 'noindex')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-[#F0ECE4] mb-6 flex items-center gap-3">
        <span class="w-1 h-7 bg-deli-500 rounded-full inline-block"></span>
        マイページ
    </h1>

    <div class="flex gap-4 mb-8 text-sm border-b border-surface-300">
        <a href="{{ route('user.dashboard') }}/" class="text-deli-400 border-b-2 border-deli-500 pb-2">お気に入り / 閲覧履歴</a>
        <a href="{{ route('user.settings') }}/?tab=notify" class="text-[#6A6A7E] hover:text-[#C8C4BC] pb-2 transition">新人通知</a>
        <a href="{{ route('user.settings') }}/?tab=prefs" class="text-[#6A6A7E] hover:text-[#C8C4BC] pb-2 transition">好み</a>
    </div>

    {{-- あなたにおすすめ --}}
    @if($recommendations->isNotEmpty())
    <section class="mb-10">
        <h2 class="font-bold text-[#E8E4DC] text-sm mb-1 flex items-center gap-2">
            <span class="w-1 h-4 bg-gold-500 rounded-full"></span>
            あなたにおすすめ
        </h2>
        <p class="text-xs text-[#6A6A7E] mb-4">好みの設定に合ったキャストです。<a href="{{ route('user.settings') }}/?tab=prefs" class="text-deli-400 hover:underline">設定を変更する</a></p>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
            @foreach($recommendations as $cast)
            <a href="{{ route('cast.show', $cast->id) }}/" class="group text-center">
                <div class="relative aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 mb-1.5 border border-surface-300 group-hover:border-gold-500 transition">
                    <img src="{{ $cast->img_url ?? '/img/no-cast.jpg' }}" alt="{{ $cast->name }}"
                         class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
                    @if($cast->working_date && $cast->working_date->isToday())
                    <span class="absolute top-1 left-1 text-[9px] font-bold bg-emerald-500 text-white px-1.5 py-0.5 rounded-full">待機中</span>
                    @endif
                </div>
                <p class="text-xs font-medium text-[#D8D4CC] group-hover:text-gold-400 truncate">{{ $cast->name }}</p>
                @if($cast->castType)<p class="text-[10px] text-deli-400 truncate">{{ $cast->castType->name }}</p>@endif
                @if($cast->shop)<p class="text-[10px] text-[#6A6A7E] truncate">{{ $cast->shop->name }}</p>@endif
            </a>
            @endforeach
        </div>
    </section>
    @else
    <section class="mb-10 bg-surface-500 border border-dashed border-surface-300 rounded-xl p-5 text-center">
        <p class="text-sm text-[#9A96A0] mb-2">好みの設定をすると「あなたにおすすめ」が表示されます</p>
        <a href="{{ route('user.settings') }}/?tab=prefs" class="text-xs text-deli-400 hover:underline">好みを設定する →</a>
    </section>
    @endif

    {{-- お気に入りキャスト --}}
    <section class="mb-10">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-[#E8E4DC] text-sm flex items-center gap-2">
                <span class="w-1 h-4 bg-deli-500 rounded-full"></span>
                お気に入りキャスト <span class="text-[#6A6A7E] font-normal">({{ $favorites->count() }}名)</span>
            </h2>
            <form action="{{ route('user.notify-working.toggle') }}/" method="POST">
                @csrf
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="_toggle" onchange="this.form.submit()"
                           @checked(auth()->user()->notify_working)
                           class="rounded border-surface-300 bg-surface-600 text-deli-500 focus:ring-deli-500">
                    <span class="text-xs text-[#B0AEAD]">お気に入り出勤通知を受け取る</span>
                </label>
            </form>
        </div>
        @if(session('notify_working_updated'))
        <p class="text-xs text-deli-400 mb-3">通知設定を更新しました。</p>
        @endif
        @if($favorites->isEmpty())
        <p class="text-sm text-[#6A6A7E]">まだお気に入り登録がありません。キャストのページ右上の ♡ ボタンで登録できます。</p>
        @else
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
            @foreach($favorites as $cast)
            @php
                $schedDate = $scheduleMap[$cast->id] ?? null;
                if ($schedDate) {
                    $diffDays = $schedDate->diffInDays(\Carbon\Carbon::today(), false);
                    // diffInDays with false: positive = past, negative = future
                    // actually for future dates we need:
                    $diffDays = \Carbon\Carbon::today()->diffInDays($schedDate, false);
                }
            @endphp
            <a href="{{ route('cast.show', $cast->id) }}/" class="group text-center">
                <div class="relative aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 mb-1.5 border border-surface-300 group-hover:border-deli-500 transition">
                    <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                         class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
                    @if($schedDate)
                        @if($diffDays === 0)
                        <span class="absolute top-1 left-1 text-[9px] font-bold bg-deli-500 text-white px-1.5 py-0.5 rounded-full whitespace-nowrap">本日出勤</span>
                        @elseif($diffDays === 1)
                        <span class="absolute top-1 left-1 text-[9px] font-bold bg-amber-500 text-white px-1.5 py-0.5 rounded-full whitespace-nowrap">明日出勤</span>
                        @elseif($diffDays === 2)
                        <span class="absolute top-1 left-1 text-[9px] font-bold bg-surface-200 text-[#1a1a2e] px-1.5 py-0.5 rounded-full whitespace-nowrap">明後日出勤</span>
                        @endif
                    @endif
                </div>
                <p class="text-xs font-medium text-[#D8D4CC] group-hover:text-gold-400 truncate">{{ $cast->name }}</p>
                @if($cast->shop)<p class="text-[10px] text-[#6A6A7E] truncate">{{ $cast->shop->name }}</p>@endif
            </a>
            @endforeach
        </div>
        @endif
    </section>

    {{-- 閲覧履歴（最新10件） --}}
    <section>
        <h2 class="font-bold text-[#E8E4DC] text-sm mb-4 flex items-center gap-2">
            <span class="w-1 h-4 bg-surface-200 rounded-full"></span>
            最近見たキャスト
        </h2>
        @if($recentlyViewed->isEmpty())
        <p class="text-sm text-[#6A6A7E]">閲覧履歴がありません。</p>
        @else
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 mb-3">
            @foreach($recentlyViewed as $view)
            @if($view->cast)
            <a href="{{ route('cast.show', $view->cast->id) }}/" class="group text-center">
                <div class="aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 mb-1.5 border border-surface-300 group-hover:border-deli-500 transition">
                    <img src="{{ $view->cast->img_url }}" alt="{{ $view->cast->name }}"
                         class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition" loading="lazy">
                </div>
                <p class="text-xs font-medium text-[#D8D4CC] group-hover:text-gold-400 truncate">{{ $view->cast->name }}</p>
                <p class="text-[10px] text-[#6A6A7E]">{{ $view->viewed_at->diffForHumans() }}</p>
            </a>
            @endif
            @endforeach
        </div>
        @endif
    </section>
</div>
@endsection
