@extends('layouts.app')

@section('title', $cast->name . ' | デリコン')
@section('description',
    $cast->name . '(' . ($cast->age ? $cast->age . '歳' : '') . ')' .
    ($cast->castType ? '・' . $cast->castType->name : '') .
    ($cast->shop ? ' - ' . $cast->shop->name : '') .
    ($cast->comment ? '。' . mb_strimwidth(strip_tags($cast->comment), 0, 60, '…') : '')
)
@section('canonical', route('cast.show', $cast->id) . '/')
@if($cast->img_url !== '/img/no-cast.jpg')
@section('ogp_image', url($cast->img_url))
@section('twitter_card', 'summary_large_image')
@endif

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    {{-- パンくず --}}
    <nav class="text-xs text-gray-500 mb-4">
        <a href="{{ route('top') }}/" class="hover:text-red-600">ホーム</a> &rsaquo;
        <a href="{{ route('cast.index') }}/" class="hover:text-red-600">キャスト検索</a> &rsaquo;
        @if($cast->shop)
        <a href="{{ route('shop.show', $cast->shop->id) }}/" class="hover:text-red-600">{{ $cast->shop->name }}</a> &rsaquo;
        @endif
        <span>{{ $cast->name }}</span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- 左カラム：写真 --}}
        <div>
            <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                 class="w-full rounded-lg shadow mb-3" onerror="this.src='/img/no-cast.jpg'">
            @if($cast->images->count() > 0)
            <div class="grid grid-cols-3 gap-1">
                @foreach($cast->images as $img)
                <img src="{{ $img->img_path }}" alt="{{ $cast->name }}"
                     class="w-full aspect-square object-cover rounded" loading="lazy">
                @endforeach
            </div>
            @endif
        </div>

        {{-- 右カラム：情報 --}}
        <div class="md:col-span-2">
            {{-- プロフィールヘッダー --}}
            <div class="bg-white rounded-lg shadow p-5 mb-4">
                <div class="flex flex-wrap gap-1 mb-2">
                    @if($cast->castType)
                    <span class="text-xs bg-pink-100 text-pink-700 px-2 py-0.5 rounded">{{ $cast->castType->name }}</span>
                    @endif
                    @if($cast->is_recommended)
                    <span class="text-xs bg-red-500 text-white px-2 py-0.5 rounded">おすすめ</span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $cast->name }}</h1>
                @if($cast->shop)
                <p class="text-sm text-gray-500">
                    <a href="{{ route('shop.show', $cast->shop->id) }}/" class="hover:text-red-600 transition">
                        {{ $cast->shop->name }}
                    </a>
                </p>
                @endif
                @if($cast->comment)
                <div class="mt-3 text-sm text-gray-700 leading-relaxed bg-pink-50 rounded p-3">
                    <p>{!! nl2br(e($cast->comment)) !!}</p>
                </div>
                @endif
            </div>

            {{-- スペック --}}
            <div class="bg-white rounded-lg shadow p-5 mb-4">
                <h2 class="font-bold text-gray-800 mb-3 text-sm">プロフィール</h2>
                <div class="grid grid-cols-2 gap-x-4 text-sm">
                    @if($cast->age)
                    <div class="border-b py-2 flex justify-between"><span class="text-gray-500">年齢</span><span>{{ $cast->age }}歳</span></div>
                    @endif
                    @if($cast->tall)
                    <div class="border-b py-2 flex justify-between"><span class="text-gray-500">身長</span><span>{{ $cast->tall }}cm</span></div>
                    @endif
                    @if($cast->bust && $cast->cup)
                    <div class="border-b py-2 flex justify-between"><span class="text-gray-500">バスト</span><span>{{ $cast->bust }}cm {{ $cast->cup }}カップ</span></div>
                    @endif
                    @if($cast->west)
                    <div class="border-b py-2 flex justify-between"><span class="text-gray-500">ウエスト</span><span>{{ $cast->west }}cm</span></div>
                    @endif
                    @if($cast->hip)
                    <div class="border-b py-2 flex justify-between"><span class="text-gray-500">ヒップ</span><span>{{ $cast->hip }}cm</span></div>
                    @endif
                    @if($cast->bodyType)
                    <div class="border-b py-2 flex justify-between"><span class="text-gray-500">スタイル</span><span>{{ $cast->bodyType->name }}</span></div>
                    @endif
                    @if($cast->blood)
                    <div class="border-b py-2 flex justify-between"><span class="text-gray-500">血液型</span><span>{{ $cast->blood }}型</span></div>
                    @endif
                    @if($cast->country)
                    <div class="border-b py-2 flex justify-between"><span class="text-gray-500">出身</span><span>{{ $cast->country }}</span></div>
                    @endif
                    @if($cast->price_on)
                    <div class="border-b py-2 flex justify-between col-span-2"><span class="text-gray-500">指名料</span><span class="text-red-600 font-medium">¥{{ number_format($cast->price_on) }}</span></div>
                    @endif
                </div>
            </div>

            {{-- 自己紹介・メッセージ --}}
            @if($cast->message)
            <div class="bg-white rounded-lg shadow p-5 mb-4">
                <h2 class="font-bold text-gray-800 mb-2 text-sm">キャストからのメッセージ</h2>
                <p class="text-sm text-gray-700 leading-relaxed">{!! nl2br(e($cast->message)) !!}</p>
            </div>
            @endif

            {{-- 詳細プロフィール --}}
            @php $details = array_filter([
                '初体験' => $cast->hatsutaiken,
                '性感帯' => $cast->seikantai,
                '得意技' => $cast->tokuiwaza,
                '好きなタイプ' => $cast->sukinatype,
                '趣味' => $cast->shumi,
                '前職' => $cast->zenshoku,
                '好きな食べ物' => $cast->likeeat,
                '芸能人似' => $cast->yuumeijin,
                '潮吹き' => $cast->shiofuki,
                '自宅派/ホテル派' => $cast->zitaku,
                '星座' => $cast->seiza,
            ]); @endphp
            @if(count($details) > 0)
            <div class="bg-white rounded-lg shadow p-5 mb-4">
                <h2 class="font-bold text-gray-800 mb-3 text-sm">詳細プロフィール</h2>
                <div class="grid grid-cols-2 gap-x-4 text-sm">
                    @foreach($details as $label => $value)
                    <div class="border-b py-2 flex justify-between gap-2">
                        <span class="text-gray-500 shrink-0">{{ $label }}</span>
                        <span class="text-right">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- チャームポイント --}}
            @if($cast->charms->count() > 0)
            <div class="bg-white rounded-lg shadow p-5 mb-4">
                <h2 class="font-bold text-gray-800 mb-2 text-sm">チャームポイント</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($cast->charms as $charm)
                    <span class="bg-pink-50 text-pink-700 text-xs px-2 py-1 rounded">{{ $charm->name }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- プレイ --}}
            @if($cast->plays->count() > 0)
            <div class="bg-white rounded-lg shadow p-5 mb-4">
                <h2 class="font-bold text-gray-800 mb-2 text-sm">可能プレイ</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($cast->plays as $play)
                    <span class="bg-red-50 text-red-700 text-xs px-2 py-1 rounded">{{ $play->name }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- タグ --}}
            @if($cast->tags->count() > 0)
            <div class="bg-white rounded-lg shadow p-5 mb-4">
                <h2 class="font-bold text-gray-800 mb-2 text-sm">タグ</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($cast->tags as $tag)
                    <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">{{ $tag->name }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 出勤スケジュール --}}
            @if($cast->schedules->count() > 0)
            <div class="bg-white rounded-lg shadow p-5 mb-4">
                <h2 class="font-bold text-gray-800 mb-3 text-sm">出勤スケジュール</h2>
                <div class="space-y-1 text-sm">
                    @foreach($cast->schedules->take(7) as $schedule)
                    <div class="flex items-center gap-3">
                        <span class="text-gray-500 w-20 shrink-0">{{ $schedule->work_date->format('m/d(D)') }}</span>
                        <span>{{ $schedule->start_time }}〜{{ $schedule->end_time }}</span>
                        @if($schedule->note)<span class="text-gray-400">{{ $schedule->note }}</span>@endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- SNS --}}
            @if($cast->twitter_account || $cast->official_url)
            <div class="bg-white rounded-lg shadow p-5 mb-4 flex gap-3 text-sm">
                @if($cast->twitter_account)
                <a href="https://twitter.com/{{ ltrim($cast->twitter_account, '@') }}" target="_blank" rel="noopener nofollow"
                   class="text-blue-400 hover:underline">Twitter/X</a>
                @endif
                @if($cast->official_url)
                <a href="{{ $cast->official_url }}" target="_blank" rel="noopener nofollow"
                   class="text-gray-600 hover:underline">公式ページ</a>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- 同店舗の他のキャスト --}}
    @if($otherCasts->count() > 0)
    <div class="mt-8">
        <h2 class="text-lg font-bold text-gray-800 mb-4">
            @if($cast->shop)<a href="{{ route('shop.show', $cast->shop->id) }}/" class="hover:text-red-600">{{ $cast->shop->name }}</a>の@endif在籍キャスト
        </h2>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
            @foreach($otherCasts as $other)
            <a href="{{ route('cast.show', $other->id) }}/" class="group text-center">
                <img src="{{ $other->img_url }}" alt="{{ $other->name }}"
                     class="w-full aspect-[3/4] object-cover rounded group-hover:opacity-90 transition"
                     loading="lazy" onerror="this.src='/img/no-cast.jpg'">
                <p class="text-xs mt-1 group-hover:text-red-600 transition">{{ $other->name }}</p>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
