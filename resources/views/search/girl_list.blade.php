@extends('layouts.app')

@php
    $tabLabels = ['all' => '女性一覧', 'standby' => '待機中', 'new' => '新人', 'diary' => '写メ日記', 'review' => '口コミ'];
    $tabLabel  = $cast_tab === 'type' ? ($typeName ?? '女性一覧') : ($tabLabels[$cast_tab] ?? '女性一覧');
    $suffix    = 'デリヘル・風俗';
    $pageTitle = $areaName
        ? "{$areaName}の{$tabLabel} | {$suffix}"
        : "{$tabLabel}一覧 | {$suffix}";
    // アクティブなフィルターパラメータ
    $activeAge  = request('age');
    $activeTall = request('tall');
    $activeCup  = request('cup');
    $activeBody = request('body');

    $filterParams = array_filter([
        'age'  => $activeAge,
        'tall' => $activeTall,
        'cup'  => $activeCup,
        'body' => $activeBody,
    ]);
    $filterCount = count($filterParams);

    // canonical URL: フィルターあり→フィルター付きURL（page除く）、なし→base URL
    $baseTabUrl = $cast_tab === 'type'
        ? url("/{$area_slug}/girl-list/type/{$type_slug}/") . '/'
        : ($cast_tab === 'all'
            ? url("/{$area_slug}/girl-list/") . '/'
            : url("/{$area_slug}/girl-list/{$cast_tab}/") . '/');
    $canonicalUrl = $filterCount > 0
        ? $baseTabUrl . '?' . http_build_query($filterParams)
        : $baseTabUrl;

    // noindex: 結果5件以下 OR フィルター2つ以上（フィルター1つ+5件超はindex）
    $noindex = $results->total() <= 5 || $filterCount >= 2;

    // フィルター定義
    $ageRanges = \App\Http\Controllers\GirlListController::ageRanges();
    $tallRanges = \App\Http\Controllers\GirlListController::tallRanges();
    $cupGroups  = \App\Http\Controllers\GirlListController::cupGroups();

    $showFilters = in_array($cast_tab, ['all', 'standby', 'new']);

    // フィルターリンク生成ヘルパー（特定パラメータをトグル）
    $filterUrl = function (string $key, string $value) {
        $params = request()->query();
        if (isset($params[$key]) && $params[$key] === $value) {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
        unset($params['page']);
        return request()->url() . '/' . ($params ? '?' . http_build_query($params) : '');
    };
    $clearUrl = request()->url() . '/';
@endphp

@section('title', $pageTitle)
@section('canonical', $canonicalUrl)
@section('robots', $noindex ? 'noindex,follow' : 'index,follow')

@section('content')

{{-- タブナビゲーション --}}
<div class="bg-surface-700 border-b border-surface-400 sticky top-0 z-10">
    <div class="max-w-6xl mx-auto px-4">
        <nav class="flex overflow-x-auto gap-0 -mb-px" aria-label="女性検索タブ">
            @php
                $tabs = [
                    'all'     => ['label' => '女性一覧', 'url' => route('girl.list', ['area_slug' => $area_slug]) . '/'],
                    'standby' => ['label' => '待機中',   'url' => route('girl.list.tab', ['area_slug' => $area_slug, 'cast_tab' => 'standby']) . '/'],
                    'new'     => ['label' => '新人',     'url' => route('girl.list.tab', ['area_slug' => $area_slug, 'cast_tab' => 'new']) . '/'],
                    'diary'   => ['label' => '写メ日記', 'url' => route('girl.list.tab', ['area_slug' => $area_slug, 'cast_tab' => 'diary']) . '/'],
                    'review'  => ['label' => '口コミ',   'url' => route('girl.list.tab', ['area_slug' => $area_slug, 'cast_tab' => 'review']) . '/'],
                ];
            @endphp
            @foreach($tabs as $tabKey => $tab)
            <a href="{{ $tab['url'] }}"
               class="shrink-0 px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap
                      {{ $cast_tab === $tabKey
                          ? 'border-deli-500 text-deli-400'
                          : 'border-transparent text-[#B0AEAD] hover:text-[#E8E4DC] hover:border-surface-200' }}">
                {{ $tab['label'] }}
            </a>
            @endforeach
            <a href="{{ route('shop.list', ['area_slug' => $area_slug]) . '/' }}"
               class="shrink-0 px-4 py-3 text-sm font-medium border-b-2 border-transparent text-[#8A8A9E] hover:text-[#B0AEAD] transition whitespace-nowrap ml-auto">
                店舗一覧 →
            </a>
        </nav>
    </div>
</div>

{{-- フィルターパネル --}}
@if($showFilters)
<div class="bg-surface-700 border-b border-surface-400">
    <div class="max-w-6xl mx-auto px-4 py-3 space-y-2.5">

        {{-- 年齢 --}}
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-xs text-[#8A8A9E] shrink-0 w-10">年齢</span>
            @foreach($ageRanges as $ageKey => $ageData)
            <a href="{{ $filterUrl('age', $ageKey) }}"
               class="px-3 py-1.5 rounded-full text-sm border transition
                      {{ $activeAge === $ageKey
                          ? 'bg-deli-500 border-deli-500 text-white'
                          : 'border-surface-400 text-[#B0AEAD] hover:border-deli-400 hover:text-deli-400' }}">
                {{ $ageData[2] }}
            </a>
            @endforeach
        </div>

        {{-- 身長 --}}
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-xs text-[#8A8A9E] shrink-0 w-10">身長</span>
            @foreach($tallRanges as $tallKey => $tallData)
            <a href="{{ $filterUrl('tall', $tallKey) }}"
               class="px-3 py-1.5 rounded-full text-sm border transition
                      {{ $activeTall === $tallKey
                          ? 'bg-deli-500 border-deli-500 text-white'
                          : 'border-surface-400 text-[#B0AEAD] hover:border-deli-400 hover:text-deli-400' }}">
                {{ $tallData[2] }}
            </a>
            @endforeach
        </div>

        {{-- カップ --}}
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-xs text-[#8A8A9E] shrink-0 w-10">カップ</span>
            @foreach($cupGroups as $cupKey => $cupData)
            <a href="{{ $filterUrl('cup', $cupKey) }}"
               class="px-3 py-1.5 rounded-full text-sm border transition
                      {{ $activeCup === $cupKey
                          ? 'bg-deli-500 border-deli-500 text-white'
                          : 'border-surface-400 text-[#B0AEAD] hover:border-deli-400 hover:text-deli-400' }}">
                {{ $cupData[count($cupData) - 1] }}
            </a>
            @endforeach
        </div>

        {{-- 体型 --}}
        @if(!empty($bodyTypes))
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-xs text-[#8A8A9E] shrink-0 w-10">体型</span>
            @foreach($bodyTypes as $bt)
            <a href="{{ $filterUrl('body', (string)$bt->id) }}"
               class="px-3 py-1.5 rounded-full text-sm border transition
                      {{ $activeBody == $bt->id
                          ? 'bg-deli-500 border-deli-500 text-white'
                          : 'border-surface-400 text-[#B0AEAD] hover:border-deli-400 hover:text-deli-400' }}">
                {{ $bt->name }}
            </a>
            @endforeach
        </div>
        @endif

        {{-- クリアボタン --}}
        @if($hasFilters)
        <div class="pt-0.5">
            <a href="{{ $clearUrl }}" class="text-xs text-[#8A8A9E] hover:text-[#E8E4DC] transition underline">
                絞り込みをクリア
            </a>
        </div>
        @endif

    </div>
</div>
@endif

<div class="max-w-6xl mx-auto px-4 py-6">

    {{-- ヘッダー --}}
    <div class="mb-4 flex items-baseline gap-3">
        <h1 class="text-lg font-bold text-[#E8E4DC]">
            {{ $areaName ? "{$areaName}の{$tabLabel}" : $tabLabel }}
        </h1>
        <span class="text-sm text-[#B0AEAD]">{{ number_format($results->total()) }}件</span>
    </div>

    @if($results->isEmpty())
        <div class="text-center py-16 text-[#8A8A9E]">
            <p class="text-lg">
                @if($cast_tab === 'standby') 現在待機中の女性はいません
                @elseif($cast_tab === 'new') 新人女性は登録されていません
                @elseif($cast_tab === 'diary') 写メ日記の投稿はありません
                @elseif($cast_tab === 'review') まだ口コミがありません
                @else 条件に合う女性が見つかりません
                @endif
            </p>
            @if($hasFilters)
            <a href="{{ $clearUrl }}" class="mt-4 inline-block text-sm text-deli-400 hover:underline">← 絞り込みをクリア</a>
            @else
            <a href="{{ route('girl.list', ['area_slug' => $area_slug]) . '/' }}"
               class="mt-4 inline-block text-sm text-deli-400 hover:underline">← 女性一覧に戻る</a>
            @endif
        </div>
    @else

        @if($cast_tab === 'review')
        {{-- 口コミ一覧 --}}
        <div class="space-y-4">
            @foreach($results as $review)
            @php
                $cast = $review->cast;
                $shop = $cast?->shop;
            @endphp
            <article class="bg-surface-600 border border-surface-400 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <a href="{{ route('cast.show', $cast) }}/" class="shrink-0">
                        <div class="w-12 h-12 rounded-full overflow-hidden bg-surface-400 border border-surface-300">
                            <img src="{{ $cast?->img_url }}"
                                 alt="{{ $cast?->name }}"
                                 loading="lazy"
                                 class="img-onerror-cast w-full h-full object-cover object-top">
                        </div>
                    </a>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center flex-wrap gap-2 mb-1">
                            <span class="text-amber-400 text-sm leading-none tracking-wide">{{ str_repeat('★', $review->rating) }}<span class="text-surface-300">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                            <a href="{{ route('cast.show', $cast) }}/" class="text-sm font-bold text-[#E8E4DC] hover:text-deli-400 truncate">{{ $cast?->name }}</a>
                            @if($shop)
                            <span class="text-xs text-[#8A8A9E] truncate">{{ $shop->name }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-[#B0AEAD] leading-relaxed mb-1">{{ $review->body }}</p>
                        <p class="text-xs text-[#8A8A9E]">{{ $review->nickname }} · {{ $review->created_at->format('Y/m/d') }}</p>
                    </div>
                </div>
            </article>
            @endforeach
        </div>

        @elseif($cast_tab === 'diary')
        {{-- 写メ日記グリッド --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($results as $diary)
            @php
                $firstImg = $diary->images->first();
                $cast     = $diary->cast;
                $shop     = $cast?->shop;
            @endphp
            <article class="bg-surface-600 border border-surface-400 rounded-xl overflow-hidden hover:border-deli-400 transition">
                <a href="{{ route('cast.show', $cast) }}/">
                    @if($firstImg)
                    <div class="aspect-square overflow-hidden bg-surface-500">
                        <img src="{{ asset('storage/' . $firstImg->img_path) }}"
                             alt="{{ $cast?->name }}の写メ日記"
                             loading="lazy" class="w-full h-full object-cover">
                    </div>
                    @else
                    <div class="aspect-square bg-surface-500 flex items-center justify-center">
                        <span class="text-[#8A8A9E] text-3xl">📷</span>
                    </div>
                    @endif
                    <div class="p-2">
                        @if($diary->body)
                        <p class="text-xs text-[#B0AEAD] line-clamp-2 mb-1">{{ $diary->body }}</p>
                        @endif
                        <p class="text-xs font-medium text-[#E8E4DC] truncate">{{ $cast?->name }}</p>
                        <p class="text-xs text-[#8A8A9E] truncate">{{ $shop?->name }}</p>
                        <p class="text-xs text-[#8A8A9E] mt-0.5">{{ $diary->created_at->diffForHumans() }}</p>
                    </div>
                </a>
            </article>
            @endforeach
        </div>

        @else
        {{-- キャストグリッド（女性一覧/待機中/新人/タイプ） --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($results as $cast)
            @php
                $shop      = $cast->shop;
                $isStandby = $cast->working_date?->isToday();
                $isNew     = $cast->isNew();
                $bodyStr   = collect([
                    $cast->bust  ? "B{$cast->bust}" . ($cast->cup ? "({$cast->cup})" : '') : null,
                    $cast->west  ? "W{$cast->west}" : null,
                    $cast->hip   ? "H{$cast->hip}"  : null,
                ])->filter()->implode(' ');
            @endphp
            <article class="bg-surface-600 border border-surface-400 hover:border-deli-400 rounded-xl overflow-hidden transition group">
                <a href="{{ route('cast.show', $cast) }}/" class="block">
                    <div class="aspect-[3/4] overflow-hidden bg-surface-500 relative">
                        <img src="{{ $cast->img_url }}"
                             alt="{{ $cast->name }}"
                             loading="lazy"
                             class="img-onerror-cast w-full h-full object-cover object-top group-hover:scale-105 transition duration-300">
                        <div class="absolute top-1.5 left-1.5 flex flex-col gap-1">
                            @if($isStandby)
                                <span class="text-xs bg-emerald-500 text-white font-bold px-1.5 py-0.5 rounded leading-tight">待機中</span>
                            @endif
                            @if($isNew)
                                <span class="text-xs bg-pink-500 text-white font-bold px-1.5 py-0.5 rounded leading-tight">NEW</span>
                            @endif
                        </div>
                    </div>
                    <div class="p-2">
                        <p class="text-sm font-bold text-[#E8E4DC] group-hover:text-gold-400 transition truncate">{{ $cast->name }}</p>
                        @if($cast->age || $cast->tall)
                        <p class="text-xs text-[#B0AEAD] mt-0.5">{{ $cast->age ? $cast->age.'歳' : '' }}{{ ($cast->age && $cast->tall) ? ' ' : '' }}{{ $cast->tall ? $cast->tall.'cm' : '' }}</p>
                        @endif
                        @if($bodyStr)
                        <p class="text-xs text-[#8A8A9E]">{{ $bodyStr }}</p>
                        @endif
                        @if($shop)
                        <p class="text-xs text-[#8A8A9E] mt-0.5 truncate">{{ $shop->name }}</p>
                        @endif
                    </div>
                </a>
            </article>
            @endforeach
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
