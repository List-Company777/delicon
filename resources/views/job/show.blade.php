@php
    $employmentLabels = [
        'PART_TIME'  => 'アルバイト',
        'CONTRACTOR' => '業務委託',
        'FULL_TIME'  => '正社員',
        'PER_DIEM'   => '日払い',
        'OTHER'      => 'その他',
    ];

    $colorMap = [
        'male'   => ['bar' => 'bg-male-800',      'text' => 'text-male-600',      'btn' => 'bg-male-600 hover:bg-male-700',      'tag' => 'bg-male-50 border-male-300 text-male-600',             'label' => '男性ナイトワーク', 'genderLabel' => '男性', 'genderRoute' => 'male'],
        'female' => ['bar' => 'bg-female-600',    'text' => 'text-female-500',    'btn' => 'bg-female-600 hover:bg-female-500',    'tag' => 'bg-female-50 border-female-100 text-female-600',       'label' => '女性ナイトワーク', 'genderLabel' => '女性', 'genderRoute' => 'female'],
        'both'   => ['bar' => 'bg-business-700',  'text' => 'text-business-600',  'btn' => 'bg-business-700 hover:bg-business-600',  'tag' => 'bg-business-50 border-business-300 text-business-700', 'label' => '男女向け求人',    'genderLabel' => '夜遊び', 'genderRoute' => 'business'],
    ];
    $c = $colorMap[$gender] ?? $colorMap['female'];
@endphp

@extends('layouts.app')

@section('canonical', route('job.show', $job->id) . '/')
@section('title', $job->title . ' | ' . $job->shop->name)
@section('description', mb_strimwidth(strip_tags($job->description ?? $job->title), 0, 120, '…'))
@php
    $ogpImg = $job->image_path
        ? asset('storage/' . \App\Services\ImageService::webpPath($job->image_path))
        : ($job->shop->main_image
            ? asset('storage/' . \App\Services\ImageService::webpPath($job->shop->main_image))
            : null);
@endphp
@if($ogpImg)
@section('ogp_image', $ogpImg)
@section('twitter_card', 'summary_large_image')
@endif

@push('head')
@php
    $ld = [
        '@context'           => 'https://schema.org',
        '@type'              => 'JobPosting',
        'title'              => $job->title,
        'description'        => implode(' ', array_filter([
            $job->description ?? $job->title,
            $job->is_daily_pay     ? '日払いOK。'     : '',
            $job->is_inexperienced ? '未経験歓迎。'   : '',
            $job->working_hours    ? '勤務時間：' . $job->working_hours . '。' : '',
        ])),
        'datePosted'         => ($job->published_at ?? $job->created_at)->toIso8601String(),
        'hiringOrganization' => [
            '@type' => 'Organization',
            'name'  => $job->shop->name,
        ],
        'jobLocation' => [
            '@type'   => 'Place',
            'address' => [
                '@type'          => 'PostalAddress',
                'addressCountry' => 'JP',
                'addressLocality' => $job->area?->name ?? $job->shop->area?->name ?? '',
                'streetAddress'   => $job->shop->address ?? '',
            ],
        ],
    ];
    if ($job->employment_type) {
        // schema.org 期待値: FULL_TIME / PART_TIME / CONTRACTOR / TEMPORARY / INTERN / VOLUNTEER / PER_DIEM / OTHER
        $ld['employmentType'] = $job->employment_type;
    }
    if ($job->expires_at) {
        $ld['validThrough'] = $job->expires_at->toIso8601String();
    }
    if ($job->hourly_wage_min) {
        $unitText = match($job->wage_type ?? 'hourly') {
            'daily'   => 'DAY',
            'monthly' => 'MONTH',
            default   => 'HOUR',
        };
        $ld['baseSalary'] = [
            '@type'    => 'MonetaryAmount',
            'currency' => 'JPY',
            'value'    => array_filter([
                '@type'    => 'QuantitativeValue',
                'minValue' => $job->hourly_wage_min,
                'maxValue' => $job->hourly_wage_max ?: null,
                'unitText' => $unitText,
            ]),
        ];
    }
