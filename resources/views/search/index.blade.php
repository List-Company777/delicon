@php
    $colorMap = [
        'business' => ['bg' => 'bg-business-700', 'border' => 'border-business-300', 'text' => 'text-business-700', 'btn' => 'bg-business-700 hover:bg-business-600', 'tag' => 'bg-business-50 border-business-300 text-business-700', 'label' => '夜遊び'],
        'male'     => ['bg' => 'bg-male-800',     'border' => 'border-male-300',     'text' => 'text-male-600',     'btn' => 'bg-male-600 hover:bg-male-700',     'tag' => 'bg-male-50 border-male-300 text-male-600',     'label' => '男性ナイトワーク'],
        'female'   => ['bg' => 'bg-female-600',   'border' => 'border-female-300',   'text' => 'text-female-500',   'btn' => 'bg-female-600 hover:bg-female-500',   'tag' => 'bg-female-50 border-female-100 text-female-600',   'label' => '女性ナイトワーク'],
    ];
    $c = $colorMap[$gender] ?? $colorMap['female'];
@endphp

@php
    // LP（ディレクトリURL）か通常検索かで表示名を切り替え
    $isLp        = isset($area_slug);
    $displayArea = $isLp ? ($areaName ?? '') : ($area ?? '');
    $displayJob  = $isLp ? ($jobTypeName ?? '') : ($keyword ?? '');

    $hasArea = (bool) $displayArea;
    $hasJob  = (bool) $displayJob;
    $site    = 'ナイトワークリスト';

    // --- ページタイトル ---
    $currentPage = request()->input('page', 1);
    $pageSuffix  = $currentPage > 1 ? "（{$currentPage}ページ目）" : '';

    if ($gender === 'business') {
        if ($hasArea && $hasJob) {
            $pageTitle = "{$displayArea}の{$displayJob}" . ($isLp ? '一覧' : '検索結果') . $pageSuffix;
        } elseif ($hasArea) {
            $pageTitle = "{$displayArea}の夜遊びスポット" . ($isLp ? '一覧' : '検索結果') . $pageSuffix;
        } elseif ($hasJob) {
            $pageTitle = ($isLp ? '全国の' : '') . "{$displayJob}" . ($isLp ? '情報一覧' : '検索結果') . $pageSuffix;
        } else {
            $pageTitle = ($isLp ? '夜遊びスポット・ナイト系店舗情報一覧' : '夜遊びスポット検索結果') . $pageSuffix;
        }
    } else {
        $titlePrefix = implode('　', array_filter([$displayArea, $displayJob]));
        if ($isLp) {
            $pageTitle = ($titlePrefix ? "{$titlePrefix}の{$c['label']}求人" : "{$c['label']}求人一覧") . $pageSuffix;
        } else {
            $pageTitle = ($titlePrefix ? "{$titlePrefix} {$c['label']}検索結果" : "{$c['label']}検索結果") . $pageSuffix;
        }
    }

    // --- メタdescription ---
    if ($gender === 'business') {
        if ($hasArea && $hasJob) {
            $pageDesc = "{$displayArea}の{$displayJob}情報を掲載。営業時間・料金・アクセスをまとめてチェック。夜遊びスポットを探すなら{$site}。";
        } elseif ($hasArea) {
            $pageDesc = "{$displayArea}の夜遊びスポット情報を掲載。キャバクラ・クラブ・バーなど営業時間・料金もチェックできます。{$site}。";
        } elseif ($hasJob) {
            $pageDesc = "全国の{$displayJob}情報を掲載。営業時間・料金・アクセスをまとめて確認。夜遊びスポット検索は{$site}。";
        } else {
            $pageDesc = "全国の夜遊びスポット・ナイト系店舗情報を掲載。キャバクラ・クラブ・バーなどエリア・業種から検索できます。{$site}。";
        }
    } elseif ($gender === 'female') {
        if ($hasArea && $hasJob) {
            $pageDesc = "{$displayArea}の{$displayJob}求人を掲載中。時給・日払い・未経験歓迎など条件で絞り込めます。女性向けナイトワーク求人サイト{$site}。";
        } elseif ($hasArea) {
            $pageDesc = "{$displayArea}の女性ナイトワークを掲載中。キャバクラ・ガールズバー・ラウンジなど多数掲載。エリア・職種から簡単検索。{$site}。";
        } elseif ($hasJob) {
            $pageDesc = "{$displayJob}の求人を全国から検索。時給・日払い・未経験歓迎の女性向けナイトワーク求人。{$site}。";
        } else {
            $pageDesc = null; // レイアウトのデフォルトdescriptionを使用
        }
    } else { // male
        if ($hasArea && $hasJob) {
            $pageDesc = "{$displayArea}の{$displayJob}求人を掲載中。未経験歓迎・日払いOKの男性向けナイトワーク求人。{$site}。";
        } elseif ($hasArea) {
            $pageDesc = "{$displayArea}の男性ナイトワークを掲載中。ホスト・バーテンダー・ボーイなど多数掲載。エリア・職種から簡単検索。{$site}。";
        } elseif ($hasJob) {
            $pageDesc = "{$displayJob}の求人を全国から検索。未経験歓迎・日払いOKの男性向けナイトワーク求人。{$site}。";
        } else {
            $pageDesc = null; // レイアウトのデフォルトdescriptionを使用
        }
    }
