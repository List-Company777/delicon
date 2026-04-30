@php
    $detail = $shop->detail;
@endphp

@extends('layouts.app')

@section('canonical', route('shop.show', $shop->id) . '/')
@section('title', $shop->name . ' | 夜遊び情報')
@section('description',
    ($shop->genre ? $shop->genre->name . '・' : '') .
    ($shop->area  ? $shop->area->name . '　'  : '') .
    ($detail->content ? mb_strimwidth(strip_tags($detail->content), 0, 100, '…') : $shop->name . 'の営業情報')
)
@if($shop->main_image)
@section('ogp_image', asset('storage/' . \App\Services\ImageService::webpPath($shop->main_image)))
@section('twitter_card', 'summary_large_image')
@endif

@push('head')
@php
    // ジャンルスラッグ → Schema.org @type マッピング
    $genreTypeMap = [
        'cabaret'   => 'NightClub',
        'host'      => 'NightClub',
        'club'      => 'NightClub',
        'lounge'    => 'NightClub',
        'girls-bar' => 'BarOrPub',
        'snack'     => 'BarOrPub',
        'pub'       => 'BarOrPub',
        'bar'       => 'BarOrPub',
    ];
    $ldType = $shop->genre ? ($genreTypeMap[$shop->genre->slug] ?? 'LocalBusiness') : 'LocalBusiness';

    $ld = [
        '@context' => 'https://schema.org',
        '@type'    => $ldType,
        'name'     => $shop->name,
        'url'      => $shop->externalUrls->where('url_type', 'website')->first()?->url ?? route('shop.show', $shop->id) . '/',
        'address'  => [
            '@type'          => 'PostalAddress',
            'addressCountry' => 'JP',
        ],
    ];
    if ($shop->postal_code)       $ld['address']['postalCode']       = $shop->postal_code;
    if ($shop->prefecture)        $ld['address']['addressRegion']   = $shop->prefecture->name;
    $locality = $shop->address_locality ?? $shop->area?->name ?? null;
    if ($locality)                $ld['address']['addressLocality'] = $locality;
    if ($shop->address)           $ld['address']['streetAddress']   = $shop->address;
    if ($shop->tel)        $ld['telephone'] = $shop->tel;
    if ($shop->genre)      $ld['description'] = $shop->genre->name . 'の営業情報';
    if ($detail->content)  $ld['description'] = mb_strimwidth(strip_tags($detail->content), 0, 200, '…');
    // image: メイン画像 + ギャラリー画像を ImageObject 配列で出力
    $imageObjects = [];
    if ($shop->main_image) {
        $imageObjects[] = [
            '@type'      => 'ImageObject',
            'url'        => asset('storage/' . $shop->main_image),
            'contentUrl' => asset('storage/' . $shop->main_image),
            'width'      => 1280,
            'height'     => 720,
        ];
    }
    if ($detail->image_paths) {
        foreach ($detail->image_paths as $imgPath) {
            $imgObj = [
                '@type'      => 'ImageObject',
                'url'        => asset('storage/' . $imgPath),
                'contentUrl' => asset('storage/' . $imgPath),
            ];
            $imgSize = @getimagesize(storage_path('app/public/' . $imgPath));
            if ($imgSize) {
                $imgObj['width']  = $imgSize[0];
                $imgObj['height'] = $imgSize[1];
            }
            $imageObjects[] = $imgObj;
        }
    }
    if (count($imageObjects) === 1) $ld['image'] = $imageObjects[0];
    elseif (count($imageObjects) > 1) $ld['image'] = $imageObjects;

    // OpeningHoursSpecification（曜日・時刻ともに入力済みの場合のみ出力）
    $dayNameMap = [
        'Mo' => 'Monday', 'Tu' => 'Tuesday',  'We' => 'Wednesday',
        'Th' => 'Thursday', 'Fr' => 'Friday', 'Sa' => 'Saturday', 'Su' => 'Sunday',
    ];
    $openDays = is_array($detail->opening_days) ? $detail->opening_days : [];
    $openTime = $detail->opening_hours;
    $closeTime = $detail->closing_hours;
    if (
        count($openDays) > 0 &&
        $openTime && preg_match('/^\d{2}:\d{2}$/', $openTime)
    ) {
        $spec = [
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => array_values(array_filter(array_map(
                fn($d) => $dayNameMap[$d] ?? null,
                $openDays
            ))),
            'opens'  => $openTime,
        ];
        if ($closeTime && preg_match('/^\d{2}:\d{2}$/', $closeTime)) {
            $spec['closes'] = $closeTime;
        }
        $ld['openingHoursSpecification'] = $spec;
    }