@endphp
<script type="application/ld+json">
{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@php
    $bcItems = [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'ナイトワーク',    'item' => route('top') . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $c['genderLabel'], 'item' => route('search.directory', ['gender' => $c['genderRoute'], 'area_slug' => 'all', 'job_slug' => 'all']) . '/'],
    ];
    $bcPos = 3;
    if ($job->prefecture) $bcItems[] = ['@type' => 'ListItem', 'position' => $bcPos++, 'name' => $job->prefecture->name, 'item' => route('search.prefecture', ['gender' => $c['genderRoute'], 'pref_slug' => $job->prefecture->slug]) . '/'];
    if ($job->area)       $bcItems[] = ['@type' => 'ListItem', 'position' => $bcPos++, 'name' => $job->area->name, 'item' => route('search.directory', ['gender' => $c['genderRoute'], 'area_slug' => $job->area->slug, 'job_slug' => 'all']) . '/'];
    $bcItems[] = ['@type' => 'ListItem', 'position' => $bcPos, 'name' => $job->shop->name, 'item' => route('job.show', $job->id) . '/'];
    $breadcrumb = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => $bcItems,
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($breadcrumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')

<div class="{{ $c['bar'] }} text-white py-3">
    <div class="max-w-4xl mx-auto px-4 text-sm">
        <a href="{{ route('top') }}/" class="underline underline-offset-2 hover:no-underline text-white/90 hover:text-white">ナイトワーク</a>
        <span class="mx-2 opacity-40">›</span>
        <a href="{{ route('search.directory', ['gender' => $c['genderRoute'], 'area_slug' => 'all', 'job_slug' => 'all']) }}/" class="underline underline-offset-2 hover:no-underline text-white/90 hover:text-white">{{ $c['genderLabel'] }}</a>
        @if($job->prefecture)
        <span class="mx-2 opacity-40">›</span>
        <a href="{{ route('search.prefecture', ['gender' => $c['genderRoute'], 'pref_slug' => $job->prefecture->slug]) }}/" class="underline underline-offset-2 hover:no-underline text-white/90 hover:text-white">{{ $job->prefecture->name }}</a>
        @endif
        @if($job->area)
        <span class="mx-2 opacity-40">›</span>
        <a href="{{ route('search.directory', ['gender' => $c['genderRoute'], 'area_slug' => $job->area->slug, 'job_slug' => 'all']) }}/" class="underline underline-offset-2 hover:no-underline text-white/90 hover:text-white">{{ $job->area->name }}</a>
        @endif
        <span class="mx-2 opacity-40">›</span>
        <span class="opacity-90">{{ $job->shop->name }}</span>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">

        {{-- 求人画像 → 店舗メイン画像 → カラーバー の順でフォールバック --}}
        @if($job->image_path)
            <img src="{{ asset('storage/' . $job->image_path) }}"
                 alt="{{ $job->title }}"
                 width="640" height="360"
                 class="w-full aspect-video object-cover"
                 fetchpriority="high" loading="eager">
        @elseif($job->shop->main_image)
            <x-shop-image :src="$job->shop->main_image" :alt="$job->shop->name" class="w-full aspect-video object-cover" fetchpriority="high" loading="eager" />
        @else
            <div class="h-16 {{ $c['bar'] }}"></div>
        @endif

        <div class="p-6 md:p-8">

            {{-- タグ列 --}}
            <div class="flex flex-wrap gap-2 mb-3">
                @if($job->is_hotlink)
                    <span class="text-xs px-2 py-0.5 bg-orange-100 border border-orange-300 text-orange-700 rounded-full">PR</span>
                @endif
                @if($job->jobType)
                    <span class="text-xs px-2 py-0.5 {{ $c['tag'] }} border rounded-full">{{ $job->jobType->name }}</span>
                @endif
                @if($job->shop->genre)
                    <span class="text-xs px-2 py-0.5 bg-gray-100 border border-gray-300 text-gray-600 rounded-full">{{ $job->shop->genre->name }}</span>
                @endif
            </div>

            {{-- タイトル --}}
            <h1 class="text-xl md:text-2xl font-bold text-gray-800 mb-1">{{ $job->title }}</h1>
            <p class="text-gray-500 text-sm mb-4">{{ $job->shop->name }}</p>

            {{-- 給与・雇用形態 --}}
            @if($job->hourly_wage_min || $job->employment_type)
                @php $wageLabel = ['hourly'=>'時給','daily'=>'日給','monthly'=>'月給'][$job->wage_type ?? 'hourly']; @endphp
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    @if($job->hourly_wage_min)
                        <p class="text-xs text-gray-500 mb-1">{{ $wageLabel }}</p>
                        <p class="text-2xl font-bold {{ $c['text'] }}">
                            {{ number_format($job->hourly_wage_min) }}円
                            @if($job->hourly_wage_max)
                                〜 {{ number_format($job->hourly_wage_max) }}円
                            @endif
                        </p>
                        @if($job->working_hours)
                            <p class="text-xs text-gray-500 mt-1">勤務時間：{{ $job->working_hours }}</p>
                        @endif
                    @endif
                    @if($job->employment_type)
                        <p class="text-xs text-gray-500 {{ $job->hourly_wage_min ? 'mt-2' : 'mb-1' }}">
                            雇用形態：{{ $employmentLabels[$job->employment_type] }}
                        </p>
                    @endif
                </div>
            @endif

            {{-- 求人詳細 --}}
            @if($job->description)
                <div class="mb-6">
                    <h2 class="font-bold text-gray-700 mb-2 text-sm">求人詳細</h2>
                    <div class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $job->description }}</div>
                </div>
            @endif

            {{-- 店舗情報 --}}
            <div class="mb-8 border-t border-gray-100 pt-6">
                <h2 class="font-bold text-gray-700 mb-3 text-sm">店舗情報</h2>
                <dl class="grid grid-cols-2 gap-y-2 text-sm">
                    <dt class="text-gray-400">店舗名</dt>
                    <dd class="text-gray-700">{{ $job->shop->name }}</dd>
                    @if($job->area)
                        <dt class="text-gray-400">エリア</dt>
                        <dd class="text-gray-700">{{ $job->area->name }}</dd>
                    @endif
                    @if($job->shop->address)
                        <dt class="text-gray-400">住所</dt>
                        <dd class="text-gray-700">{{ $job->shop->address }}</dd>
                    @endif
                    @if($job->shop->nearest_station_name)
                        <dt class="text-gray-400">最寄り駅</dt>
                        <dd class="text-gray-700">
                            @if($job->shop->nearest_line)
                                <span class="text-gray-500">{{ $job->shop->nearest_line }}</span>
                            @endif
                            {{ $job->shop->nearest_station_name }}駅
                            @if($job->shop->nearest_station_walk)
                                <span class="text-gray-500 text-xs">徒歩{{ $job->shop->nearest_station_walk }}分</span>
                            @endif
                        </dd>
                    @endif
                </dl>
                @if($job->shop->detail?->status === 'active')
                    <a href="{{ route('shop.show', $job->shop->id) }}/"
                       class="mt-4 inline-block text-sm text-business-700 hover:underline">
                        この店舗の営業情報を見る →
                    </a>
                @endif
            </div>

            {{-- 応募ボタン --}}
            <a href="{{ route('apply.create', $job->id) }}/"
               rel="nofollow"
               class="{{ $c['btn'] }} text-white font-bold py-4 px-8 rounded-xl text-center block w-full text-lg transition">
                この求人に応募する
            </a>

            <p class="text-center text-xs text-gray-400 mt-3">
                フォーム応募のみ対応しています
            </p>

        </div>
    </div>

    {{-- 同じ店舗の他の求人 --}}
    @if($sameShopJobs->isNotEmpty())
    <div class="mt-8">
        <h2 class="text-sm font-bold text-gray-600 mb-3">{{ $job->shop->name }}の他の求人</h2>
        <div class="space-y-2">
            @foreach($sameShopJobs as $sJob)
            <a href="{{ route('job.show', $sJob->id) }}/"
               class="flex items-center justify-between p-3 rounded-xl border {{ $gender === 'male' ? 'border-male-200 bg-male-50 hover:bg-male-100' : ($gender === 'both' ? 'border-business-200 bg-business-50 hover:bg-business-100' : 'border-female-100 bg-female-50 hover:bg-female-100') }} transition group">
                <div class="min-w-0">
                    <p class="text-xs {{ $c['text'] }} font-medium">{{ $sJob->jobType?->name ?? '求人' }}</p>
                    <p class="text-sm font-bold text-gray-800 truncate">{{ $sJob->title }}</p>
                    @if($sJob->hourly_wage_min)
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ ['hourly'=>'時給','daily'=>'日給','monthly'=>'月給'][$sJob->wage_type ?? 'hourly'] }}
                        {{ number_format($sJob->hourly_wage_min) }}円〜
                    </p>
                    @endif
                </div>
                <span class="{{ $c['text'] }} opacity-60 ml-3 shrink-0 group-hover:translate-x-0.5 transition-transform">›</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 関連求人（同エリア・同職種、無料店ページのみ・有料店優先・クリック課金） --}}
    @if($relatedJobs->isNotEmpty())
    <div class="mt-8">
        <h2 class="text-sm font-bold text-gray-600 mb-3">関連求人</h2>
        <div class="space-y-2">
            @foreach($relatedJobs as $rJob)
            <a href="{{ url('/track/job/' . $rJob->id) . '/' }}"
               rel="nofollow"
               class="flex items-center justify-between p-3 rounded-xl border border-gray-200 bg-white hover:border-gray-300 hover:shadow-sm transition group">
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 font-medium">
                        {{ $rJob->jobType?->name ?? '求人' }} &nbsp;·&nbsp; {{ $rJob->shop->area?->name ?? '' }}
                    </p>
                    <p class="text-sm font-bold text-gray-800 truncate">{{ $rJob->title }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $rJob->shop->name }}</p>
                </div>
                <span class="text-gray-300 ml-3 shrink-0 group-hover:translate-x-0.5 transition-transform">›</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 最近見た求人 --}}
    <div x-data="recentlyViewedJobs()" x-init="init()" class="mt-8" x-show="items.length > 0" x-cloak>
        <h2 class="text-sm font-bold text-gray-600 mb-3">最近見た求人</h2>
        <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
            <template x-for="item in items" :key="item.id">
                <a :href="item.url"
                   class="shrink-0 w-36 bg-white border border-gray-200 rounded-xl p-3 hover:shadow-sm transition group">
                    <p class="text-xs text-gray-400 mb-1" x-text="item.type"></p>
                    <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-tight mb-1" x-text="item.title"></p>
                    <p class="text-xs text-gray-400 truncate" x-text="item.shop"></p>
                </a>
            </template>
        </div>
    </div>

    {{-- 戻るリンク --}}
    <div class="mt-6 text-center">
        <a href="javascript:history.back()" class="text-sm text-gray-400 hover:text-gray-600">
            ← 検索結果に戻る
        </a>
    </div>

    @include('partials._promo-banner', ['wrapClass' => 'my-8 rounded-2xl overflow-hidden shadow-md', 'innerClass' => ''])

    {{-- 通報フォーム --}}
    @include('partials._report-form', ['reportTargetType' => 'job', 'reportTargetId' => $job->id])