@endphp

@extends('layouts.app')

@php
    $currentPage = (int) request()->input('page', 1);

    if ($isLp) {
        $baseUrl = ($isPrefPage ?? false)
            ? route('search.prefecture', ['gender' => $gender, 'pref_slug' => $area_slug]) . '/'
            : route('search.directory', ['gender' => $gender, 'area_slug' => $area_slug, 'job_slug' => $job_slug]) . '/';
        $extraParams  = array_filter([
            'all_you_can_drink' => ($allYouCanDrink ?? false)  ? 1 : null,
            'has_karaoke'       => ($hasKaraoke ?? false)      ? 1 : null,
            'has_private_room'  => ($hasPrivateRoom ?? false)  ? 1 : null,
        ], fn($v) => $v !== null);
        $canonicalUrl = $baseUrl . ($extraParams ? '?' . http_build_query($extraParams) : '');
    } else {
        $canonicalParams = array_filter([
            'gender'    => $gender,
            'area'      => $area    ?? '',
            'keyword'   => $keyword ?? '',
            'wage_type' => $wageType ?? '',
            'wage_min'  => ($wageMin ?? 0) > 0 ? ($wageMin ?? 0) : null,
        ], fn($v) => $v !== null && $v !== '');
        $canonicalUrl = url('/search/') . ($canonicalParams ? '?' . http_build_query($canonicalParams) : '');
    }

    // rel="prev" / "next" 用URL（page パラメータを追加）
    $prevUrl = $currentPage > 1
        ? $canonicalUrl . (str_contains($canonicalUrl, '?') ? '&' : '?') . 'page=' . ($currentPage - 1)
        : null;
    $nextUrl = $results->hasMorePages()
        ? $canonicalUrl . (str_contains($canonicalUrl, '?') ? '&' : '?') . 'page=' . ($currentPage + 1)
        : null;
    // page=1 のprevは不要（pageパラメータなし = 1ページ目）
    if ($currentPage === 2) {
        $prevUrl = $canonicalUrl;
    }
@endphp
@section('canonical', $canonicalUrl)
@section('title', $pageTitle)
@if($currentPage > 1 || ($noindex ?? false))
@section('robots', 'noindex, follow')
@endif
@if($pageDesc)
@section('description', $pageDesc)
@endif

@push('head')
@if($prevUrl)
<link rel="prev" href="{{ $prevUrl }}">
@endif
@if($nextUrl)
<link rel="next" href="{{ $nextUrl }}">
@endif
@if($currentPage > 1 || $results->hasMorePages())
@php
    $pageUrl = $currentPage > 1
        ? $canonicalUrl . (str_contains($canonicalUrl, '?') ? '&' : '?') . 'page=' . $currentPage
        : $canonicalUrl;
    $ldPage  = array_filter([
        '@context'     => 'https://schema.org',
        '@type'        => 'CollectionPage',
        'name'         => $pageTitle,
        'url'          => $pageUrl,
        'isPartOf'     => ['@type' => 'WebSite', 'url' => $canonicalUrl],
        'previousPage' => $prevUrl ?: null,
        'nextPage'     => $nextUrl ?: null,
    ]);