@endphp
<script type="application/ld+json" @nonce>
{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG) !!}
</script>
@if(!empty($detail->faq))
@php
    $shopFaqLd = [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => collect($detail->faq)->map(fn($item) => [
            '@type'          => 'Question',
            'name'           => $item['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
        ])->values()->all(),
    ];
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($shopFaqLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endif
@php
    $bcItems = [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'ナイトワーク', 'item' => route('top') . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => '夜遊び',       'item' => route('search.directory', ['gender' => 'yoasobi', 'area_slug' => 'all', 'job_slug' => 'all']) . '/'],
    ];
    $bcPos = 3;
    if ($shop->prefecture) $bcItems[] = ['@type' => 'ListItem', 'position' => $bcPos++, 'name' => $shop->prefecture->name, 'item' => route('search.prefecture', ['gender' => 'yoasobi', 'pref_slug' => $shop->prefecture->slug]) . '/'];
    if ($shop->area)       $bcItems[] = ['@type' => 'ListItem', 'position' => $bcPos++, 'name' => $shop->area->name, 'item' => route('search.directory', ['gender' => 'yoasobi', 'area_slug' => $shop->area->slug, 'job_slug' => 'all']) . '/'];
    $bcItems[] = ['@type' => 'ListItem', 'position' => $bcPos, 'name' => $shop->name, 'item' => route('shop.show', $shop->id) . '/'];
    $breadcrumb = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => $bcItems,
    ];
@endphp
<script type="application/ld+json" @nonce>
{!! json_encode($breadcrumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG) !!}
</script>
@endpush

@section('content')

