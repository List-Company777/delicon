@extends('layouts.app')

@section('title', 'ナイトワーク求人・夜遊び情報')
@section('canonical', route('top') . '/')
@section('description', 'ナイトワーク求人・夜遊びスポット情報を掲載。キャバクラ・ホスト・ガールズバーなどの求人をエリア・職種から簡単検索。全国のナイトワーク情報はナイトワークリスト。')

@push('head')
@php
$ldTop = [
    '@context' => 'https://schema.org',
    '@type'    => 'WebPage',
    '@id'      => route('top') . '/#webpage',
    'url'      => route('top') . '/',
    'name'     => 'ナイトワークリスト | キャバクラ・ホスト・ガールズバーの求人・夜遊び情報',
    'inLanguage'  => 'ja',
    'description' => 'キャバクラ・ホスト・ガールズバーの求人・夜遊び情報を掲載。エリア・職種から簡単検索。',
    'isPartOf'    => ['@id' => url('/') . '#website'],
    'about'       => ['@id' => url('/') . '#org'],
    'publisher'   => ['@id' => url('/') . '#org'],
];
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($ldTop, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')

{{-- ヒーロー：年齢確認と検索グループ --}}
<section class="bg-gray-900 text-white py-10 md:py-16">
    <div class="max-w-6xl mx-auto px-4">

        {{-- タイトル --}}
        <div class="text-center mb-8">
            <h1 class="text-2xl md:text-4xl font-bold mb-2">
                ナイトワーク<span class="text-business-300">求人</span>・夜遊び情報
            </h1>
            <p class="text-gray-400 text-sm md:text-base">
                ナイトワークリスト｜キャバクラ・ホスト・ガールズバーをエリア・職種から検索
            </p>
            <p class="text-gray-500 text-xs mt-2">
                ※本サイトは18歳以上の方を対象としています
            </p>
        </div>

        {{-- 3グループの検索ボックス --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- 夜遊びリスト（mobile:1位、desktop:1位） --}}
            <div class="order-1 md:order-1 rounded-xl p-5 border border-business-700/40 bg-business-700/10">
                <h2 class="flex items-center gap-2 mb-4 text-business-300 font-bold text-lg">
                    <span class="w-3 h-3 rounded-full bg-business-300 shrink-0" aria-hidden="true"></span>
                    夜遊びリスト<span class="text-sm font-normal ml-1 opacity-75">（ナイトスポット情報）</span>
                </h2>
                <p class="text-gray-400 text-xs mb-4">夜遊びスポット情報・セット料金を検索</p>
                <form action="{{ route('search') }}" method="GET">
                    <input type="hidden" name="gender" value="business">
                    <div class="space-y-2">
                        <label class="sr-only" for="yoasobi-area">エリア・駅名</label>
                        <input id="yoasobi-area" type="text" name="area"
                               placeholder="エリア・駅名（例：新宿）"
                               class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-business-300">
                        <label class="sr-only" for="yoasobi-keyword">業種・店名</label>
                        <input id="yoasobi-keyword" type="text" name="keyword"
                               placeholder="業種・店名（例：キャバクラ）"
                               class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-business-300">
                        <button type="submit"
                                class="w-full bg-business-700 hover:bg-business-600 text-white font-bold py-2 rounded-lg text-sm transition">
                            夜遊び情報を検索
                        </button>
                    </div>
                </form>
            </div>

            {{-- 男性ナイトワーク（mobile:3位、desktop:2位） --}}
            <div class="order-3 md:order-2 rounded-xl p-5 border border-male-300/30 bg-male-800/30">
                <h2 class="flex items-center gap-2 mb-4 text-male-300 font-bold text-lg">
                    <span class="w-3 h-3 rounded-full bg-male-300 shrink-0" aria-hidden="true"></span>
                    男性ナイトワーク
                </h2>
                <p class="text-gray-400 text-xs mb-4">ホスト・黒服・ボーイなどの男性ナイトワーク</p>
                <form action="{{ route('search') }}" method="GET">
                    <input type="hidden" name="gender" value="male">
                    <div class="space-y-2">
                        <label class="sr-only" for="male-area">エリア・駅名</label>
                        <input id="male-area" type="text" name="area"
                               placeholder="エリア・駅名（例：歌舞伎町）"
                               class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-male-300">
                        <label class="sr-only" for="male-keyword">職種・業種</label>
                        <input id="male-keyword" type="text" name="keyword"
                               placeholder="職種・業種（例：黒服、キャバクラ）"
                               class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-male-300">
                        <button type="submit"
                                class="w-full bg-male-600 hover:bg-male-700 text-white font-bold py-2 rounded-lg text-sm transition">
                            男性ナイトワークを検索
                        </button>
                    </div>
                </form>
            </div>

            {{-- 女性ナイトワーク（mobile:2位、desktop:3位） --}}
            <div class="order-2 md:order-3 rounded-xl p-5 border border-female-500/40 bg-female-600/10">
                <h2 class="flex items-center gap-2 mb-4 text-female-400 font-bold text-lg">
                    <span class="w-3 h-3 rounded-full bg-female-500 shrink-0" aria-hidden="true"></span>
                    女性ナイトワーク
                </h2>
                <p class="text-gray-400 text-xs mb-4">キャスト・ガールズバーなどの女性ナイトワーク</p>
                <form action="{{ route('search') }}" method="GET">
                    <input type="hidden" name="gender" value="female">
                    <div class="space-y-2">
                        <label class="sr-only" for="female-area">エリア・駅名</label>
                        <input id="female-area" type="text" name="area"
                               placeholder="エリア・駅名（例：池袋）"
                               class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-female-400">
                        <label class="sr-only" for="female-keyword">職種・業種</label>
                        <input id="female-keyword" type="text" name="keyword"
                               placeholder="職種・業種（例：キャスト、ガールズバー）"
                               class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-female-400">
                        <button type="submit"
                                class="w-full bg-female-600 hover:bg-female-500 text-white font-bold py-2 rounded-lg text-sm transition">
                            女性ナイトワークを検索
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</section>

{{-- 人気エリアのクイックリンク --}}
<section class="max-w-6xl mx-auto px-4 py-10">
    <h2 class="text-lg font-bold text-gray-700 mb-5">人気エリアから探す</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- 夜遊びリスト --}}
        <nav aria-label="夜遊びリスト エリア別">
            <h3 class="text-sm font-bold text-business-700 border-b-2 border-business-300 pb-1 mb-3">
                夜遊びリスト（ナイトスポット情報）
            </h3>
            <div class="flex flex-wrap gap-2">
                @forelse($popularAreas->get('yoasobi', collect())->take(15) as $row)
                <a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => $row->area_slug, 'job_slug' => 'all']) }}/"
                   class="px-3 py-1 bg-business-50 border border-business-300 text-business-700 rounded-full text-xs hover:bg-business-100 transition">
                    {{ $row->area_name }}
                </a>
                @empty
                @foreach(['shinjuku' => '新宿', 'ikebukuro' => '池袋', 'shibuya' => '渋谷', 'roppongi' => '六本木', 'ginza' => '銀座', 'nakasu' => '中洲'] as $slug => $name)
                <a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => $slug, 'job_slug' => 'all']) }}/"
                   class="px-3 py-1 bg-business-50 border border-business-300 text-business-700 rounded-full text-xs hover:bg-business-100 transition">
                    {{ $name }}
                </a>
                @endforeach
                @endforelse
            </div>
        </nav>

        {{-- 男性向け --}}
        <nav aria-label="男性ナイトワーク エリア別">
            <h3 class="text-sm font-bold text-male-600 border-b-2 border-male-300 pb-1 mb-3">
                男性ナイトワーク
            </h3>
            <div class="flex flex-wrap gap-2">
                @forelse($popularAreas->get('male', collect())->take(15) as $row)
                <a href="{{ route('search.directory', ['gender' => 'male', 'area_slug' => $row->area_slug, 'job_slug' => 'all']) }}/"
                   class="px-3 py-1 bg-male-50 border border-male-300 text-male-600 rounded-full text-xs hover:bg-male-100 transition">
                    {{ $row->area_name }}
                </a>
                @empty
                @foreach(['shinjuku' => '新宿', 'ikebukuro' => '池袋', 'shibuya' => '渋谷', 'roppongi' => '六本木', 'ginza' => '銀座', 'namba' => '難波'] as $slug => $name)
                <a href="{{ route('search.directory', ['gender' => 'male', 'area_slug' => $slug, 'job_slug' => 'all']) }}/"
                   class="px-3 py-1 bg-male-50 border border-male-300 text-male-600 rounded-full text-xs hover:bg-male-100 transition">
                    {{ $name }}
                </a>
                @endforeach
                @endforelse
            </div>
        </nav>

        {{-- 女性向け --}}
        <nav aria-label="女性ナイトワーク エリア別">
            <h3 class="text-sm font-bold text-female-600 border-b-2 border-female-400 pb-1 mb-3">
                女性ナイトワーク
            </h3>
            <div class="flex flex-wrap gap-2">
                @forelse($popularAreas->get('female', collect())->take(15) as $row)
                <a href="{{ route('search.directory', ['gender' => 'female', 'area_slug' => $row->area_slug, 'job_slug' => 'all']) }}/"
                   class="px-3 py-1 bg-female-50 border border-female-100 text-female-600 rounded-full text-xs hover:bg-female-100 transition">
                    {{ $row->area_name }}
                </a>
                @empty
                @foreach(['shinjuku' => '新宿', 'ikebukuro' => '池袋', 'shibuya' => '渋谷', 'roppongi' => '六本木', 'ginza' => '銀座', 'namba' => '難波'] as $slug => $name)
                <a href="{{ route('search.directory', ['gender' => 'female', 'area_slug' => $slug, 'job_slug' => 'all']) }}/"
                   class="px-3 py-1 bg-female-50 border border-female-100 text-female-600 rounded-full text-xs hover:bg-female-100 transition">
                    {{ $name }}
                </a>
                @endforeach
                @endforelse
            </div>
        </nav>

    </div>