@endphp
<script type="application/ld+json">{!! json_encode($ldPage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif
@php
    // BreadcrumbList
    $breadcrumbs = [['@type' => 'ListItem', 'position' => 1, 'name' => 'ホーム', 'item' => url('/')]];
    $pos = 2;
    if ($gender === 'business') {
        $breadcrumbs[] = ['@type' => 'ListItem', 'position' => $pos++, 'name' => '夜遊びリスト', 'item' => url('/business/all/all/')];
    } elseif ($gender === 'female') {
        $breadcrumbs[] = ['@type' => 'ListItem', 'position' => $pos++, 'name' => '女性ナイトワーク', 'item' => url('/female/all/all/')];
    } else {
        $breadcrumbs[] = ['@type' => 'ListItem', 'position' => $pos++, 'name' => '男性ナイトワーク', 'item' => url('/male/all/all/')];
    }
    if (isset($prefModel) && $prefModel && empty($isPrefPage)) {
        $breadcrumbs[] = ['@type' => 'ListItem', 'position' => $pos++, 'name' => $prefModel->name, 'item' => url("/{$gender}/{$prefModel->slug}/all/")];
    }
    if ($displayArea) {
        $breadcrumbs[] = ['@type' => 'ListItem', 'position' => $pos++, 'name' => $displayArea, 'item' => isset($area_slug) ? url("/{$gender}/{$area_slug}/all/") : null];
    }
    if ($displayJob) {
        $breadcrumbs[] = ['@type' => 'ListItem', 'position' => $pos++, 'name' => $displayJob, 'item' => $canonicalUrl];
    }
    $breadcrumbs = array_map(fn($b) => array_filter($b), $breadcrumbs);
    $ldBreadcrumb = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $breadcrumbs];
@endphp
<script type="application/ld+json">{!! json_encode($ldBreadcrumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')

{{-- パンくずリスト --}}
<nav aria-label="パンくずリスト" class="bg-gray-50 border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-4 py-2">
        <ol class="flex flex-wrap items-center gap-1 text-xs text-gray-500">
            <li><a href="{{ url('/') }}" class="hover:text-gray-700">ホーム</a></li>
            @foreach(array_slice($breadcrumbs, 1) as $crumb)
            <li class="flex items-center gap-1">
                <span class="text-gray-300">›</span>
                @if(isset($crumb['item']) && $crumb['item'] !== end($breadcrumbs)['item'])
                <a href="{{ $crumb['item'] }}" class="hover:text-gray-700">{{ $crumb['name'] }}</a>
                @else
                <span class="text-gray-700">{{ $crumb['name'] }}</span>
                @endif
            </li>
            @endforeach
        </ol>
    </div>
</nav>

{{-- カラーバー --}}
<div class="{{ $c['bg'] }} text-white py-4">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-center gap-3">
            <h1 class="text-lg font-bold">
                @if($gender === 'business')
                    @if($displayArea || $displayJob)
                        {{ $displayArea }}{{ $displayJob ? '　' . $displayJob : '' }}の夜遊びスポット情報
                    @else
                        夜遊びスポット・ナイト系店舗情報
                    @endif
                @else
                    @if($displayArea || $displayJob)
                        {{ $displayArea }}{{ $displayJob ? '　' . $displayJob : '' }}の{{ $c['label'] }}求人
                    @else
                        {{ $c['label'] }}求人一覧
                    @endif
                @endif
            </h1>
            {{-- 絞り込みフォーム --}}
            <form action="{{ route('search') }}/" method="GET" class="flex gap-2 ml-auto">
                <input type="hidden" name="gender" value="{{ $gender }}">
                <input type="text" name="area" value="{{ $displayArea }}"
                       placeholder="エリア・駅名"
                       class="bg-white/20 border border-white/40 rounded px-3 py-1 text-sm text-white placeholder-white/60 focus:outline-none focus:bg-white/30 w-32">
                <input type="text" name="keyword" value="{{ $displayJob }}"
                       placeholder="{{ $gender === 'business' ? '業種・店名' : '職種・業種' }}"
                       class="bg-white/20 border border-white/40 rounded px-3 py-1 text-sm text-white placeholder-white/60 focus:outline-none focus:bg-white/30 w-32">
                <button type="submit" class="bg-white/20 hover:bg-white/30 border border-white/40 text-white text-sm px-3 py-1 rounded transition">
                    検索
                </button>
            </form>
        </div>
    </div>
</div>

@php
    $wageType = $wageType ?? '';
    $wageMin  = $wageMin  ?? 0;
    $wagePresets = [
        'hourly' => [3000 => '時給3,000円以上', 5000 => '時給5,000円以上', 8000 => '時給8,000円以上', 10000 => '時給10,000円以上'],
        'daily'  => [20000 => '日給20,000円以上', 40000 => '日給40,000円以上', 60000 => '日給60,000円以上'],
    ];
    $hasWageFilter = $wageType && $wageMin > 0;
@endphp

<div class="bg-gray-50 border-b border-gray-200">
    <div class="max-w-6xl mx-auto px-4 py-4"
         x-data="{ detail: {{ $hasWageFilter ? 'true' : 'false' }} }">
        <form action="{{ route('search') }}/" method="GET">
            <input type="hidden" name="gender" value="{{ $gender }}">
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" name="area" value="{{ $displayArea }}"
                       placeholder="エリア・駅名"
                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-{{ $gender === 'male' ? 'male-500' : 'female-400' }}">
                @if(!($isLp ?? false))
                <input type="text" name="keyword" value="{{ $keyword ?? '' }}"
                       placeholder="{{ $gender === 'business' ? '業種・店名（例：キャバクラ）' : ($gender === 'male' ? '職種・業種（例：黒服、キャバクラ）' : '職種・業種（例：キャスト、ガールズバー）') }}"
                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-{{ $gender === 'male' ? 'male-500' : 'female-400' }}">
                @else
                <input type="hidden" name="keyword" value="{{ $jobTypeName ?? '' }}">
                @endif
                <button type="submit"
                        class="{{ $c['btn'] }} text-white text-sm font-bold px-5 py-2 rounded-lg transition whitespace-nowrap">
                    再検索
                </button>
            </div>
            {{-- クイックタグ --}}
            @if($gender === 'business')
            @php
                $allYouCanDrink   = $allYouCanDrink ?? false;
                $hasKaraoke       = $hasKaraoke ?? false;
                $hasPrivateRoom   = $hasPrivateRoom ?? false;
                $discountFirstSet = $discountFirstSet ?? false;
                $businessChips  = [
                    'all_you_can_drink'  => ['label' => '飲み放題', 'active' => $allYouCanDrink],
                    'has_karaoke'        => ['label' => 'カラオケ',   'active' => $hasKaraoke],
                    'has_private_room'   => ['label' => '個室あり',   'active' => $hasPrivateRoom],
                    'discount_first_set' => ['label' => '初回割引',   'active' => $discountFirstSet],
                ];
                // チップのURL生成：LPならpathベース、通常検索ならqueryベース
                $chipBase = $isLp
                    ? route('search.directory', ['gender' => $gender, 'area_slug' => $area_slug, 'job_slug' => $job_slug]) . '/'
                    : url('/search/');
                $chipQuery = array_filter([
                    'gender'             => $isLp ? null : $gender,
                    'area'               => $isLp ? null : ($area ?? ''),
                    'keyword'            => $isLp ? null : ($keyword ?? ''),
                    'all_you_can_drink'  => $allYouCanDrink   ? 1 : null,
                    'has_karaoke'        => $hasKaraoke       ? 1 : null,
                    'has_private_room'   => $hasPrivateRoom   ? 1 : null,
                    'discount_first_set' => $discountFirstSet ? 1 : null,
                ], fn($v) => $v !== null && $v !== '');
            @endphp
            <div class="flex flex-wrap gap-2 mt-3">
                @foreach($businessChips as $param => $chip)
                    @php
                        if ($chip['active']) {
                            // アクティブ → クリックで除去
                            $params = array_filter(array_merge($chipQuery, [$param => null]), fn($v) => $v !== null);
                        } else {
                            // 非アクティブ → クリックで追加
                            $params = array_merge($chipQuery, [$param => 1]);
                        }
                        $chipUrl = $chipBase . ($params ? '?' . http_build_query($params) : '');
                    @endphp
                    <a href="{{ $chipUrl }}"
                       class="text-xs border rounded-full px-3 py-1.5 transition whitespace-nowrap
                              {{ $chip['active']
                                  ? $c['btn'] . ' text-white border-transparent'
                                  : 'bg-white ' . $c['text'] . ' border-gray-300 hover:border-current' }}">
                        @if($chip['active'])✓ @endif{{ $chip['label'] }}
                    </a>
                @endforeach
            </div>
            @else
            @php
                $currentArea    = $area ?? ($areaName ?? '');
                $currentKeyword = $keyword ?? '';
                $isLpMode       = isset($area_slug) && isset($job_slug);
                $activeFilter   = $filter_slug ?? null;
                // slug => label のマッピング（job_typesのkeyword_filter型スラッグと一致させる）
                $quickTags = [
                    ['slug' => 'mikeiken', 'label' => '未経験歓迎'],
                    ['slug' => 'hibarai',  'label' => '日払いOK'],
                ];
                // アルバイトタグ用URL（wage_type=hourly + employment_type=PART_TIMEをまとめてON/OFF）
                $isArubaitoActive = $arubaito ?? false;
                $currentQs = request()->query();
                if ($isArubaitoActive) {
                    unset($currentQs['arubaito']);
                    $arubaitoUrl = url()->current() . ($currentQs ? '?' . http_build_query($currentQs) : '');
                } else {
                    $currentQs['arubaito'] = 1;
                    $arubaitoUrl = url()->current() . '?' . http_build_query($currentQs);
                }
            @endphp
            <div class="flex flex-wrap gap-2 mt-3">
                @foreach($quickTags as $tag)
                    @php
                        if ($isLpMode) {
                            $isActive = $activeFilter === $tag['slug'];
                            $tagUrl   = $isActive
                                ? route('search.directory', ['gender' => $gender, 'area_slug' => $area_slug, 'job_slug' => $job_slug]) . '/'
                                : route('search.filtered_directory', ['gender' => $gender, 'area_slug' => $area_slug, 'job_slug' => $job_slug, 'filter_slug' => $tag['slug']]) . '/';
                        } else {
                            $isActive = $currentKeyword === $tag['label'];
                            $tagParams = array_filter([
                                'gender'    => $gender,
                                'area'      => $currentArea,
                                'keyword'   => $isActive ? '' : $tag['label'],
                                'wage_type' => $wageType ?: null,
                                'wage_min'  => $wageMin  ?: null,
                            ]);
                            $tagUrl = route('search') . '/?' . http_build_query($tagParams);
                        }
                    @endphp
                    <a href="{{ $tagUrl }}"
                       class="text-xs border rounded-full px-3 py-1.5 transition whitespace-nowrap
                              {{ $isActive
                                  ? $c['btn'] . ' text-white border-transparent'
                                  : 'bg-white ' . $c['text'] . ' border-gray-300 hover:border-current' }}">
                        @if($isActive)✓ @endif{{ $tag['label'] }}
                    </a>
                @endforeach
                {{-- アルバイト（時給 × PART_TIME）タグ：男性のみ --}}
                @if($gender === 'male')
                <a href="{{ $arubaitoUrl }}"
                   class="text-xs border rounded-full px-3 py-1.5 transition whitespace-nowrap
                          {{ $isArubaitoActive
                              ? $c['btn'] . ' text-white border-transparent'
                              : 'bg-white ' . $c['text'] . ' border-gray-300 hover:border-current' }}">
                    @if($isArubaitoActive)✓ @endifアルバイト
                </a>
                @endif
            </div>
            @endif

            {{-- 詳細条件（時給フィルタ） --}}
            @if($gender !== 'business')
            <div class="mt-2">
                <button type="button" @click="detail = !detail"
                        class="text-xs {{ $c['text'] }} hover:opacity-80 flex items-center gap-1">
                    <span x-text="detail ? '▲ 詳細条件を閉じる' : '▼ 詳細条件（給与で絞り込む）'">▼ 詳細条件（給与で絞り込む）</span>
                    @if($hasWageFilter)
                        <span class="ml-2 bg-yellow-100 text-yellow-700 border border-yellow-300 rounded-full px-2 py-0.5 text-xs">設定中</span>
                    @endif
                </button>
                <div x-show="detail" x-transition class="mt-3 flex flex-wrap items-center gap-3">
                    <select name="wage_type"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none bg-white">
                        <option value="">給与形態を選択</option>
                        <option value="hourly" {{ $wageType === 'hourly' ? 'selected' : '' }}>時給</option>
                        <option value="daily"  {{ $wageType === 'daily'  ? 'selected' : '' }}>日給</option>
                    </select>
                    <select name="wage_min"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none bg-white">
                        <option value="0">金額下限を選択</option>
                        @foreach($wagePresets as $type => $presets)
                            @foreach($presets as $val => $label)
                                <option value="{{ $val }}" {{ (int)$wageMin === $val && $wageType === $type ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                    @if($hasWageFilter)
                        <a href="{{ route('search', array_filter(['gender' => $gender, 'area' => $area ?? '', 'keyword' => $keyword ?? ''], fn($v) => $v !== '')) }}"
                           class="text-xs text-gray-400 hover:text-gray-600 underline">条件をリセット</a>
                    @endif
                </div>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-6">

    {{-- 件数 --}}
    <p class="text-sm text-gray-500 mb-4">
        {{ number_format($results->total()) }}件
        @if($hasWageFilter)
            <span class="ml-2 text-xs bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-full px-2 py-0.5">
                {{ ['hourly'=>'時給','daily'=>'日給'][$wageType] }}{{ number_format($wageMin) }}円以上で絞り込み中
            </span>
        @endif
    </p>

    {{-- LP導入文（ディレクトリURLのみ表示） --}}
    @if($isLp && !$results->isEmpty())
    <div class="bg-white border border-gray-100 rounded-xl p-4 mb-6 text-sm text-gray-600 leading-relaxed space-y-2">
        @if($gender === 'business')
            @if($displayArea && $displayJob)
                <p>{{ $displayArea }}の{{ $displayJob }}を{{ number_format($results->total()) }}店舗掲載中。営業時間・料金・アクセスなど店舗情報をまとめてチェックできます。</p>
                <p>飲み放題・カラオケ・個室・初回割引などの条件でも絞り込めます。{{ $displayArea }}でお気に入りの{{ $displayJob }}を見つけてください。</p>
            @elseif($displayArea)
                <p>{{ $displayArea }}エリアの夜遊びスポット・ナイト系店舗情報を{{ number_format($results->total()) }}件掲載中。キャバクラ・クラブ・バーなど{{ $displayArea }}の夜遊び情報をまとめてチェックできます。</p>
                <p>飲み放題・カラオケ・個室・初回割引などの条件で絞り込みが可能。{{ $displayArea }}で今夜の夜遊び先を探してみましょう。</p>
            @elseif($displayJob)
                <p>{{ $displayJob }}の店舗情報を{{ number_format($results->total()) }}件掲載中。全国の{{ $displayJob }}をエリアから探せます。営業時間・料金もまとめて確認できます。</p>
                <p>エリアや設備・サービスから絞り込んで、あなたの目的に合った{{ $displayJob }}を見つけましょう。</p>
            @else
                <p>全国の夜遊びスポット・ナイト系店舗情報を{{ number_format($results->total()) }}件掲載中。エリア・業種から絞り込んで夜遊び場所を探しましょう。</p>
                <p>キャバクラ・ガールズバー・クラブ・バーなど多彩な業態を掲載。飲み放題・カラオケ・個室ありの店舗も一覧で確認できます。</p>
            @endif
        @else
            @if($displayArea && $displayJob)
                <p>{{ $displayArea }}で{{ $displayJob }}として働きたい方向けの{{ $c['label'] }}求人を{{ number_format($results->total()) }}件掲載しています。日払い・週払い・未経験歓迎など、さまざまな条件の求人を掲載中です。</p>
                <p>時給・勤務日数・経験の有無など細かい条件でも絞り込めます。{{ $displayArea }}の{{ $displayJob }}求人をまとめて比較してみましょう。</p>
            @elseif($displayArea)
                <p>{{ $displayArea }}エリアの{{ $c['label'] }}求人を{{ number_format($results->total()) }}件掲載しています。{{ $gender === 'male' ? '黒服・ボーイ・フロアスタッフなど' : 'キャバクラ・ガールズバー・ラウンジなど' }}、{{ $displayArea }}のナイトワーク求人情報をまとめてチェックできます。</p>
                <p>{{ $gender === 'male' ? '経験・未経験問わず幅広い職種を掲載。日払い・週払い・高時給の求人も揃っています。' : '未経験歓迎・日払いOK・高時給など条件から絞り込めます。まずは気軽に体験入店から始める方も歓迎している店舗が多数掲載中です。' }}</p>
            @elseif($displayJob)
                <p>{{ $displayJob }}の{{ $c['label'] }}求人を{{ number_format($results->total()) }}件掲載しています。未経験歓迎・高収入・日払いOKなど、さまざまな{{ $displayJob }}求人をエリアから探せます。</p>
                <p>エリア・時給・勤務形態などの条件から絞り込んで、あなたのライフスタイルに合った{{ $displayJob }}の仕事を見つけましょう。</p>
            @else
                <p>{{ $c['label'] }}の求人情報を{{ number_format($results->total()) }}件掲載しています。エリア・職種・条件から絞り込んで、あなたに合ったナイトワーク求人を見つけてください。</p>
                <p>日払い・週払い・未経験歓迎・高時給など多彩な条件の求人を掲載。気になる求人への応募もナイトワークリストから簡単に行えます。</p>
            @endif
        @endif
    </div>
    @endif

    {{-- LINEアラート告知バナー（female/male・1ページ目のみ） --}}
    @if($gender !== 'business' && $currentPage === 1)
    <a href="{{ route('alert.register', ['gender' => $gender]) }}" rel="nofollow"
       class="flex items-center gap-3 bg-green-50 hover:bg-green-100 border border-green-200 rounded-xl px-4 py-3.5 mb-5 transition group">
        <div class="text-2xl shrink-0">🔔</div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-green-800">新着求人をLINEで受け取る</p>
            <p class="text-xs text-green-700 mt-0.5">エリア・職種を設定してLINEにお知らせ。無料で登録できます。</p>
        </div>
        <div class="text-green-600 shrink-0">›</div>
    </a>
    @endif

    {{-- LP統計バー（6件以上のインデックスページのみ） --}}
    @if($isLp && !empty($lpStats))
    @php
        $statsPrefix = implode('', array_filter([$displayArea, $displayJob]));
        if ($gender === 'business') {
            $statsLabel = $statsPrefix ? "{$statsPrefix}の夜遊びスポットの統計データ" : '夜遊びスポットの統計データ';
        } else {
            $statsLabel = $statsPrefix ? "{$statsPrefix}の{$c['label']}の統計データ" : "{$c['label']}の統計データ";
        }
    @endphp
    <p class="text-xs font-medium text-gray-400 mb-2">{{ $statsLabel }}</p>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @if($gender === 'business')
            @if($lpStats['all_you_can_drink'] > 0)
            <div class="bg-white border border-gray-100 rounded-xl px-4 py-3 text-center">
                <p class="text-lg font-bold {{ $c['text'] }}">{{ number_format($lpStats['all_you_can_drink']) }}店</p>
                <p class="text-xs text-gray-500 mt-1">飲み放題あり</p>
            </div>
            @endif
            @if($lpStats['has_karaoke'] > 0)
            <div class="bg-white border border-gray-100 rounded-xl px-4 py-3 text-center">
                <p class="text-lg font-bold {{ $c['text'] }}">{{ number_format($lpStats['has_karaoke']) }}店</p>
                <p class="text-xs text-gray-500 mt-1">カラオケあり</p>
            </div>
            @endif
            @if($lpStats['has_private_room'] > 0)
            <div class="bg-white border border-gray-100 rounded-xl px-4 py-3 text-center">
                <p class="text-lg font-bold {{ $c['text'] }}">{{ number_format($lpStats['has_private_room']) }}店</p>
                <p class="text-xs text-gray-500 mt-1">個室あり</p>
            </div>
            @endif
            @if($lpStats['discount_first_set'] > 0)
            <div class="bg-white border border-gray-100 rounded-xl px-4 py-3 text-center">
                <p class="text-lg font-bold {{ $c['text'] }}">{{ number_format($lpStats['discount_first_set']) }}店</p>
                <p class="text-xs text-gray-500 mt-1">初回割引あり</p>
            </div>
            @endif
        @else
            <div class="bg-white border border-gray-100 rounded-xl px-4 py-3 text-center">
                <p class="text-lg font-bold {{ $c['text'] }}">{{ number_format($results->total()) }}件</p>
                <p class="text-xs text-gray-500 mt-1">掲載求人数</p>
            </div>
            {{-- 時給：female/male 共通、5件以上のみ表示 --}}
            @if(!empty($lpStats['avg_hourly']))
            <div class="bg-white border border-gray-100 rounded-xl px-4 py-3 text-center">
                <p class="text-lg font-bold {{ $c['text'] }}">{{ number_format($lpStats['avg_hourly']) }}円</p>
                <p class="text-xs text-gray-500 mt-1">平均時給（最低）</p>
            </div>
            @endif
            {{-- 月給：male のみ、5件以上表示 --}}
            @if(!empty($lpStats['avg_monthly']))
            <div class="bg-white border border-gray-100 rounded-xl px-4 py-3 text-center">
                <p class="text-lg font-bold {{ $c['text'] }}">{{ number_format($lpStats['avg_monthly']) }}円</p>
                <p class="text-xs text-gray-500 mt-1">平均月給（最低）</p>
            </div>
            @endif
            @if($lpStats['daily_count'] > 0)
            <div class="bg-white border border-gray-100 rounded-xl px-4 py-3 text-center">
                <p class="text-lg font-bold {{ $c['text'] }}">{{ number_format($lpStats['daily_count']) }}件</p>
                <p class="text-xs text-gray-500 mt-1">日払いOK</p>
            </div>
            @endif
            @if(!empty($lpStats['part_time_count']) && $lpStats['part_time_count'] > 0)
            <div class="bg-white border border-gray-100 rounded-xl px-4 py-3 text-center">
                <p class="text-lg font-bold {{ $c['text'] }}">{{ number_format($lpStats['part_time_count']) }}件</p>
                <p class="text-xs text-gray-500 mt-1">アルバイト</p>
            </div>
            @endif
        @endif
    </div>
    @endif

    @if($results->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <p class="text-lg">該当する{{ $gender === 'business' ? '夜遊びスポット' : $c['label'] . '求人' }}が見つかりませんでした</p>
            <a href="{{ route('top') }}" class="mt-4 inline-block text-sm {{ $c['text'] }} hover:underline">
                ← トップに戻る
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($results as $item)
                @if($gender === 'business')
                    {{-- 営業情報カード --}}
                    <a href="{{ url('/track/shop/' . $item->shop->id) . '/' }}"
                       rel="nofollow"
                       class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition overflow-hidden block">
                        @if($item->shop->main_image)
                            <picture>
                                <source srcset="{{ asset('storage/' . \App\Services\ImageService::webpPath($item->shop->main_image)) }}" type="image/webp">
                                <img src="{{ asset('storage/' . $item->shop->main_image) }}"
                                     alt="{{ $item->shop->name }}"
                                     width="640" height="360"
                                     @if($loop->first) fetchpriority="high" @else loading="lazy" decoding="async" @endif
                                     class="w-full aspect-video object-cover">
                            </picture>
                        @else
                            <div class="aspect-video {{ $c['bg'] }} opacity-30"></div>
                        @endif
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h2 class="font-bold text-gray-800 text-sm leading-tight">
                                    {{ $item->shop->name ?? '店舗名未設定' }}
                                </h2>
                                @if($item->shop->genre)
                                    <span class="text-xs px-2 py-0.5 {{ $c['tag'] }} border rounded-full whitespace-nowrap">
                                        {{ $item->shop->genre->name }}
                                    </span>
                                @endif
                            </div>
                            @if($item->set_price)
                                <p class="text-xs text-gray-500 mb-1">セット料金：{{ $item->set_price }}</p>
                            @endif
                            @if($item->opening_hours)
                                <p class="text-xs text-gray-500 mb-2">
                                    営業時間：{{ $item->opening_hours }}〜{{ $item->closing_hours }}
                                </p>
                            @endif
                            @if($item->shop->nearest_station_name)
                                <p class="text-xs text-gray-500 mb-1">
                                    🚉
                                    @if($item->shop->nearest_line){{ $item->shop->nearest_line }} @endif
                                    {{ $item->shop->nearest_station_name }}駅
                                    @if($item->shop->nearest_station_walk) 徒歩{{ $item->shop->nearest_station_walk }}分 @endif
                                </p>
                            @endif
                            <div class="flex flex-wrap gap-1 mt-2">
                                @if($item->all_you_can_drink)
                                    <span class="text-xs px-2 py-0.5 {{ $c['tag'] }} border rounded-full">飲み放題</span>
                                @endif
                                @if($item->has_karaoke)
                                    <span class="text-xs px-2 py-0.5 {{ $c['tag'] }} border rounded-full">カラオケ</span>
                                @endif
                                @if($item->has_private_room)
                                    <span class="text-xs px-2 py-0.5 {{ $c['tag'] }} border rounded-full">個室あり</span>
                                @endif
                                @if($item->discount_first_set)
                                    <span class="text-xs px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-300 rounded-full font-bold">初回割引</span>
                                @endif
                            </div>
                            @if($item->shop->area)
                                <p class="text-xs text-gray-400 mt-1">📍 {{ $item->shop->area->name }}</p>
                            @endif
                        </div>
                    </a>
                @else
                    {{-- 求人カード --}}
                    <a href="{{ $item->is_hotlink && $item->hotlink_url ? url('/click/' . $item->id) . '/' : url('/track/job/' . $item->id) . '/' }}"
                       rel="nofollow"
                       class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition overflow-hidden block">
                        @php
                            $cardImg     = $item->image_path ?? $item->shop?->main_image;
                            $cardImgWebp = $cardImg ? \App\Services\ImageService::webpPath($cardImg) : null;
                        @endphp
                        @if($cardImg)
                            <picture>
                                <source srcset="{{ asset('storage/' . $cardImgWebp) }}" type="image/webp">
                                <img src="{{ asset('storage/' . $cardImg) }}"
                                     alt="{{ $item->title }}"
                                     width="640" height="360"
                                     @if($loop->first) fetchpriority="high" @else loading="lazy" decoding="async" @endif
                                     class="w-full aspect-video object-cover">
                            </picture>
                        @else
                            <div class="aspect-video {{ $c['bg'] }} opacity-30"></div>
                        @endif
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h2 class="font-bold text-gray-800 text-sm leading-tight">
                                    {{ $item->title }}
                                </h2>
                                @if($item->is_hotlink)
                                    <span class="text-xs px-2 py-0.5 bg-orange-100 border border-orange-300 text-orange-700 rounded-full whitespace-nowrap">
                                        PR
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-600 mb-2">{{ $item->shop->name ?? '' }}</p>
                            @if($item->hourly_wage_min)
                                <p class="text-sm font-bold {{ $c['text'] }}">
                                    {{ ['hourly'=>'時給','daily'=>'日給','monthly'=>'月給'][$item->wage_type ?? 'hourly'] }}
                                    {{ number_format($item->hourly_wage_min) }}円〜
                                    @if($item->hourly_wage_max)
                                        {{ number_format($item->hourly_wage_max) }}円
                                    @endif
                                </p>
                            @endif
                            @if($item->jobType)
                                <span class="inline-block mt-2 text-xs px-2 py-0.5 {{ $c['tag'] }} border rounded-full">
                                    {{ $item->jobType->name }}
                                </span>
                            @endif
                            @if($item->working_hours)
                                <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $item->working_hours }}
                                </p>
                            @endif
                            @if($item->area)
                                <p class="text-xs text-gray-400 mt-1">📍 {{ $item->area->name }}</p>
                            @endif
                        </div>
                    </a>
                @endif
            @endforeach
        </div>

        {{-- ページネーション --}}
        <div class="mt-8">
            {{ $results->appends(request()->query())->links() }}
        </div>
    @endif

    {{-- 関連コラム（LPのみ・1ページ目・記事あり） --}}
    @if($isLp && $currentPage === 1 && isset($relatedArticles) && $relatedArticles->isNotEmpty())
    <div class="mt-10">
        <p class="text-xs font-bold text-gray-400 mb-3">関連コラム・ガイド</p>
        <div class="space-y-3">
            @foreach($relatedArticles as $ra)
            <a href="{{ route('article.show', $ra->slug) }}"
               class="flex items-start gap-3 bg-white border border-gray-100 rounded-xl px-4 py-3 hover:shadow-sm transition">
                @if($ra->hero_image)
                <picture class="shrink-0">
                    <source srcset="{{ asset('storage/' . \App\Services\ImageService::webpPath($ra->hero_image)) }}" type="image/webp">
                    <img src="{{ asset('storage/' . $ra->hero_image) }}"
                         alt="{{ $ra->title }}"
                         width="80" height="56"
                         loading="lazy" decoding="async"
                         class="w-20 h-14 object-cover rounded-lg">
                </picture>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-gray-800 leading-snug line-clamp-2">{{ $ra->title }}</p>
                    @if($ra->lead)
                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $ra->lead }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">{{ $ra->published_at?->format('Y年n月j日') }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 関連エリア・関連職種リンク（LPのみ） --}}
    @if($isLp && isset($lpRelated))
    @php
        $hasRelatedAreas = isset($lpRelated['areas']) && $lpRelated['areas']->isNotEmpty();
        $hasRelatedTypes = isset($lpRelated['types']) && $lpRelated['types']->isNotEmpty();
        $typeLabel = $gender === 'business' ? '業種' : '職種';
    @endphp
    @if($hasRelatedAreas || $hasRelatedTypes)
    <div class="mt-10 space-y-4">
        @if($hasRelatedAreas)
        <div class="bg-white border border-gray-100 rounded-xl px-5 py-4">
            <p class="text-xs font-bold text-gray-400 mb-3">関連エリア</p>
            <div class="flex flex-wrap gap-2">
                @foreach($lpRelated['areas'] as $relArea)
                <a href="{{ route('search.directory', ['gender' => $gender, 'area_slug' => $relArea->slug, 'job_slug' => $job_slug ?? 'all']) }}/"
                   class="px-3 py-1 text-xs rounded-full border {{ $c['tag'] }} hover:opacity-80 transition">
                    {{ $relArea->name }}
                </a>
                @endforeach
            </div>
        </div>
        @endif
        @if($hasRelatedTypes)
        <div class="bg-white border border-gray-100 rounded-xl px-5 py-4">
            <p class="text-xs font-bold text-gray-400 mb-3">関連{{ $typeLabel }}</p>
            <div class="flex flex-wrap gap-2">
                @foreach($lpRelated['types'] as $relType)
                <a href="{{ route('search.directory', ['gender' => $gender, 'area_slug' => $area_slug ?? 'all', 'job_slug' => $relType->slug]) }}/"
                   class="px-3 py-1 text-xs rounded-full border {{ $c['tag'] }} hover:opacity-80 transition">
                    {{ $relType->name }}
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif
    @endif

    {{-- 求人アラート登録バナー（female/male のLPのみ、business は除外） --}}
    @if($isLp && $gender !== 'business')
    <div class="mt-10 bg-green-50 border border-green-200 rounded-xl px-5 py-4 flex flex-col sm:flex-row items-start sm:items-center gap-3">
        <div class="flex-1">
            <p class="font-bold text-green-800 text-sm mb-1">新着求人をLINEで受け取る</p>
            <p class="text-xs text-green-700">
                @if($hasArea && $hasJob)
                    {{ $displayArea }}の{{ $displayJob }}求人が公開されたらLINEでお知らせします。
                @elseif($hasArea)
                    {{ $displayArea }}の新着{{ $c['label'] }}求人をLINEでお知らせします。
                @else
                    条件に合う新着求人が公開されたらLINEでお知らせします。
                @endif
            </p>
        </div>
        <a href="{{ route('alert.register', ['gender' => $gender]) }}"
           rel="nofollow"
           class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white text-sm font-bold px-4 py-2 rounded-lg transition whitespace-nowrap">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63.349 0 .631.285.631.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.070 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/></svg>
            求人アラートを登録する
        </a>
    </div>
    @endif

</div>
@endsection