{{-- パンくずナビ --}}
<nav aria-label="パンくずリスト" class="bg-business-700 text-white py-3">
    <div class="max-w-4xl mx-auto px-4 text-sm">
        <a href="{{ route('top') }}/" class="underline underline-offset-2 hover:no-underline text-white/90 hover:text-white">ナイトワーク</a>
        <span class="mx-2 opacity-40" aria-hidden="true">›</span>
        <a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => 'all', 'job_slug' => 'all']) }}/" class="underline underline-offset-2 hover:no-underline text-white/90 hover:text-white">夜遊び</a>
        @if($shop->prefecture)
        <span class="mx-2 opacity-40" aria-hidden="true">›</span>
        <a href="{{ route('search.prefecture', ['gender' => 'yoasobi', 'pref_slug' => $shop->prefecture->slug]) }}/" class="underline underline-offset-2 hover:no-underline text-white/90 hover:text-white">{{ $shop->prefecture->name }}</a>
        @endif
        @if($shop->area)
        <span class="mx-2 opacity-40" aria-hidden="true">›</span>
        <a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => $shop->area->slug, 'job_slug' => 'all']) }}/" class="underline underline-offset-2 hover:no-underline text-white/90 hover:text-white">{{ $shop->area->name }}</a>
        @endif
        <span class="mx-2 opacity-40" aria-hidden="true">›</span>
        <span class="opacity-90">{{ $shop->name }}</span>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-4 py-8">
    <article class="bg-white rounded-xl shadow-sm overflow-hidden">

        {{-- メイン画像 --}}
        @if($shop->main_image)
            <x-shop-image :src="$shop->main_image" :alt="$shop->name" class="w-full aspect-video object-cover" fetchpriority="high" loading="eager" />
        @else
            <div class="h-3 bg-business-700" aria-hidden="true"></div>
        @endif

        <div class="p-6 md:p-8">

            <header>
                {{-- タグ列 --}}
                <div class="flex flex-wrap gap-2 mb-3">
                    @if($shop->genre)
                        <a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => 'all', 'job_slug' => $shop->genre->slug]) }}/"
                           class="text-xs px-2 py-0.5 bg-business-50 border border-business-300 text-business-700 rounded-full hover:opacity-80 transition-opacity">{{ $shop->genre->name }}</a>
                    @endif
                    @if($shop->area)
                        <a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => $shop->area->slug, 'job_slug' => 'all']) }}/"
                           class="text-xs px-2 py-0.5 bg-gray-100 border border-gray-300 text-gray-600 rounded-full hover:opacity-80 transition-opacity">{{ $shop->area->name }}</a>
                    @endif
                    @if($detail->is_hotlink)
                        <span class="text-xs px-2 py-0.5 bg-orange-100 border border-orange-300 text-orange-700 rounded-full">PR</span>
                    @endif
                </div>

                {{-- 店舗名 --}}
                <h1 class="text-xl md:text-2xl font-bold text-gray-800 mb-2">{{ $shop->name }}</h1>

                {{-- ひとこと紹介 --}}
                @if($detail->short_description)
                    <p class="text-sm text-gray-500 mb-5">{{ $detail->short_description }}</p>
                @else
                    <div class="mb-5"></div>
                @endif
            </header>

            {{-- 割引・特典 --}}
            @if($detail && ($detail->discount_first_set || $detail->discount_custom))
            <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 space-y-1">
                @if($detail->discount_first_set)
                <p class="text-sm font-bold text-amber-800">🎉 初回セット料金10%オフ</p>
                @endif
                @if($detail->discount_custom)
                <p class="text-sm text-amber-700">{{ $detail->discount_custom }}</p>
                @endif
                <p class="text-xs text-amber-600">※ナイトワークリスト経由のご来店に適用されます</p>
            </div>
            @endif

            {{-- 営業情報テーブル --}}
            <section class="mb-6 border border-gray-100 rounded-lg overflow-hidden" aria-label="営業情報">
                <table class="w-full text-sm">
                <caption class="sr-only">{{ $shop->name }} の営業情報</caption>
                    @if($shop->genre)
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">業種</th>
                        <td class="px-4 py-3 text-gray-700">
                            @if($shop->area && $shop->genre->slug)
                                <a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => $shop->area->slug, 'job_slug' => $shop->genre->slug]) }}/"
                                   class="text-business-700 hover:underline">{{ $shop->genre->name }}</a>
                            @else
                                {{ $shop->genre->name }}
                            @endif
                        </td>
                    </tr>
                    @endif
                    @if($shop->pricePlans->isNotEmpty())
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap align-top">セット料金</th>
                        <td class="px-4 py-3 text-gray-700">
                            @foreach($shop->pricePlans as $plan)
                                @if($plan->name)
                                    <p class="text-xs font-bold text-gray-600 mt-2 mb-1 first:mt-0">{{ $plan->name }}</p>
                                @endif
                                @foreach($plan->setPrices as $sp)
                                    <div class="flex items-center gap-2 text-sm">
                                        @if($sp->time_from || $sp->time_to)
                                            <span class="text-gray-500 text-xs whitespace-nowrap">{{ $sp->time_from }}〜{{ $sp->time_to }}</span>
                                        @endif
                                        <span>{{ is_numeric($sp->price) ? number_format($sp->price) . '円' : $sp->price }}</span>
                                    </div>
                                @endforeach
                                @if($plan->extensionPrices->isNotEmpty())
                                    <div class="mt-1.5 space-y-0.5">
                                        @foreach($plan->extensionPrices as $ep)
                                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                                @if($ep->label)<span class="text-xs">{{ $ep->label }}</span>@endif
                                                <span>{{ is_numeric($ep->price) ? number_format($ep->price) . '円' : $ep->price }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </td>
                    </tr>
                    @elseif($shop->setPrices->isNotEmpty())
                    {{-- 後方互換: plan_id なしのセット料金 --}}
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap align-top">セット料金</th>
                        <td class="px-4 py-3 text-gray-700">
                            <div class="space-y-0.5">
                                @foreach($shop->setPrices as $sp)
                                <div class="flex items-center gap-2 text-sm">
                                    @if($sp->time_from || $sp->time_to)
                                        <span class="text-gray-500 text-xs whitespace-nowrap">{{ $sp->time_from }}〜{{ $sp->time_to }}</span>
                                    @endif
                                    <span>{{ is_numeric($sp->price) ? number_format($sp->price) . '円' : $sp->price }}</span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @elseif($detail->set_price)
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">セット料金</th>
                        <td class="px-4 py-3 text-gray-700">{{ $detail->set_price }}</td>
                    </tr>
                    @endif
                    @if($shop->otherCharges->isNotEmpty())
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap align-top">その他料金</th>
                        <td class="px-4 py-3 text-gray-700">
                            <div class="space-y-0.5">
                                @foreach($shop->otherCharges as $oc)
                                <div class="flex items-center gap-2 text-sm">
                                    @if($oc->label)<span class="text-gray-500 text-xs whitespace-nowrap">{{ $oc->label }}</span>@endif
                                    <span>{{ is_numeric($oc->price) ? number_format($oc->price) . '円' : $oc->price }}</span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @endif
                    @if($detail->nomination_fee)
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">指名料</th>
                        <td class="px-4 py-3 text-gray-700">{{ is_numeric($detail->nomination_fee) ? number_format($detail->nomination_fee) . '円' : $detail->nomination_fee }}</td>
                    </tr>
                    @endif
                    @if($detail->tax_included !== null)
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">税</th>
                        <td class="px-4 py-3 text-gray-700">{{ $detail->tax_included ? '消費税込み' : '消費税別途' }}</td>
                    </tr>
                    @endif
                    @if($detail->service_charge)
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">サービス料</th>
                        <td class="px-4 py-3 text-gray-700">{{ is_numeric($detail->service_charge) ? $detail->service_charge . '%' : $detail->service_charge }}</td>
                    </tr>
                    @endif
                    @if($detail->has_karaoke)
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">カラオケ</th>
                        <td class="px-4 py-3 text-gray-700">あり</td>
                    </tr>
                    @endif
                    @if($detail->all_you_can_drink)
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">飲み放題</th>
                        <td class="px-4 py-3 text-gray-700">あり</td>
                    </tr>
                    @endif
                    @if($detail->has_private_room)
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">個室</th>
                        <td class="px-4 py-3 text-gray-700">あり</td>
                    </tr>
                    @endif
                    @if($detail->opening_hours)
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">営業時間</th>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $detail->opening_hours }}
                            @if($detail->closing_hours) 〜 {{ $detail->closing_hours }} @endif
                        </td>
                    </tr>
                    @endif
                    @if($detail->holiday)
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">定休日</th>
                        <td class="px-4 py-3 text-gray-700">{{ $detail->holiday }}</td>
                    </tr>
                    @endif
                    @if($shop->area)
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">エリア</th>
                        <td class="px-4 py-3 text-gray-700">
                            <a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => $shop->area->slug, 'job_slug' => 'all']) }}/"
                               class="text-business-700 hover:underline">{{ $shop->area->name }}</a>
                        </td>
                    </tr>
                    @endif
                    @if($shop->address_locality || $shop->address)
                    @php $fullAddress = trim(($shop->address_locality ? $shop->address_locality . ' ' : '') . $shop->address); @endphp
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">住所</th>
                        <td class="px-4 py-3 text-gray-700">
                            <a href="https://maps.google.com/?q={{ urlencode($fullAddress) }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="text-business-700 underline underline-offset-2 hover:no-underline">{{ $fullAddress }}</a>
                            <p class="text-xs text-gray-500 mt-1">※クリックするとGoogleマップに遷移します</p>
                        </td>
                    </tr>
                    @endif
                    @if($shop->nearest_station_name)
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">最寄り駅</th>
                        <td class="px-4 py-3 text-gray-700">
                            @if($shop->nearest_line)
                                <span class="text-gray-500">{{ $shop->nearest_line }}</span>
                            @endif
                            {{ $shop->nearest_station_name }}駅
                            @if($shop->nearest_station_walk)
                                <span class="text-gray-500 text-xs">徒歩{{ $shop->nearest_station_walk }}分</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                    @if($shop->tel)
                    <tr class="border-b border-gray-100">
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">電話番号</th>
                        <td class="px-4 py-3 text-gray-700">
                            <a href="tel:{{ $shop->tel }}" class="text-business-700 underline underline-offset-2 hover:no-underline">{{ $shop->tel }}</a>
                            <p class="text-xs text-gray-500 mt-1">※ナイトワークリストを見たとお伝えいただくとスムーズです</p>
                        </td>
                    </tr>
                    @endif
                    @if($shop->line_id)
                    <tr{{ $shop->externalUrls->isNotEmpty() ? ' class="border-b border-gray-100"' : '' }}>
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">LINE ID</th>
                        <td class="px-4 py-3 text-gray-700">{{ $shop->line_id }}</td>
                    </tr>
                    @endif
                    @if($shop->externalUrls->isNotEmpty())
                    <tr>
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap align-middle">リンク</th>
                        <td class="px-4 py-3">
                            @php
                                $urlLabels = ['website'=>'公式サイト','instagram'=>'Instagram','tiktok'=>'TikTok','x'=>'X','line'=>'LINE','youtube'=>'YouTube','other'=>'リンク'];
                            @endphp
                            <div class="flex flex-wrap gap-3">
                                @foreach($shop->externalUrls as $extUrl)
                                <a href="{{ $extUrl->url }}"
                                   target="_blank"
                                   rel="nofollow noopener noreferrer"
                                   class="inline-flex items-center gap-1.5 text-sm text-business-700 hover:underline whitespace-nowrap">
                                    @include('components.sns-icon', ['type' => $extUrl->url_type])
                                    <span>{{ $urlLabels[$extUrl->url_type] ?? 'リンク' }}</span>
                                </a>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @elseif($detail?->website_url)
                    <tr>
                        <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">公式サイト</th>
                        <td class="px-4 py-3">
                            <a href="{{ $detail->website_url }}"
                               target="_blank"
                               rel="nofollow noopener noreferrer"
                               class="text-business-700 hover:underline break-all text-sm">{{ $detail->website_url }}</a>
                        </td>
                    </tr>
                    @endif
                </table>
            </section>

            {{-- 店舗紹介文 --}}
            @if($detail->content)
                <section class="mb-6" aria-label="店舗紹介">
                    <h2 class="font-bold text-gray-700 mb-2 text-sm">店舗紹介</h2>
                    <div class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $detail->content }}</div>
                </section>
            @endif

            {{-- ギャラリー --}}
            @if($detail->image_paths && count($detail->image_paths) > 0)
                <section class="mb-6" aria-label="店内写真">
                    <h2 class="font-bold text-gray-700 mb-2 text-sm">店内写真</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($detail->image_paths as $i => $imgPath)
                            <img src="{{ asset('storage/' . $imgPath) }}"
                                 alt="{{ $shop->name }} 店内写真{{ $i + 1 }}"
                                 class="w-full h-32 object-cover rounded-lg"
                                 loading="lazy" decoding="async">
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- よくある質問 --}}
            @if(!empty($detail->faq))
                <section class="mb-6 border-t border-gray-100 pt-6" aria-label="よくある質問">
                    <h2 class="font-bold text-gray-700 mb-3 text-sm">よくある質問</h2>
                    <dl class="space-y-3">
                        @foreach($detail->faq as $item)
                        <div class="bg-gray-50 rounded-lg px-4 py-3">
                            <dt class="font-bold text-gray-800 text-sm flex items-start gap-2 mb-1">
                                <span class="shrink-0 font-black text-gray-400">Q.</span>{{ $item['q'] }}
                            </dt>
                            <dd class="text-sm text-gray-600 leading-relaxed flex items-start gap-2">
                                <span class="shrink-0 font-bold text-gray-400">A.</span>{{ $item['a'] }}
                            </dd>
                        </div>
                        @endforeach
                    </dl>
                </section>
            @endif

            {{-- この店舗の求人情報 --}}
            @php
                $castJobs  = $shop->jobs->filter(fn($j) => in_array($j->search_group, ['female', 'both']));
                $staffJobs = $shop->jobs->filter(fn($j) => $j->search_group === 'male');
            @endphp
            @if($shop->jobs->isNotEmpty())
                <section class="mb-6 border-t border-gray-100 pt-6" aria-label="求人情報">
                    <h2 class="font-bold text-gray-700 mb-3 text-sm">この店舗の求人情報</h2>
                    <div class="space-y-2">
                        @foreach($castJobs as $job)
                            <a href="{{ route('job.show', $job->id) }}/"
                               class="flex items-center justify-between p-3 rounded-lg border border-female-100 bg-female-50 hover:bg-female-100 transition group">
                                <div class="min-w-0">
                                    <span class="text-xs text-female-500 font-medium">
                                        {{ $job->jobType?->name ?? 'キャスト' }}
                                    </span>
                                    <p class="text-sm font-bold text-gray-800 truncate">{{ $job->title }}</p>
                                    @if($job->hourly_wage_min)
                                        <p class="text-xs text-female-600 mt-0.5">
                                            {{ ['hourly'=>'時給','daily'=>'日給','monthly'=>'月給'][$job->wage_type ?? 'hourly'] }}
                                            {{ number_format($job->hourly_wage_min) }}円〜
                                        </p>
                                    @endif
                                </div>
                                <span class="text-female-400 ml-3 shrink-0 group-hover:translate-x-0.5 transition-transform">›</span>
                            </a>
                        @endforeach
                        @foreach($staffJobs as $job)
                            <a href="{{ route('job.show', $job->id) }}/"
                               class="flex items-center justify-between p-3 rounded-lg border border-male-200 bg-male-50 hover:bg-male-100 transition group">
                                <div class="min-w-0">
                                    <span class="text-xs text-male-500 font-medium">
                                        {{ $job->jobType?->name ?? 'スタッフ' }}
                                    </span>
                                    <p class="text-sm font-bold text-gray-800 truncate">{{ $job->title }}</p>
                                    @if($job->hourly_wage_min)
                                        <p class="text-xs text-male-600 mt-0.5">
                                            {{ ['hourly'=>'時給','daily'=>'日給','monthly'=>'月給'][$job->wage_type ?? 'hourly'] }}
                                            {{ number_format($job->hourly_wage_min) }}円〜
                                        </p>
                                    @endif
                                </div>
                                <span class="text-male-400 ml-3 shrink-0 group-hover:translate-x-0.5 transition-transform">›</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ホットリンクボタン --}}
            @if($detail->is_hotlink && $detail->hotlink_url)
                <a href="{{ url('/track/shop/' . $shop->id) . '/' }}"
                   target="_blank" rel="noopener noreferrer nofollow"
                   class="bg-business-700 hover:bg-business-600 text-white font-bold py-4 px-8 rounded-xl text-center block w-full text-lg transition">
                    公式サイトを見る
                </a>
            @endif

        </div>
    </article>

    {{-- 同業種・同エリアの関連店舗 --}}
    @if($relatedShops->isNotEmpty())
    <section class="mt-8" aria-label="関連店舗">
        <h2 class="text-sm font-bold text-gray-600 mb-3">{{ $shop->genre?->name ?? '同業種' }}の他店舗（{{ $shop->area?->name }}）</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($relatedShops as $relShop)
            <a href="{{ url('/track/shop/' . $relShop->id) }}/"
               rel="nofollow"
               class="flex items-center gap-3 bg-white rounded-xl border border-gray-200 p-3 hover:border-business-300 hover:shadow-sm transition group">
                @if($relShop->main_image)
                    <img src="{{ asset('storage/' . \App\Services\ImageService::webpPath($relShop->main_image)) }}"
                         alt="{{ $relShop->name }}"
                         class="w-14 h-14 rounded-lg object-cover shrink-0">
                @else
                    <div class="w-14 h-14 rounded-lg bg-gray-100 shrink-0 flex items-center justify-center text-gray-300 text-xl">🏮</div>
                @endif
                <div class="min-w-0">
                    <p class="text-xs text-business-600 font-medium">{{ $relShop->genre?->name }}</p>
                    <p class="text-sm font-bold text-gray-800 truncate">{{ $relShop->name }}</p>
                    <p class="text-xs text-gray-400">{{ $relShop->area?->name }}</p>
                </div>
                <span class="text-gray-300 ml-auto shrink-0 group-hover:text-business-500 transition">›</span>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- 最近見た店舗 --}}
    <div x-data="recentlyViewedShops()" x-init="init()">
        <template x-if="items.length > 0">
            <section class="mt-8" aria-label="最近見た店舗">
                <h2 class="text-sm font-bold text-gray-600 mb-3">最近見た店舗</h2>
                <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                    <template x-for="item in items" :key="item.id">
                        <a :href="item.url"
                           class="shrink-0 w-32 bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-sm transition group">
                            <div class="h-20 bg-gray-100 overflow-hidden">
                                <template x-if="item.img">
                                    <img :src="item.img" :alt="item.name"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                </template>
                                <template x-if="!item.img">
                                    <div class="w-full h-full flex items-center justify-center text-gray-300 text-2xl" aria-hidden="true">🏮</div>
                                </template>
                            </div>
                            <div class="p-2">
                                <p class="text-xs text-gray-700 font-medium line-clamp-2 leading-tight" x-text="item.name"></p>
                            </div>
                        </a>
                    </template>
                </div>
            </section>
        </template>
    </div>

    {{-- 戻るリンク --}}
    <div class="mt-6 text-center">
        <a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => 'all', 'job_slug' => 'all']) }}/" class="text-sm text-gray-400 hover:text-gray-600">
            ← 夜遊びスポット一覧に戻る
        </a>
    </div>

    @include('partials._promo-banner', ['wrapClass' => 'my-8 rounded-2xl overflow-hidden shadow-md', 'innerClass' => ''])

    {{-- 通報フォーム --}}
    @include('partials._report-form', ['reportTargetType' => 'shop', 'reportTargetId' => $shop->id])
</div>

@endsection

@push('scripts')
<script @nonce>
function recentlyViewedShops() {
    const KEY = 'nw_recent_shops_business';
    const MAX = 8;
    const currentId = {{ $shop->id }};
    const currentData = {
        id:   currentId,
        name: @json($shop->name),
        url:  '{{ route('shop.show', $shop->id) }}/',
        img:  @json($shop->main_image ? asset('storage/' . \App\Services\ImageService::webpPath($shop->main_image)) : null),
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