</div>

@endsection

@push('scripts')
<script>
function recentlyViewedJobs() {
    // search_group → localStorage キー: スタッフ/キャスト/店舗で分離
    const GROUP_KEY = {
        female: 'nw_recent_jobs_female',
        male:   'nw_recent_jobs_male',
        both:   'nw_recent_jobs_business',
    };
    const group = @json($gender === 'both' ? 'both' : $gender);
    const KEY = GROUP_KEY[group] || 'nw_recent_jobs_female';
    const MAX = 8;
    const currentId = {{ $job->id }};
    const currentData = {
        id:    currentId,
        title: @json($job->title),
        shop:  @json($job->shop->name),
        type:  @json($job->jobType?->name ?? ($gender === 'male' ? 'スタッフ求人' : ($gender === 'both' ? '夜遊び求人' : 'キャスト求人'))),
        url:   '{{ route('job.show', $job->id) }}/',
        group: group,
    };

    return {
        items: [],
        init() {
            let list = [];
            try { list = JSON.parse(localStorage.getItem(KEY) || '[]'); } catch(e) {}
            list = list.filter(i => i.id !== currentId);
            list.unshift(currentData);
            if (list.length > MAX) list = list.slice(0, MAX);
            try { localStorage.setItem(KEY, JSON.stringify(list)); } catch(e) {}
            this.items = list.filter(i => i.id !== currentId).slice(0, 6);
        }
    };
}
</script>
@endpush
