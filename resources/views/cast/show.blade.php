@extends('layouts.app')

@section('title', $cast->name . ($cast->age ? '（'.$cast->age.'歳）' : '') . ($cast->shop ? ' - '.$cast->shop->name : ''))
@section('description',
    $cast->name . '(' . ($cast->age ? $cast->age . '歳' : '') . ')' .
    ($cast->castType ? '・' . $cast->castType->name : '') .
    ($cast->shop ? ' - ' . $cast->shop->name : '') .
    ($cast->comment ? '。' . mb_strimwidth(strip_tags($cast->comment), 0, 60, '…') : '')
)
@section('canonical', route('cast.show', $cast->id) . '/')
@if($noindex)
@section('robots', 'noindex,follow')
@endif
@if($cast->img_url !== '/img/no-cast.svg')
@section('ogp_image', url($cast->img_url))
@section('twitter_card', 'summary_large_image')
@endif
@push('head')
@php
    $bc = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type'=>'ListItem','position'=>1,'name'=>'ホーム','item'=>route('top').'/'],
            ['@type'=>'ListItem','position'=>2,'name'=>'デリヘル女性一覧','item'=>url('/all/girl-list/').'/'],
        ],
    ];
    if ($cast->shop) {
        $bc['itemListElement'][] = ['@type'=>'ListItem','position'=>3,'name'=>$cast->shop->name,'item'=>route('shop.show',$cast->shop->id).'/'];
    }
    $bc['itemListElement'][] = ['@type'=>'ListItem','position'=>count($bc['itemListElement'])+1,'name'=>$cast->name,'item'=>route('cast.show',$cast->id).'/'];

    $approvedReviews = $cast->reviews->where('is_approved', true);
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($bc, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) !!}</script>
@php
    $ld_person = array_filter([
        '@context'    => 'https://schema.org',
        '@type'       => 'Person',
        'name'        => $cast->name,
        'url'         => route('cast.show', $cast->id) . '/',
        'image'       => ($cast->img_url && !str_contains($cast->img_url, 'no-cast')) ? url($cast->img_url) : null,
        'description' => $cast->comment ? mb_strimwidth(strip_tags($cast->comment), 0, 160, '…') : null,
        'jobTitle'    => $cast->castType?->name ?? null,
        'worksFor'    => $cast->shop ? [
            '@type' => 'LocalBusiness',
            'name'  => $cast->shop->name,
            'url'   => route('shop.show', $cast->shop->id) . '/',
        ] : null,
    ], fn($v) => $v !== null);
    if ($approvedReviews->count() > 0 && !$noindex) {
        $ld_person['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => round($approvedReviews->avg('rating'), 1),
            'bestRating'  => 5,
            'worstRating' => 1,
            'ratingCount' => $approvedReviews->count(),
        ];
    }
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($ld_person, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 pb-20 md:pb-8">

    {{-- パンくず --}}
    <nav aria-label="パンくずリスト" class="text-xs text-[#6A6A7E] mb-5">
        <ol class="flex flex-wrap items-center gap-1 list-none m-0 p-0">
        <li><a href="{{ route('top') }}/" class="hover:text-deli-400 transition">ホーム</a></li>
        <li aria-hidden="true" class="text-[#3A3A4E]">/</li>
        <li><a href="{{ route('cast.index') }}/" class="hover:text-deli-400 transition">キャスト検索</a></li>
        @if($cast->shop)
        <li aria-hidden="true" class="text-[#3A3A4E]">/</li>
        <li><a href="{{ route('shop.show', $cast->shop->id) }}/" class="hover:text-deli-400 transition">{{ $cast->shop->name }}</a></li>
        @endif
        <li aria-hidden="true" class="text-[#3A3A4E]">/</li>
        <li><span class="text-[#C8C4BC]" aria-current="page">{{ $cast->name }}</span></li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- 左カラム：写真 --}}
        <div x-data="{ lbOpen: false, lbSrc: '' }">
            <div class="relative">
                <div class="aspect-[3/4] rounded-xl border border-surface-300 mb-3 overflow-hidden cursor-zoom-in"
                     @click="lbOpen=true; lbSrc='{{ $cast->img_url }}'">
                    <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                         class="img-onerror-cast w-full h-full object-cover object-top" loading="eager" fetchpriority="high">
                </div>
                {{-- 出勤バッジ --}}
                @if($cast->schedules->contains(fn($s) => $s->work_date->isToday()))
                <span class="absolute top-2 left-2 text-xs font-bold bg-emerald-500 text-white px-2.5 py-1 rounded-full shadow">本日出勤</span>
                @elseif($cast->schedules->contains(fn($s) => $s->work_date->isTomorrow()))
                <span class="absolute top-2 left-2 text-xs font-bold bg-blue-500 text-white px-2.5 py-1 rounded-full shadow">明日出勤</span>
                @elseif($cast->schedules->contains(fn($s) => $s->work_date->gt(today()->addDay())))
                <span class="absolute top-2 left-2 text-xs font-bold bg-purple-500 text-white px-2.5 py-1 rounded-full shadow">出勤予定あり</span>
                @endif
                @if($cast->isNew())
                <span class="absolute top-2 right-2 text-xs font-bold bg-gold-500 text-white px-2.5 py-1 rounded-full shadow">NEW</span>
                @endif
            </div>
            @if($cast->images->count() > 0)
            <div class="grid grid-cols-3 gap-1.5">
                @foreach($cast->images as $img)
                <picture class="cursor-zoom-in"
                         @click="lbOpen=true; lbSrc='{{ Storage::url(\App\Services\ImageService::webpPath($img->img_path)) }}'">
                    <source srcset="{{ Storage::url(\App\Services\ImageService::webpPath($img->img_path)) }}" type="image/webp">
                    <img src="{{ Storage::url($img->img_path) }}" alt="{{ $cast->name }}"
                         class="w-full aspect-square object-cover rounded-lg border border-surface-400" loading="lazy">
                </picture>
                @endforeach
            </div>
            @endif
        </div>
            {{-- Lightbox modal --}}
            <template x-teleport="body">
            <div x-show="lbOpen"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click.self="lbOpen=false"
                 @keydown.escape.window="lbOpen=false"
                 class="fixed inset-0 z-[300] flex items-center justify-center bg-black/90 p-4"
                 style="display:none">
                <button @click="lbOpen=false"
                        class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full bg-surface-600/80 text-[#E8E4DC] hover:bg-surface-500 text-xl transition">
                    ✕
                </button>
                <img :src="lbSrc" alt="拡大表示"
                     class="max-h-[90vh] max-w-full object-contain rounded-lg shadow-2xl">
            </div>
            </template>

        {{-- 右カラム：情報 --}}
        <div class="md:col-span-2 space-y-4">

            {{-- プロフィールヘッダー --}}
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <div class="flex flex-wrap gap-1.5 mb-3">
                    @if($cast->castType)
                    <span class="text-xs bg-deli-900/40 text-deli-400 border border-deli-800/60 px-2.5 py-0.5 rounded-full">{{ $cast->castType->name }}</span>
                    @endif
                    @if($cast->is_recommended)
                    <span class="text-xs bg-gold-500/20 text-gold-400 border border-gold-700/40 px-2.5 py-0.5 rounded-full">おすすめ</span>
                    @endif
                </div>
                <div class="flex items-start justify-between gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-[#F0ECE4] tracking-tight">{{ $cast->name }}</h1>
                    <button id="fav-btn" data-cast="{{ $cast->id }}"
                            data-favorited="{{ $isFavorited ? 'true' : 'false' }}"
                            class="shrink-0 w-10 h-10 flex items-center justify-center rounded-full border transition
                                   {{ $isFavorited ? 'bg-deli-500 border-deli-500 text-white' : 'bg-surface-600 border-surface-300 text-[#6A6A7E] hover:border-deli-400 hover:text-deli-400' }}"
                            title="{{ auth()->check() ? 'お気に入り' : 'ログインするとお気に入り登録できます' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                             fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor"
                             stroke-width="2" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </button>
                </div>
                @if($cast->shop)
                <p class="text-sm text-[#6A6A7E]">
                    <a href="{{ route('shop.show', $cast->shop->id) }}/" class="hover:text-deli-400 transition">{{ $cast->shop->name }}</a>
                </p>
                @endif
                @if($cast->comment)
                <div class="mt-3 text-sm text-[#C8C4BC] leading-relaxed bg-surface-600 border border-surface-400 rounded-lg p-3">
                    {!! nl2br(e($cast->comment)) !!}
                </div>
                @endif
            </div>

            {{-- スペック --}}
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <h2 class="font-bold text-[#E8E4DC] mb-3 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-deli-500 rounded-full"></span>プロフィール
                </h2>
                <div class="grid grid-cols-2 gap-x-4 text-sm">
                    @if($cast->age)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">年齢</span><span class="text-[#E8E4DC]">{{ $cast->age }}歳</span></div>
                    @endif
                    @if($cast->tall)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">身長</span><span class="text-[#E8E4DC]">{{ $cast->tall }}cm</span></div>
                    @endif
                    @if($cast->bust && $cast->cup)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">バスト</span><span class="text-[#E8E4DC]">{{ $cast->bust }}cm {{ $cast->cup }}カップ</span></div>
                    @endif
                    @if($cast->west)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">ウエスト</span><span class="text-[#E8E4DC]">{{ $cast->west }}cm</span></div>
                    @endif
                    @if($cast->hip)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">ヒップ</span><span class="text-[#E8E4DC]">{{ $cast->hip }}cm</span></div>
                    @endif
                    @if($cast->bodyType)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">スタイル</span><span class="text-[#E8E4DC]">{{ $cast->bodyType->name }}</span></div>
                    @endif
                    @if($cast->blood)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">血液型</span><span class="text-[#E8E4DC]">{{ $cast->blood }}型</span></div>
                    @endif
                    @if($cast->country)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">出身</span><span class="text-[#E8E4DC]">{{ $cast->country }}</span></div>
                    @endif
                    @if($cast->price_on)
                    <div class="border-b border-surface-400 py-2 flex justify-between col-span-2"><span class="text-[#6A6A7E]">指名料</span><span class="text-deli-400 font-medium">¥{{ number_format($cast->price_on) }}</span></div>
                    @endif
                </div>
            </div>

            {{-- キャストからのメッセージ --}}
            @if($cast->message)
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <h2 class="font-bold text-[#E8E4DC] mb-2 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-deli-500 rounded-full"></span>キャストからのメッセージ
                </h2>
                <p class="text-sm text-[#C8C4BC] leading-relaxed">{!! nl2br(e($cast->message)) !!}</p>
            </div>
            @endif

            {{-- 詳細プロフィール --}}
            @php $details = array_filter([
                '初体験' => $cast->hatsutaiken, '性感帯' => $cast->seikantai,
                '得意技' => $cast->tokuiwaza, '好きなタイプ' => $cast->sukinatype,
                '趣味' => $cast->shumi, '前職' => $cast->zenshoku,
                '好きな食べ物' => $cast->likeeat, '芸能人似' => $cast->yuumeijin,
                '潮吹き' => $cast->shiofuki, '自宅派/ホテル派' => $cast->zitaku,
                '星座' => $cast->seiza,
            ]); @endphp
            @if(count($details) > 0)
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <h2 class="font-bold text-[#E8E4DC] mb-3 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-surface-200 rounded-full"></span>詳細プロフィール
                </h2>
                <div class="grid grid-cols-2 gap-x-4 text-sm">
                    @foreach($details as $label => $value)
                    <div class="border-b border-surface-400 py-2 flex justify-between gap-2">
                        <span class="text-[#6A6A7E] shrink-0">{{ $label }}</span>
                        <span class="text-[#C8C4BC] text-right">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- チャームポイント --}}
            @if($cast->charms->count() > 0)
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <h2 class="font-bold text-[#E8E4DC] mb-3 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-gold-500 rounded-full"></span>チャームポイント
                </h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($cast->charms as $charm)
                    <span class="bg-gold-900/30 text-gold-400 border border-gold-800/40 text-xs px-2.5 py-1 rounded-full">{{ $charm->name }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 可能プレイ --}}
            @if($cast->plays->count() > 0)
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <h2 class="font-bold text-[#E8E4DC] mb-3 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-deli-600 rounded-full"></span>可能プレイ
                </h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($cast->plays as $play)
                    <span class="bg-deli-900/30 text-deli-400 border border-deli-800/40 text-xs px-2.5 py-1 rounded-full">{{ $play->name }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- タグ --}}
            @if($cast->tags->count() > 0)
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <h2 class="font-bold text-[#E8E4DC] mb-3 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-surface-200 rounded-full"></span>タグ
                </h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($cast->tags as $tag)
                    <span class="bg-surface-400 text-[#C8C4BC] text-xs px-2.5 py-1 rounded-full border border-surface-300">{{ $tag->name }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 出勤スケジュール（2週間カレンダー） --}}
            @php
                $today = \Illuminate\Support\Carbon::today();
                $scheduleByDate = $cast->schedules
                    ->filter(fn($s) => $s->work_date->gte($today))
                    ->keyBy(fn($s) => $s->work_date->format('Y-m-d'));
                $dowLabels = ['日','月','火','水','木','金','土'];
            @endphp
            @if($scheduleByDate->isNotEmpty())
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <h2 class="font-bold text-[#E8E4DC] mb-4 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-emerald-500 rounded-full"></span>出勤スケジュール
                </h2>
                <div class="grid grid-cols-7 gap-1 text-center">
                    @for($i = 0; $i < 14; $i++)
                    @php
                        $day     = $today->copy()->addDays($i);
                        $key     = $day->format('Y-m-d');
                        $sch     = $scheduleByDate->get($key);
                        $isToday = $i === 0;
                        $dow     = (int)$day->format('w');
                    @endphp
                    <div class="rounded-lg py-2 px-0.5 {{ $sch ? 'bg-emerald-900/50 border border-emerald-700/60' : 'bg-surface-600 border border-surface-400' }}">
                        <p class="text-[10px] font-bold mb-0.5 {{ $isToday ? 'text-deli-400' : ($dow === 0 ? 'text-red-400' : ($dow === 6 ? 'text-blue-400' : 'text-[#6A6A7E]')) }}">
                            {{ $dowLabels[$dow] }}
                        </p>
                        <p class="text-xs font-bold {{ $isToday ? 'text-deli-400' : 'text-[#C8C4BC]' }}">
                            {{ $day->format('j') }}
                        </p>
                        @if($sch)
                        <p class="text-[9px] text-emerald-400 mt-1 leading-tight">
                            {{ substr($sch->start_time, 0, 5) }}<br>〜{{ substr($sch->end_time, 0, 5) }}
                        </p>
                        @else
                        <p class="text-[10px] text-[#3A3A4E] mt-1">—</p>
                        @endif
                    </div>
                    @endfor
                </div>
            </div>
            @endif


        </div>
    </div>

    {{-- 写メ日記 --}}
    @if($cast->diaries->count() > 0)
    <section class="mt-10">
        <h2 class="text-lg font-bold text-[#F0ECE4] mb-5 flex items-center gap-3">
            <span class="w-1 h-6 bg-deli-500 rounded-full inline-block"></span>
            写メ日記
        </h2>
        <div class="space-y-5">
            @foreach($cast->diaries->take(5) as $diary)
            <article class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xs text-[#6A6A7E]">{{ $diary->created_at->format('Y年m月d日') }}</span>
                    @if($diary->title)
                    <h3 class="font-bold text-[#E8E4DC] text-sm">{{ $diary->title }}</h3>
                    @endif
                </div>
                @if($diary->images->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 mb-3">
                    @foreach($diary->images as $img)
                    <picture>
                        <source srcset="{{ Storage::url(\App\Services\ImageService::webpPath($img->img_path)) }}" type="image/webp">
                        <img src="{{ Storage::url($img->img_path) }}" alt="{{ $cast->name }}の日記"
                             class="w-full aspect-square object-cover rounded-lg border border-surface-400" loading="lazy">
                    </picture>
                    @endforeach
                </div>
                @endif
                @if($diary->body)
                <p class="text-sm text-[#C8C4BC] leading-relaxed">{!! nl2br(e($diary->body)) !!}</p>
                @endif
                {{-- いいねボタン --}}
                <div class="flex items-center justify-end mt-3 pt-3 border-t border-surface-400">
                    @auth
                    @if(auth()->user()->role === 'visitor')
                    <button type="button"
                            data-diary-id="{{ $diary->id }}"
                            data-liked="{{ in_array($diary->id, $likedDiaryIds) ? '1' : '0' }}"
                            data-diary-like="1"
                            class="diary-like-btn flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full border transition
                                   {{ in_array($diary->id, $likedDiaryIds) ? 'bg-deli-500/20 border-deli-500/60 text-deli-400' : 'border-surface-300 text-[#6A6A7E] hover:border-deli-400 hover:text-deli-400' }}">
                        <svg class="w-3.5 h-3.5" fill="{{ in_array($diary->id, $likedDiaryIds) ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span class="like-count">{{ $diary->likes->count() }}</span>
                    </button>
                    @endif
                    @else
                    <span class="flex items-center gap-1.5 text-xs text-[#6A6A7E]">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        {{ $diary->likes->count() }}
                    </span>
                    @endauth
                </div>
            </article>
            @endforeach
        </div>
    </section>
    @endif

    {{-- キャスト口コミ --}}
    @php $approvedReviews = $cast->reviews->where('is_approved', true); @endphp
    @if($approvedReviews->count() > 0 || true)
    <section class="mt-10">
        <h2 class="text-lg font-bold text-[#F0ECE4] mb-5 flex items-center gap-3">
            <span class="w-1 h-6 bg-gold-500 rounded-full inline-block"></span>
            口コミ @if($approvedReviews->count() > 0)<span class="text-sm text-[#6A6A7E] font-normal">({{ $approvedReviews->count() }}件)</span>@endif
        </h2>

        @if($approvedReviews->count() > 0)
        <div class="relative">
            {{-- 未ログイン時はぼかし + ログイン誘導 --}}
            @guest
            <div class="space-y-4 blur-sm select-none pointer-events-none" aria-hidden="true">
                @foreach($approvedReviews->take(3) as $review)
                <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-amber-400 text-sm tracking-widest">{{ str_repeat('★', $review->rating) }}<span class="text-[#3A3A4E]">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                        <span class="text-xs text-[#6A6A7E]">{{ $review->nickname }}</span>
                    </div>
                    <p class="text-sm text-[#C8C4BC] leading-relaxed">{{ $review->body }}</p>
                </div>
                @endforeach
            </div>
            <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-surface-900/60 rounded-xl">
                <p class="text-sm text-[#B0AEAD] font-medium">口コミを見るには会員登録 / ログインが必要です</p>
                <div class="flex gap-2">
                    <a href="{{ route('visitor.register') }}?redirect={{ urlencode(request()->path()) }}"
                       class="px-5 py-2 bg-deli-500 hover:bg-deli-400 text-white text-sm font-bold rounded-lg transition">無料会員登録</a>
                    <a href="{{ route('login') }}?redirect={{ urlencode(request()->path()) }}"
                       class="px-5 py-2 bg-surface-400 hover:bg-surface-300 text-[#E8E4DC] text-sm font-bold rounded-lg transition">ログイン</a>
                </div>
            </div>
            @endguest

            {{-- ログイン済みは全件表示 --}}
            @auth
            <div class="space-y-4">
                @foreach($approvedReviews->take(5) as $review)
                <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-amber-400 text-sm tracking-widest">{{ str_repeat('★', $review->rating) }}<span class="text-[#3A3A4E]">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                        <span class="text-xs text-[#6A6A7E]">{{ $review->nickname }}</span>
                        <span class="text-xs text-[#4A4A5E]">{{ $review->created_at->format('Y/m/d') }}</span>
                    </div>
                    <p class="text-sm text-[#C8C4BC] leading-relaxed">{{ $review->body }}</p>
                    @if($review->shop_reply)
                    <div class="mt-3 bg-surface-600 border border-surface-400 rounded-lg px-4 py-3">
                        <p class="text-xs text-[#6A6A7E] mb-1">店舗からの返信</p>
                        <p class="text-xs text-[#B0AEAD] leading-relaxed whitespace-pre-wrap">{{ $review->shop_reply }}</p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endauth
        </div>
        @else
        <p class="text-sm text-[#6A6A7E]">まだ口コミはありません。最初の口コミを投稿しませんか？</p>
        @endif

        {{-- 口コミ投稿フォーム（ログイン済みのみ） --}}
        @auth
        <div class="mt-8 bg-surface-500 border border-surface-300 rounded-2xl p-6">
            <h3 class="text-base font-bold text-[#F0ECE4] mb-4">口コミを投稿する</h3>
            @if(session('review_success'))
            <p class="mb-4 text-sm text-deli-400 bg-deli-500/10 border border-deli-500/30 rounded-lg px-4 py-2">{{ session('review_success') }}</p>
            @endif
            @if($errors->hasBag('cast_review'))
            <div class="mb-4 text-sm text-deli-400 bg-deli-500/10 border border-deli-500/30 rounded-lg px-4 py-2">
                @foreach($errors->getBag('cast_review')->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
            @endif
            <form action="{{ route('cast.review.store', $cast->id) }}/" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-[#B0AEAD] mb-1">評価 <span class="text-deli-400">*</span></label>
                    <div class="flex gap-2">
                        @for($i = 5; $i >= 1; $i--)
                        <label class="cursor-pointer">
                            <input type="radio" name="rating" value="{{ $i }}" class="sr-only peer" {{ old('rating') == $i ? 'checked' : ($i == 5 ? 'checked' : '') }}>
                            <span class="text-2xl peer-checked:text-amber-400 text-[#3A3A4E] hover:text-amber-300 transition select-none">★</span>
                        </label>
                        @endfor
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-[#B0AEAD] mb-1">本文 <span class="text-deli-400">*</span><span class="text-xs text-[#6A6A7E] ml-2">20〜2000文字</span></label>
                    <textarea name="body" rows="4" required minlength="20" maxlength="2000"
                              placeholder="接客・雰囲気・おすすめポイントなど..."
                              class="w-full bg-surface-400 border border-surface-300 rounded-lg px-4 py-2.5 text-[#E8E4DC] text-sm focus:outline-none focus:border-deli-500 resize-none">{{ old('body') }}</textarea>
                </div>
                <p class="text-xs text-[#6A6A7E] leading-relaxed">口コミはサービスの評価についてのみ受け付けます。個人攻撃や法的に問題のあるコンテンツは削除します。良識に従ったご利用をお願いします。</p>
                <button type="submit"
                        class="w-full bg-deli-500 hover:bg-deli-400 text-white font-bold py-2.5 rounded-lg transition text-sm">
                    投稿する（承認制）
                </button>
            </form>
        </div>
        @else
        <div class="mt-6 text-center">
            <p class="text-sm text-[#8A8A9E] mb-3">口コミを投稿するには会員登録が必要です</p>
            <a href="{{ route('visitor.register') }}?redirect={{ urlencode(request()->path()) }}"
               class="inline-block px-6 py-2.5 bg-deli-500 hover:bg-deli-400 text-white text-sm font-bold rounded-lg transition">無料会員登録して投稿する</a>
        </div>
        @endauth
    </section>
    @endif

    {{-- 同店舗の在籍キャスト --}}
    @if($otherCasts->count() > 0)
    <section class="mt-10">
        <h2 class="text-lg font-bold text-[#F0ECE4] mb-5 flex items-center gap-3">
            <span class="w-1 h-6 bg-surface-200 rounded-full inline-block"></span>
            @if($cast->shop)<a href="{{ route('shop.show', $cast->shop->id) }}/" class="hover:text-deli-400 transition">{{ $cast->shop->name }}</a>の@endif在籍キャスト
        </h2>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
            @foreach($otherCasts as $other)
            <a href="{{ route('cast.show', $other->id) }}/" class="group text-center">
                <div class="aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 border border-surface-300 group-hover:border-deli-500 transition mb-1">
                    <img src="{{ $other->img_url }}" alt="{{ $other->name }}"
                         class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition" loading="lazy">
                </div>
                <p class="text-xs text-[#C8C4BC] group-hover:text-gold-400 transition truncate">{{ $other->name }}</p>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- こちらのキャストも人気（類似） --}}
    @if($similarCasts->isNotEmpty())
    <section class="mt-10">
        <h2 class="text-lg font-bold text-[#F0ECE4] mb-5 flex items-center gap-3">
            <span class="w-1 h-6 bg-deli-600 rounded-full inline-block"></span>
            こちらのキャストも人気
        </h2>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
            @foreach($similarCasts as $similar)
            <a href="{{ route('cast.show', $similar->id) }}/" class="group text-center">
                <div class="aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 border border-surface-300 group-hover:border-deli-500 transition mb-1">
                    <img src="{{ $similar->img_url }}" alt="{{ $similar->name }}"
                         class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition" loading="lazy">
                </div>
                <p class="text-xs text-[#C8C4BC] group-hover:text-gold-400 transition truncate">{{ $similar->name }}</p>
                @if($similar->shop)
                <p class="text-[10px] text-[#6A6A7E] truncate">{{ $similar->shop->name }}</p>
                @endif
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- 近隣有料店の似た女性（所属店が無料の場合のみ） --}}
    @if($nearbyPaidSimilarCasts->isNotEmpty())
    <section class="mt-10">
        <h2 class="text-lg font-bold text-[#F0ECE4] mb-5 flex items-center gap-3">
            <span class="w-1 h-6 bg-deli-500 rounded-full inline-block"></span>
            このエリアのおすすめキャスト
        </h2>
        <div class="grid grid-cols-3 gap-3">
            @foreach($nearbyPaidSimilarCasts as $similar)
            <a href="{{ route('cast.show', $similar->id) }}/" class="group text-center">
                <div class="aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 border border-surface-300 group-hover:border-deli-500 transition mb-1">
                    <img src="{{ $similar->img_url }}" alt="{{ $similar->name }}"
                         class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition" loading="lazy">
                </div>
                <p class="text-xs text-[#C8C4BC] group-hover:text-gold-400 transition truncate">{{ $similar->name }}</p>
                @if($similar->shop)
                <p class="text-[10px] text-[#6A6A7E] truncate">{{ $similar->shop->name }}</p>
                @endif
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- 削除依頼フォーム（有料プランのみ） --}}
    @if($cast->shop?->isPaid())
    <div class="mt-10 pt-6 border-t border-surface-400">
        @if(session('deletion_sent'))
        <div class="mb-3 bg-emerald-900/40 border border-emerald-600/40 text-emerald-400 text-sm px-4 py-3 rounded-lg">削除依頼を受け付けました。確認後ご連絡いたします。</div>
        @endif
        <details class="group">
            <summary class="cursor-pointer text-xs text-[#6A6A7E] hover:text-[#C8C4BC] transition list-none flex items-center gap-2 select-none">
                <svg class="w-3.5 h-3.5 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                このページの削除を依頼する
            </summary>
            <div class="mt-4 bg-surface-600 border border-surface-400 rounded-xl p-5">
                <h3 class="text-sm font-bold text-[#E8E4DC] mb-1">削除依頼フォーム</h3>
                <p class="text-xs text-[#6A6A7E] mb-3">ご本人様またはご関係者からの削除依頼を受け付けています。内容を確認後、速やかに対応いたします。</p>
                <p class="text-xs text-[#8A8A9E] bg-surface-500 border border-surface-300 rounded-lg px-3 py-2.5 leading-relaxed mb-4">削除理由が明確でないもの、削除を要請する事情などが不明な場合、相当な理由と認められない場合には対応しないことがあります。必ず身分と理由を明確にしてフォーム送信をしてください。</p>
                <form method="POST" action="{{ route('cast.deletion-request', $cast->id) }}/">
                    @csrf
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-[#C8C4BC] block mb-1">お名前 <span class="text-deli-400">*</span></label>
                            <input type="text" name="requester_name" required maxlength="50" value="{{ old('requester_name') }}"
                                   class="w-full bg-surface-500 border border-surface-300 rounded-lg px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:border-deli-500">
                            @error('requester_name')<p class="text-xs text-deli-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-xs text-[#C8C4BC] block mb-1">メールアドレス <span class="text-deli-400">*</span></label>
                            <input type="email" name="requester_email" required maxlength="100" value="{{ old('requester_email') }}"
                                   class="w-full bg-surface-500 border border-surface-300 rounded-lg px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:border-deli-500">
                            @error('requester_email')<p class="text-xs text-deli-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-xs text-[#C8C4BC] block mb-1">削除理由（任意）</label>
                            <textarea name="reason" rows="3" maxlength="500"
                                   class="w-full bg-surface-500 border border-surface-300 rounded-lg px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:border-deli-500 resize-none">{{ old('reason') }}</textarea>
                        </div>
                        <button type="submit" class="w-full bg-surface-400 hover:bg-surface-300 text-[#C8C4BC] text-sm font-bold py-2 rounded-lg transition">
                            削除を依頼する
                        </button>
                    </div>
                </form>
            </div>
        </details>
    </div>
    @endif

</div>

@push('scripts')
<script @nonce>
(function() {
    var btn = document.getElementById('fav-btn');
    if (!btn) return;
    @auth
    btn.addEventListener('click', function() {
        fetch('/favorites/' + btn.dataset.cast + '/', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            }
        }).then(function(r){ return r.json(); }).then(function(data) {
            btn.dataset.favorited = data.favorited ? 'true' : 'false';
            var svg = btn.querySelector('svg');
            if (data.favorited) {
                btn.classList.add('bg-deli-500','border-deli-500','text-white');
                btn.classList.remove('bg-surface-600','border-surface-300','text-[#6A6A7E]');
                svg.setAttribute('fill','currentColor');
                document.getElementById('fav-success-modal').classList.remove('hidden');
            } else {
                btn.classList.remove('bg-deli-500','border-deli-500','text-white');
                btn.classList.add('bg-surface-600','border-surface-300','text-[#6A6A7E]');
                svg.setAttribute('fill','none');
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
    document.getElementById('fav-success-close').addEventListener('click', function() {
        document.getElementById('fav-success-modal').classList.add('hidden');
    });
    document.getElementById('fav-success-backdrop').addEventListener('click', function() {
        document.getElementById('fav-success-modal').classList.add('hidden');
    });
    @endauth
})();
</script>
@endpush

{{-- 最近見たキャスト --}}
<div id="recent-casts-section" class="max-w-5xl mx-auto px-4 pb-6 hidden">
    <h2 class="text-base font-bold text-[#C8C4BC] mb-3 flex items-center gap-2">
        <span class="w-1 h-4 bg-surface-300 rounded-full inline-block"></span>
        最近見たキャスト
    </h2>
    <div id="recent-casts-grid" class="grid grid-cols-4 sm:grid-cols-6 gap-2"></div>
</div>

@push('scripts')
<script @nonce>
(function() {
    var cur = { id: {{ $cast->id }}, name: @json($cast->name), img: @json($cast->img_url !== '/img/no-cast.svg' ? $cast->img_url : '/img/no-cast.svg'), url: '{{ route('cast.show', $cast->id) }}/' };
    var stored = [];
    try { stored = JSON.parse(localStorage.getItem('recentCasts') || '[]'); } catch(e) {}
    stored = [cur].concat(stored.filter(function(c) { return c.id !== cur.id; })).slice(0, 12);
    try { localStorage.setItem('recentCasts', JSON.stringify(stored)); } catch(e) {}
    var others = stored.filter(function(c) { return c.id !== cur.id; }).slice(0, 6);
    if (others.length > 0) {
        var grid = document.getElementById('recent-casts-grid');
        var sect = document.getElementById('recent-casts-section');
        others.forEach(function(c) {
            var a = document.createElement('a');
            a.href = c.url;
            a.className = 'group text-center';
            a.innerHTML = '<div class="aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 border border-surface-300 group-hover:border-deli-500 transition mb-1">' +
                '<img src="' + c.img + '" alt="' + c.name + '" onerror="this.src='/img/no-cast.svg'" class="w-full h-full object-cover group-hover:scale-105 transition" loading="lazy">' +
                '</div><p class="text-xs text-[#C8C4BC] group-hover:text-gold-400 transition truncate">' + c.name + '</p>';
            grid.appendChild(a);
        });
        sect.classList.remove('hidden');
    }
})();
</script>
@endpush


{{-- 近隣有料掲載店（所属店が無料の場合のみ） --}}
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

        {{-- お気に入りボタン --}}
        <button id="fav-float-btn"
                data-cast="{{ $cast->id }}"
                data-favorited="{{ $isFavorited ? 'true' : 'false' }}"
                class="flex-1 flex items-center justify-center gap-1.5 py-3 rounded-xl border text-xs font-medium transition
                       {{ $isFavorited
                           ? 'bg-deli-500/20 border-deli-500 text-deli-400'
                           : 'bg-surface-700 border-surface-400 text-[#B0AEAD]' }}">
            <svg class="w-4 h-4 shrink-0" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
            </svg>
            <span id="fav-float-label">{{ $isFavorited ? 'お気に入り登録済み' : 'この女性をお気に入り登録' }}</span>
        </button>

        {{-- 電話ボタン --}}
        @if($cast->shop?->tel)
        <a href="tel:{{ $cast->shop->tel }}" rel="nofollow"
           data-cast-id="{{ $cast->id }}"
           data-tel-track="1"
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
    var btn = document.getElementById('fav-float-btn');
    if (!btn) return;
    @auth
    btn.addEventListener('click', function() {
        fetch('/favorites/' + btn.dataset.cast + '/', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            }
        }).then(function(r){ return r.json(); }).then(function(data) {
            btn.dataset.favorited = data.favorited ? 'true' : 'false';
            document.getElementById('fav-float-label').textContent = data.favorited ? 'お気に入り登録済み' : 'この女性をお気に入り登録';
            var svg = btn.querySelector('svg');
            if (data.favorited) {
                btn.classList.add('bg-deli-500/20','border-deli-500','text-deli-400');
                btn.classList.remove('bg-surface-700','border-surface-400','text-[#B0AEAD]');
                svg.setAttribute('fill','currentColor');
                document.getElementById('fav-success-modal').classList.remove('hidden');
            } else {
                btn.classList.remove('bg-deli-500/20','border-deli-500','text-deli-400');
                btn.classList.add('bg-surface-700','border-surface-400','text-[#B0AEAD]');
                svg.setAttribute('fill','none');
            }
            // ヘッダーのハートボタンにも反映
            var headerBtn = document.getElementById('fav-btn');
            if (headerBtn) {
                headerBtn.dataset.favorited = btn.dataset.favorited;
                var hSvg = headerBtn.querySelector('svg');
                if (data.favorited) {
                    headerBtn.classList.add('bg-deli-500','border-deli-500','text-white');
                    headerBtn.classList.remove('bg-surface-600','border-surface-300','text-[#6A6A7E]');
                    hSvg.setAttribute('fill','currentColor');
                } else {
                    headerBtn.classList.remove('bg-deli-500','border-deli-500','text-white');
                    headerBtn.classList.add('bg-surface-600','border-surface-300','text-[#6A6A7E]');
                    hSvg.setAttribute('fill','none');
                }
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

{{-- お気に入り登録成功モーダル --}}
<div id="fav-success-modal" class="hidden fixed inset-0 z-[200] flex items-center justify-center p-4">
    <div id="fav-success-backdrop" class="absolute inset-0 bg-black/60"></div>
    <div class="relative bg-surface-700 border border-surface-400 rounded-2xl p-6 mx-4 max-w-sm w-full shadow-xl">
        <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-deli-400 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            <p class="text-[#E8E4DC] font-bold text-sm">お気に入りに登録しました</p>
        </div>
        <p class="text-[#B0AEAD] text-sm mb-5">マイページでこの女性の出勤通知の設定ができます。</p>
        <div class="flex gap-2">
            <button id="fav-success-close" class="flex-1 py-2.5 rounded-xl border border-surface-400 text-[#B0AEAD] text-sm transition hover:border-surface-300">閉じる</button>
            <a href="{{ route('user.settings') }}/" class="flex-1 py-2.5 rounded-xl bg-deli-500 hover:bg-deli-400 text-white text-sm font-bold text-center transition">マイページへ</a>
        </div>
    </div>
</div>

{{-- 非ログイン通知モーダル --}}
<div id="auth-modal" class="hidden fixed inset-0 z-[200] flex items-center justify-center p-4">
    <div id="auth-modal-backdrop" class="absolute inset-0 bg-black/60"></div>
    <div class="relative bg-surface-700 border border-surface-400 rounded-2xl p-6 mx-4 max-w-sm w-full shadow-xl">
        <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-deli-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            <p class="text-[#E8E4DC] font-bold text-sm">ログインが必要です</p>
        </div>
        <p class="text-[#B0AEAD] text-sm mb-5">お気に入り登録するにはログインが必要です。</p>
        <div class="flex gap-2">
            <button id="auth-modal-close" class="flex-1 py-2.5 rounded-xl border border-surface-400 text-[#B0AEAD] text-sm transition hover:border-surface-300">閉じる</button>
            <a href="{{ route('login') }}/" class="flex-1 py-2.5 rounded-xl bg-deli-500 hover:bg-deli-400 text-white text-sm font-bold text-center transition">ログインする</a>
        </div>
    </div>
</div>

@push('scripts')
<script @nonce>
function toggleDiaryLike(btn) {
    var diaryId = btn.dataset.diaryId;
    var csrf = document.querySelector('meta[name=csrf-token]').content;
    fetch('/diary/' + diaryId + '/like/', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        btn.dataset.liked = data.liked ? '1' : '0';
        btn.querySelector('.like-count').textContent = data.count;
        var svg = btn.querySelector('svg');
        if (data.liked) {
            btn.classList.remove('border-surface-300','text-[#6A6A7E]','hover:border-deli-400','hover:text-deli-400');
            btn.classList.add('bg-deli-500/20','border-deli-500/60','text-deli-400');
            svg.setAttribute('fill','currentColor');
        } else {
            btn.classList.add('border-surface-300','text-[#6A6A7E]','hover:border-deli-400','hover:text-deli-400');
            btn.classList.remove('bg-deli-500/20','border-deli-500/60','text-deli-400');
            svg.setAttribute('fill','none');
        }
    });
}
</script>
@endpush

@endsection

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
// 日記いいねボタン
document.querySelectorAll('[data-diary-like]').forEach(function(btn) {
    btn.addEventListener('click', function() { toggleDiaryLike(this); });
});
// 電話ボタン トラッキング
document.querySelectorAll('[data-tel-track]').forEach(function(link) {
    link.addEventListener('click', function() {
        var castId = this.dataset.castId;
        if (!castId) return;
        fetch('/ranking/tel/' + castId + '/', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        }).catch(function() {});
    });
});
</script>
@endpush
