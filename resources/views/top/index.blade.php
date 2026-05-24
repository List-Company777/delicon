@extends('layouts.app')
@section('title', 'デリヘル情報サイト｜全国のデリヘル店・キャスト')
@section('description', '全国のデリヘル情報を掲載。デリヘル店のシステム・料金・在籍キャストのプロフィールが検索できる総合情報サイト「デリヘルリスト」。新着キャスト情報も随時更新中。')
@section('canonical', route('top') . '/')
@section('robots', 'index, follow')
@section('og_type', 'website')

@push('head')
@php
$ldWebsite = ['@context'=>'https://schema.org','@type'=>'WebSite','@id'=>url('/').'#website','url'=>url('/').'/',
    'name'=>'デリヘルリスト','description'=>'全国のデリヘル情報サイト','inLanguage'=>'ja',
    'potentialAction'=>['@type'=>'SearchAction','target'=>['@type'=>'EntryPoint','urlTemplate'=>url('/shops/').'?q={search_term_string}'],'query-input'=>'required name=search_term_string']];
$ldPage = ['@context'=>'https://schema.org','@type'=>'WebPage','@id'=>url('/').'#webpage','url'=>url('/').'/',
    'name'=>'デリヘル情報サイト｜全国のデリヘル店・キャスト - デリヘルリスト','inLanguage'=>'ja',
    'description'=>'全国のデリヘル情報を掲載。デリヘル店のシステム・料金・在籍キャスト情報が検索できる総合情報サイト。',
    'isPartOf'=>['@id'=>url('/').'#website']];
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($ldWebsite, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG) !!}</script>
<script type="application/ld+json" @nonce>{!! json_encode($ldPage, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG) !!}</script>
@endpush

@section('content')

{{-- ヒーロー --}}
<section class="relative bg-surface-800 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-deli-900/60 via-transparent to-transparent pointer-events-none"></div>
    <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-deli-900/20 to-transparent pointer-events-none"></div>
    <div class="relative max-w-5xl mx-auto px-4 py-16 md:py-24">
        <p class="text-gold-400 text-xs tracking-[0.3em] uppercase mb-4">Japan's Delivery Health Information</p>
        <h1 class="text-3xl md:text-5xl font-bold mb-4 tracking-tight leading-tight text-[#F0ECE4]">
            全国<span class="text-deli-400">デリヘル</span>情報サイト
        </h1>
        <p class="text-[#A0A0B8] text-base md:text-lg mb-2 max-w-xl">
            デリヘル店のシステム・料金・在籍キャストを詳しく掲載
        </p>
        <p class="text-[#8A8A9E] text-sm mb-6">デリヘルリスト｜デリヘル・風俗総合情報</p>
        <p class="text-sm font-semibold text-deli-400 mb-6 border border-deli-500/40 inline-block px-4 py-1.5 rounded-full">⚠ 本サイトは18歳以上の方を対象としています</p>

        <form action="{{ url('/all/girl-list/') }}/" method="get"
              class="mt-6 flex gap-2 max-w-md">
            <input type="text" name="q" placeholder="キャスト名で検索..."
                   class="flex-1 bg-surface-600/80 border border-surface-400 focus:border-deli-400 rounded-xl px-4 py-3 text-sm text-[#E8E4DC] placeholder-[#6A6A7E] focus:outline-none transition">
            <button type="submit"
                    class="bg-deli-500 hover:bg-deli-400 text-white px-5 py-3 rounded-xl text-sm font-bold transition whitespace-nowrap">
                検索
            </button>
        </form>

        @if($prefectures->isNotEmpty())
        @php
            $heroRegions = [
                '北海道・東北' => ['hokkaido','aomori','iwate','miyagi','akita','yamagata','fukushima'],
                '関東'        => ['tokyo','kanagawa','saitama','chiba','ibaraki','tochigi','gunma','yamanashi','nagano'],
                '中部'        => ['aichi','shizuoka','gifu','mie','niigata','toyama','ishikawa','fukui'],
                '関西'        => ['osaka','hyogo','kyoto','nara','shiga','wakayama'],
                '中国・四国'  => ['okayama','hiroshima','yamaguchi','tottori','shimane','tokushima','kagawa','ehime','kochi'],
                '九州・沖縄'  => ['fukuoka','saga','nagasaki','kumamoto','oita','miyazaki','kagoshima','okinawa'],
            ];
            $heroPrefMap = $prefectures->keyBy('slug');
        @endphp
        <h2 class="text-sm font-bold text-[#C8C4BC] tracking-wide mb-4">エリアから探す</h2>
            @foreach($heroRegions as $regionName => $slugs)
            <div class="mb-3">
                <p class="text-xs text-[#8A8A9E] mb-1.5">{{ $regionName }}</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($slugs as $slug)
                    @if($heroPrefMap->has($slug))
                    <a href="{{ route('area.top', ['area_slug' => $slug]) }}/"
                       class="bg-surface-600/80 border border-surface-400 hover:border-deli-400 hover:text-deli-400 text-[#C8C4BC] text-xs px-3 py-1 rounded-full transition">
                        {{ $heroPrefMap[$slug]->prefecture }}
                    </a>
                    @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        @endif
    </div>