</section>

{{-- 最近見た求人・店舗 --}}
<div x-data="recentlyViewedTop()" x-init="init()">
    <template x-if="items.length > 0">
        <section class="max-w-6xl mx-auto px-4 py-8 border-t border-gray-100" aria-label="最近見た求人・店舗">
            <h2 class="text-sm font-bold text-gray-500 mb-3">最近見た求人・店舗</h2>
            <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                <template x-for="item in items" :key="item.id + item.kind">
                    <a :href="item.url"
                       class="shrink-0 w-36 bg-white border border-gray-200 rounded-xl p-3 hover:shadow-sm transition">
                        <p class="text-xs mb-1"
                           :class="item.kind === 'shop' ? 'text-business-600' : (item.group === 'male' ? 'text-male-600' : 'text-female-500')"
                           x-text="item.type"></p>
                        <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-tight mb-1" x-text="item.title"></p>
                        <p class="text-xs text-gray-400 truncate" x-text="item.shop || item.name"></p>
                    </a>
                </template>
            </div>
        </section>
    </template>
</div>

{{-- みんなの検索ワード --}}
@if($popularKeywords->count())
<section class="bg-gray-100 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-sm font-bold text-gray-500 mb-3">よく検索されているキーワード</h2>
        <div class="flex flex-wrap gap-2">
            @foreach($popularKeywords as $kw)
            @php
                $kwStyle = match($kw->gender) {
                    'female'   => 'bg-female-50 border-female-100 text-female-600 hover:bg-female-100',
                    'male'     => 'bg-male-50 border-male-300 text-male-600 hover:bg-male-100',
                    'business' => 'bg-business-50 border-business-300 text-business-700 hover:bg-business-100',
                    default    => 'bg-white border-gray-300 text-gray-600 hover:bg-gray-100',
                };
            @endphp
            <a href="{{ $kw->directory_url ?? route('search', ['gender' => $kw->gender, 'keyword' => $kw->keyword]) }}"
               class="px-3 py-1 border rounded-full text-xs transition {{ $kwStyle }}">
                {{ $kw->keyword }}
                <span class="opacity-60 ml-1">{{ number_format($kw->search_count) }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@include('partials._promo-banner', ['wrapClass' => 'mt-2'])

@endsection

@push('scripts')
<script @nonce>
function recentlyViewedTop() {
    return {
        items: [],
        init() {
            const buckets = [
                { key: 'nw_recent_jobs_female',   kind: 'job',  group: 'female' },
                { key: 'nw_recent_jobs_male',      kind: 'job',  group: 'male'   },
                { key: 'nw_recent_shops_business', kind: 'shop', group: 'both'   },
            ].map(b => {
                let list = [];
                try { list = JSON.parse(localStorage.getItem(b.key) || '[]'); } catch(e) {}
                return { ...b, list };
            });

            // ラウンドロビン: 各バケットから1件ずつ交互に取り出す
            const result = [];
            const max = Math.max(...buckets.map(b => b.list.length));
            for (let i = 0; i < max && result.length < 9; i++) {
                buckets.forEach(b => {
                    if (b.list[i] && result.length < 9) {
                        result.push({ ...b.list[i], kind: b.kind, group: b.group });
                    }
                });
            }
            this.items = result;
        }
    };
}
</script>
@endpush
