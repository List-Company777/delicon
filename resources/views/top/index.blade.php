@extends('layouts.app')

@section('title', 'ナイトワークリスト - キャバクラ・ホスト・ガールズバーの求人・夜遊び情報')
@section('canonical', route('top'))
@section('description', 'キャバクラ・ホスト・ガールズバーの求人・夜遊び情報を掲載。エリア・職種から簡単検索。')

@section('content')

{{-- ヒーロー：年齢確認と検索グループ --}}
<section class="bg-gray-900 text-white py-10 md:py-16">
    <div class="max-w-6xl mx-auto px-4">

        {{-- タイトル --}}
        <div class="text-center mb-8">
            <h1 class="text-2xl md:text-4xl font-bold mb-2">
                ナイトワーク<span class="text-business-300">リスト</span>
            </h1>
            <p class="text-gray-400 text-sm md:text-base">
                キャバクラ・ホスト・ガールズバーの求人・夜遊び情報
            </p>
            <p class="text-gray-500 text-xs mt-2">
                ※本サイトは18歳以上の方を対象としています
            </p>
        </div>

        {{-- 3グループの検索ボックス --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- 夜遊びリスト --}}
            <div class="rounded-xl p-5 border border-business-700/40 bg-business-700/10">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-3 h-3 rounded-full bg-business-300 inline-block"></span>
                    <h2 class="text-business-300 font-bold text-lg">夜遊びリスト</h2>
                </div>
                <p class="text-gray-400 text-xs mb-4">夜遊びスポット情報・セット料金を検索</p>
                <form action="{{ route('search') }}" method="GET">
                    <input type="hidden" name="gender" value="business">
                    <div class="space-y-2">
                        <input type="text" name="area"
                               placeholder="エリア・駅名（例：新宿）"
                               class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-business-300">
                        <input type="text" name="keyword"
                               placeholder="業種・店名（例：キャバクラ）"
                               class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-business-300">
                        <button type="submit"
                                class="w-full bg-business-700 hover:bg-business-600 text-white font-bold py-2 rounded-lg text-sm transition">
                            夜遊び情報を検索
                        </button>
                    </div>
                </form>
            </div>

            {{-- 男性ナイトワーク --}}
            <div class="rounded-xl p-5 border border-male-300/30 bg-male-800/30">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-3 h-3 rounded-full bg-male-300 inline-block"></span>
                    <h2 class="text-male-300 font-bold text-lg">男性ナイトワーク</h2>
                </div>
                <p class="text-gray-400 text-xs mb-4">ホスト・黒服・ボーイなどの男性ナイトワーク</p>
                <form action="{{ route('search') }}" method="GET">
                    <input type="hidden" name="gender" value="male">
                    <div class="space-y-2">
                        <input type="text" name="area"
                               placeholder="エリア・駅名（例：歌舞伎町）"
                               class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-male-300">
                        <input type="text" name="keyword"
                               placeholder="職種・業種（例：黒服、キャバクラ）"
                               class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-male-300">
                        <button type="submit"
                                class="w-full bg-male-600 hover:bg-male-700 text-white font-bold py-2 rounded-lg text-sm transition">
                            男性ナイトワークを検索
                        </button>
                    </div>
                </form>
            </div>

            {{-- 女性ナイトワーク --}}
            <div class="rounded-xl p-5 border border-female-500/40 bg-female-600/10">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-3 h-3 rounded-full bg-female-500 inline-block"></span>
                    <h2 class="text-female-400 font-bold text-lg">女性ナイトワーク</h2>
                </div>
                <p class="text-gray-400 text-xs mb-4">キャスト・ガールズバーなどの女性ナイトワーク</p>
                <form action="{{ route('search') }}" method="GET">
                    <input type="hidden" name="gender" value="female">
                    <div class="space-y-2">
                        <input type="text" name="area"
                               placeholder="エリア・駅名（例：池袋）"
                               class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-female-400">
                        <input type="text" name="keyword"
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
        <div>
            <h3 class="text-sm font-bold text-business-700 border-b-2 border-business-300 pb-1 mb-3">
                夜遊びリスト
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach(['shinjuku' => '新宿', 'ikebukuro' => '池袋', 'shibuya' => '渋谷', 'roppongi' => '六本木', 'ginza' => '銀座', 'nakasu' => '中洲'] as $slug => $name)
                <a href="{{ route('search.directory', ['gender' => 'business', 'area_slug' => $slug, 'job_slug' => 'all']) }}/"
                   class="px-3 py-1 bg-business-50 border border-business-300 text-business-700 rounded-full text-xs hover:bg-business-100 transition">
                    {{ $name }}
                </a>
                @endforeach
            </div>
        </div>

        {{-- 男性向け --}}
        <div>
            <h3 class="text-sm font-bold text-male-600 border-b-2 border-male-300 pb-1 mb-3">
                男性ナイトワーク
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach(['shinjuku' => '新宿', 'ikebukuro' => '池袋', 'shibuya' => '渋谷', 'roppongi' => '六本木', 'ginza' => '銀座', 'namba' => '難波'] as $slug => $name)
                <a href="{{ route('search.directory', ['gender' => 'male', 'area_slug' => $slug, 'job_slug' => 'all']) }}/"
                   class="px-3 py-1 bg-male-50 border border-male-300 text-male-600 rounded-full text-xs hover:bg-male-100 transition">
                    {{ $name }}
                </a>
                @endforeach
            </div>
        </div>

        {{-- 女性向け --}}
        <div>
            <h3 class="text-sm font-bold text-female-600 border-b-2 border-female-400 pb-1 mb-3">
                女性ナイトワーク
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach(['shinjuku' => '新宿', 'ikebukuro' => '池袋', 'shibuya' => '渋谷', 'roppongi' => '六本木', 'ginza' => '銀座', 'namba' => '難波'] as $slug => $name)
                <a href="{{ route('search.directory', ['gender' => 'female', 'area_slug' => $slug, 'job_slug' => 'all']) }}/"
                   class="px-3 py-1 bg-female-50 border border-female-100 text-female-600 rounded-full text-xs hover:bg-female-100 transition">
                    {{ $name }}
                </a>
                @endforeach
            </div>
        </div>

    </div>
</section>

{{-- 最近見た求人・店舗 --}}
<div x-data="recentlyViewedTop()" x-init="init()" x-show="items.length > 0" x-cloak
     class="max-w-6xl mx-auto px-4 py-8 border-t border-gray-100">
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
</div>

{{-- みんなの検索ワード --}}
@if($popularKeywords->count())
<section class="bg-gray-100 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-sm font-bold text-gray-500 mb-3">よく検索されているキーワード</h2>
        <div class="flex flex-wrap gap-2">
            @foreach($popularKeywords as $kw)
            <a href="{{ $kw->directory_url ?? route('search', ['gender' => $kw->gender, 'keyword' => $kw->keyword]) }}"
               class="px-3 py-1 bg-white border border-gray-300 text-gray-600 rounded-full text-xs hover:bg-gray-200 transition">
                {{ $kw->keyword }}
                <span class="text-gray-400 ml-1">{{ number_format($kw->search_count) }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@include('partials._promo-banner', ['wrapClass' => 'mt-2'])

@endsection

@push('scripts')
<script>
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