</section>

{{-- 業種クイックリンク --}}
@if($shopTypes->isNotEmpty())
<nav class="bg-surface-600 border-b border-surface-400" aria-label="デリヘル業種別">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex gap-1 overflow-x-auto py-2">
            <a href="{{ route('shop.list', ['area_slug' => 'all']) }}/"
               class="flex-shrink-0 bg-deli-500 text-white text-xs font-medium px-4 py-1.5 rounded-full whitespace-nowrap">
                すべて
            </a>
            @foreach($shopTypes as $type)
            <a href="{{ $type->slug ? route('shop.list.filter', ['area_slug' => 'all', 'filter_slug' => $type->slug]).'/' : route('shop.list', ['area_slug' => 'all']).'/' }}"
               class="flex-shrink-0 bg-surface-400 hover:bg-deli-500 text-[#B0AEAD] hover:text-white text-xs px-4 py-1.5 rounded-full transition whitespace-nowrap">
                {{ $type->name }}
            </a>
            @endforeach
        </div>
    </div>
</nav>
@endif

{{-- おすすめデリヘル店舗 --}}
<section class="max-w-6xl mx-auto px-4 py-12">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl md:text-2xl font-bold text-[#F0ECE4] flex items-center gap-3">
            <span aria-hidden="true" class="w-1 h-6 bg-deli-500 rounded-full inline-block"></span>
            おすすめデリヘル店舗
        </h2>
        <a href="{{ route('shop.list', ['area_slug' => 'all']) }}/" class="text-sm text-gold-400 hover:text-gold-300 transition">すべて見る →</a>
    </div>
    @if($recommendedShops->isNotEmpty())
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($recommendedShops as $shop)
        <a href="{{ route('shop.show', $shop->id) }}/"
           class="bg-surface-500 border border-surface-300 hover:border-deli-500 rounded-xl overflow-hidden transition group">
            <div class="relative aspect-[5/2] bg-gradient-to-br from-surface-400 to-surface-600 overflow-hidden">
                @if($shop->shop_image_url)
                <img src="{{ $shop->shop_image_url }}"
                     alt="{{ $shop->name }}のデリヘル情報"
                     loading="lazy"
                     class="img-onerror-hide absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-300 opacity-90 group-hover:opacity-100">
                @else
                <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-gold-400 text-2xl opacity-30">✦</span>
                @endif
                <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-t from-surface-900/80 via-transparent to-transparent"></div>
                @if($shop->shop_type_name ?? null)
                <span class="absolute top-2 left-2 z-10 bg-deli-500/90 text-white text-xs px-2 py-0.5 rounded-full">{{ $shop->shop_type_name }}</span>
                @endif
                @if($shop->shop_image_url)
                <p aria-hidden="true" class="absolute bottom-2 left-2 right-2 z-10 text-[#E8E4DC] text-xs font-bold line-clamp-1 drop-shadow-md">{{ $shop->name }}</p>
                @endif
            </div>
            <div class="p-3">
                <h3 class="font-bold text-sm text-[#E8E4DC] group-hover:text-gold-400 transition line-clamp-1">
                    {{ $shop->name }}
                </h3>
                @if($shop->catche)
                <p class="text-xs text-[#B0AEAD] mt-0.5 line-clamp-2">{{ $shop->catche }}</p>
                @endif
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-[#8A8A9E]">在籍{{ $shop->cast_count ?? 0 }}名</span>
                    @if($shop->price_60)
                    <span class="text-gold-400 font-medium">60分¥{{ number_format($shop->price_60) }}〜</span>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif
    <div class="mt-8 text-center">
        <a href="{{ route('shop.list', ['area_slug' => 'all']) }}/"
           class="inline-block border border-deli-500 text-deli-400 hover:bg-deli-500 hover:text-white font-bold px-10 py-3 rounded-lg transition">
            デリヘル店舗をもっと見る
        </a>
    </div>
</section>

{{-- 新着デリヘルキャスト --}}
<section class="bg-surface-600 border-y border-surface-400 py-12">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-[#F0ECE4] flex items-center gap-3">
                <span aria-hidden="true" class="w-1 h-6 bg-deli-400 rounded-full inline-block"></span>
                新着デリヘルキャスト
            </h2>
            <a href="{{ route('girl.list', ['area_slug' => 'all']) }}/" class="text-sm text-gold-400 hover:text-gold-300 transition">すべて見る →</a>
        </div>
        @if($newCasts->isNotEmpty())
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
            @foreach($newCasts as $cast)
            @php $castImg = $cast->img_url; @endphp
            <a href="{{ route('cast.show', $cast->id) }}/"
               class="group">
                <div class="aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 mb-2 border border-surface-300 group-hover:border-deli-500 transition">
                    <img src="{{ $castImg }}"
                         alt="{{ $cast->name }}のキャスト情報"
                         loading="lazy"
                         class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition duration-300 opacity-90 group-hover:opacity-100">
                </div>
                <p class="font-medium text-xs text-[#D8D4CC] group-hover:text-gold-400 transition truncate">{{ $cast->name }}</p>
                <p class="text-xs text-[#8A8A9E] mt-0.5">
                    {{ $cast->age ? $cast->age . '歳' : '' }}{{ ($cast->age && $cast->cup) ? ' · ' : '' }}{{ $cast->cup ? $cast->cup . 'カップ' : '' }}
                </p>
                @if($cast->shop_name)<p class="text-[10px] text-[#6A6A7E] truncate">{{ $cast->shop_name }}</p>@endif
            </a>
            @endforeach
        </div>
        <div class="mt-8 text-center">
            <a href="{{ route('girl.list', ['area_slug' => 'all']) }}/"
               class="inline-block border border-deli-400 text-deli-400 hover:bg-deli-500 hover:border-deli-500 hover:text-white font-bold px-10 py-3 rounded-lg transition">
                キャストをもっと見る
            </a>
        </div>
        @endif
    </div>
</section>

{{-- あなたにおすすめ（ログイン中かつ好み設定あり） --}}
@auth
@if($recommendations->isNotEmpty())
<section class="bg-surface-700 py-12">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-[#F0ECE4] flex items-center gap-3">
                <span aria-hidden="true" class="w-1 h-6 bg-gold-500 rounded-full inline-block"></span>
                あなたにおすすめ
            </h2>
            <a href="{{ route('user.settings') }}/" class="text-xs text-[#8A8A9E] hover:text-deli-400 transition">設定を変更</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
            @foreach($recommendations as $cast)
            <a href="{{ route('cast.show', $cast->id) }}/" class="group text-center">
                <div class="relative aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 mb-1.5 border border-surface-300 group-hover:border-gold-500 transition">
                    <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                         class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
                    @if($cast->working_date && $cast->working_date->isToday())
                    <span class="absolute top-1 left-1 text-[9px] font-bold bg-emerald-500 text-white px-1.5 py-0.5 rounded-full">本日出勤</span>
                    @endif
                    @if($cast->isNew())
                    <span class="absolute top-1 right-1 text-[9px] font-bold bg-gold-500 text-white px-1.5 py-0.5 rounded-full">NEW</span>
                    @endif
                </div>
                <p class="text-xs font-medium text-[#D8D4CC] group-hover:text-gold-400 truncate">{{ $cast->name }}</p>
                @if($cast->castType)<p class="text-[10px] text-deli-400 truncate">{{ $cast->castType->name }}</p>@endif
                @if($cast->shop)<p class="text-[10px] text-[#8A8A9E] truncate">{{ $cast->shop->name }}</p>@endif
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@endauth

{{-- 本日の出勤 --}}
@if($workingToday->isNotEmpty())
<section class="bg-surface-700 py-12">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-xl md:text-2xl font-bold text-[#F0ECE4] mb-6 flex items-center gap-3">
            <span aria-hidden="true" class="w-1 h-6 bg-deli-500 rounded-full inline-block"></span>
            本日の出勤
            <span class="text-sm font-normal text-deli-400 ml-1">{{ today()->format('m月d日') }}</span>
        </h2>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
            @foreach($workingToday as $cast)
            @php $castImg = $cast->img_url; @endphp
            <a href="{{ route('cast.show', $cast->id) }}/"
               class="group">
                <div class="relative aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 mb-2 border border-surface-300 group-hover:border-deli-500 transition">
                    <img src="{{ $castImg }}"
                         alt="{{ $cast->name }}のデリヘル出勤情報"
                         loading="lazy"
                         class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition duration-300 opacity-90 group-hover:opacity-100">
                    <span class="absolute top-1.5 left-1.5 bg-deli-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">出勤中</span>
                </div>
                <p class="font-medium text-xs text-[#D8D4CC] group-hover:text-gold-400 transition truncate">{{ $cast->name }}</p>
                <p class="text-xs text-[#8A8A9E] mt-0.5">{{ $cast->shop_name }}</p>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- 新人キャスト --}}
@if($newArrivals->isNotEmpty())
<section class="bg-surface-600 border-y border-surface-400 py-12">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-xl md:text-2xl font-bold text-[#F0ECE4] mb-6 flex items-center gap-3">
            <span aria-hidden="true" class="w-1 h-6 bg-gold-400 rounded-full inline-block"></span>
            新人キャスト
        </h2>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
            @foreach($newArrivals as $cast)
            @php $castImg = $cast->img_url; @endphp
            <a href="{{ route('cast.show', $cast->id) }}/"
               class="group">
                <div class="relative aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 mb-2 border border-surface-300 group-hover:border-gold-400 transition">
                    <img src="{{ $castImg }}"
                         alt="{{ $cast->name }}の新人デリヘルキャスト情報"
                         loading="lazy"
                         class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition duration-300 opacity-90 group-hover:opacity-100">
                    <span class="absolute top-1.5 left-1.5 bg-gold-400 text-surface-800 text-[10px] font-bold px-1.5 py-0.5 rounded">NEW</span>
                </div>
                <p class="font-medium text-xs text-[#D8D4CC] group-hover:text-gold-400 transition truncate">{{ $cast->name }}</p>
                <p class="text-xs text-[#8A8A9E] mt-0.5">{{ $cast->join_date }}</p>
                @if($cast->shop_name)<p class="text-[10px] text-[#6A6A7E] truncate">{{ $cast->shop_name }}</p>@endif
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- 業種から探す --}}
@if($popularKeywords->isNotEmpty())
<section class="max-w-6xl mx-auto px-4 py-12">
    <h2 class="text-xl md:text-2xl font-bold text-[#F0ECE4] mb-6 flex items-center gap-3">
        <span aria-hidden="true" class="w-1 h-6 bg-gold-400 rounded-full inline-block"></span>
        デリヘルの業種から探す
    </h2>
    <div class="flex flex-wrap gap-3">
        @foreach($popularKeywords as $kw)
        <a href="{{ $kw->slug ? route('shop.list.filter', ['area_slug' => 'all', 'filter_slug' => $kw->slug]).'/' : route('shop.list', ['area_slug' => 'all']).'/' }}"
           class="bg-surface-500 border border-surface-300 hover:border-gold-400 text-[#B0AEAD] hover:text-gold-400 rounded-full px-5 py-2 text-sm transition">
            {{ $kw->name }}
            @if(isset($kw->count) && $kw->count > 0)
            <span class="text-[#8A8A9E] text-xs ml-1">{{ $kw->count }}店</span>
            @endif
        </a>
        @endforeach
    </div>
</section>
@endif

{{-- デリヘルとは + 統計 --}}
<section class="bg-surface-800 border-t border-surface-400 py-12">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-xl font-bold text-[#F0ECE4] mb-4">デリヘルとは</h2>
        <div class="text-[#9090A4] text-sm leading-7 space-y-3 mb-8">
            <p>デリヘル（デリバリーヘルス）とは、派遣型の風俗サービスの一種で、キャストがお客様の指定する場所（ホテル・自宅など）に出張する形式の店舗です。店舗型ではないため比較的リーズナブルな料金設定が多く、全国各地で多くの店舗が営業しています。</p>
            <p>デリヘルリストでは、全国のデリヘル・風俗店情報を掲載しています。各店舗のシステム・料金・在籍キャストのプロフィールをまとめて確認できます。ホテヘル・素人系・人妻・SMなど業種ごとに絞り込み検索も可能です。</p>
        </div>

        <dl class="grid grid-cols-3 gap-4">
            <div class="bg-surface-600 border border-surface-400 rounded-xl p-5 text-center">
                <dd class="text-2xl font-bold text-deli-400 mb-1">{{ number_format(\App\Models\Shop::where('status','active')->count()) }}<span class="text-base">店</span></dd>
                <dt class="text-xs text-[#7A7A8E]">掲載中の店舗数</dt>
            </div>
            <div class="bg-surface-600 border border-surface-400 rounded-xl p-5 text-center">
                <dd class="text-2xl font-bold text-deli-400 mb-1">{{ number_format(\App\Models\Cast::where('status','active')->count()) }}<span class="text-base">名</span></dd>
                <dt class="text-xs text-[#7A7A8E]">在籍キャスト数</dt>
            </div>
            <div class="bg-surface-600 border border-surface-400 rounded-xl p-5 text-center">
                <dd class="text-2xl font-bold text-gold-400 mb-1">47</dd>
                <dt class="text-xs text-[#7A7A8E]">都道府県対応</dt>
            </div>
        </dl>
    </div>
</section>

{{-- よくある質問 --}}
<section class="bg-surface-700 border-t border-surface-400 py-12">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-xl font-bold text-[#F0ECE4] mb-6">よくある質問</h2>
        <div class="space-y-3">
            <details class="bg-surface-600 border border-surface-400 hover:border-surface-200 rounded-xl p-5 group transition">
                <summary class="font-medium text-[#D8D4CC] cursor-pointer list-none flex justify-between items-center gap-3">
                    デリヘルの料金相場はどのくらいですか？
                    <span aria-hidden="true" class="text-[#8A8A9E] group-open:rotate-180 transition-transform text-sm shrink-0">▼</span>
                </summary>
                <p class="mt-3 text-sm text-[#9090A4] leading-7">デリヘルの料金は店舗・地域・コースによって異なりますが、一般的に60分コースで10,000円〜30,000円程度が相場です。素人系・人妻系などの業種や指名料の有無によっても変わります。各店舗の詳細ページでシステム・料金を確認できます。</p>
            </details>
            <details class="bg-surface-600 border border-surface-400 hover:border-surface-200 rounded-xl p-5 group transition">
                <summary class="font-medium text-[#D8D4CC] cursor-pointer list-none flex justify-between items-center gap-3">
                    デリヘルはどんな業種がありますか？
                    <span aria-hidden="true" class="text-[#8A8A9E] group-open:rotate-180 transition-transform text-sm shrink-0">▼</span>
                </summary>
                <p class="mt-3 text-sm text-[#9090A4] leading-7">デリヘルにはホテヘル・素人系・人妻・熟女・SM・ニューハーフ・アロマエステ・イメクラなどさまざまな業種があります。デリヘルリストでは業種ごとに絞り込み検索ができます。</p>
            </details>
            <details class="bg-surface-600 border border-surface-400 hover:border-surface-200 rounded-xl p-5 group transition">
                <summary class="font-medium text-[#D8D4CC] cursor-pointer list-none flex justify-between items-center gap-3">
                    キャストの情報はどこで確認できますか？
                    <span aria-hidden="true" class="text-[#8A8A9E] group-open:rotate-180 transition-transform text-sm shrink-0">▼</span>
                </summary>
                <p class="mt-3 text-sm text-[#9090A4] leading-7">各店舗の詳細ページに在籍キャスト一覧が掲載されています。また「キャストを探す」ページでは、タイプ・年齢・カップサイズなどの条件でキャストを横断検索することができます。</p>
            </details>
        </div>
    </div>
</section>

@endsection
