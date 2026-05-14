<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'デリヘル・風俗情報') | デリヘルリスト</title>
    <meta name="description" content="@yield('description', '全国のデリヘル・風俗情報を掲載。デリヘル店のシステム・料金・在籍キャストのプロフィールが検索できる総合情報サイト。')">
    @hasSection('canonical')
    <link rel="canonical" href="@yield('canonical')">
    @endif
    @hasSection('robots')
    <meta name="robots" content="@yield('robots')">
    @endif
    <meta property="og:site_name" content="デリヘルリスト">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', 'デリヘル・風俗情報') | デリヘルリスト">
    <meta property="og:description" content="@yield('description', '全国のデリヘル・風俗情報を掲載。デリヘル店のシステム・料金・在籍キャストのプロフィールが検索できる総合情報サイト。')">
    <meta property="og:url" content="@hasSection('canonical')@yield('canonical')@else{{ url()->current() }}@endif">
    <meta property="og:image" content="@yield('ogp_image', asset('android-chrome-192x192.png'))">
    <meta name="twitter:card" content="@yield('twitter_card', 'summary')">
    <meta name="twitter:image" content="@yield('ogp_image', asset('android-chrome-192x192.png'))">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#C02040">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="デリヘルリスト">
    <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
    <link rel="preconnect" href="https://www.google-analytics.com" crossorigin>
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <link rel="dns-prefetch" href="https://www.google-analytics.com">
@vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
    @stack('head')
@php
    $ldSchema = [
        '@context' => 'https://schema.org',
        '@graph'   => [
            [
                '@type'           => 'Organization',
                '@id'             => url('/') . '#org',
                'name'            => 'デリヘルリスト',
                'url'             => url('/') . '/',
                'foundingDate'    => '2026',
                'logo'            => ['@type' => 'ImageObject', '@id' => url('/') . '#logo', 'url' => asset('images/logo.svg'), 'width' => 600, 'height' => 120],
                'parentOrganization' => ['@type' => 'Organization', 'name' => '株式会社リスト', 'url' => 'https://list-company.net/'],
            ],
            [
                '@type'           => 'WebSite',
                '@id'             => url('/') . '#website',
                'url'             => url('/') . '/',
                'name'            => 'デリヘルリスト',
                'inLanguage'      => 'ja',
                'publisher'       => ['@id' => url('/') . '#org'],
                'isAccessibleForFree' => true,
            ],
        ],
    ];
@endphp
    <script type="application/ld+json" @nonce>{!! json_encode($ldSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-F8P5QRNC87" @nonce></script>
    <script @nonce>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-F8P5QRNC87');
    </script>
</head>
<body class="bg-surface-700 text-[#E8E4DC] antialiased">
@php $fa = (isset($footerPrefSlug) && $footerPrefSlug) ? $footerPrefSlug : (request()->route('area_slug') ?? 'all'); @endphp
    <header class="bg-surface-800 border-b border-surface-400 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('top') }}/" class="text-lg font-bold tracking-widest hover:opacity-80 transition shrink-0">
                <span class="text-[#E8E4DC]">デリヘル</span><span class="text-gold-400">リスト</span>
            </a>
            <nav class="hidden md:flex items-center gap-1 text-sm" aria-label="メインナビゲーション">
                <a href="{{ url("/{$fa}/shop-list/") }}/"
                   class="px-3 py-2 rounded text-[#B0AEAD] hover:text-gold-400 hover:bg-surface-600 transition">
                    店舗一覧
                </a>
                <a href="{{ url("/{$fa}/girl-list/") }}/"
                   class="px-3 py-2 rounded text-deli-400 hover:text-deli-300 hover:bg-surface-600 transition">
                    キャスト検索
                </a>
                <a href="{{ route('article.index') }}/"
                   class="px-3 py-2 rounded text-[#B0AEAD] hover:text-gold-400 hover:bg-surface-600 transition">
                    コラム
                </a>
                <a href="{{ route('ranking.index') }}/"
                   class="px-3 py-2 rounded text-[#B0AEAD] hover:text-gold-400 hover:bg-surface-600 transition">
                    ランキング
                </a>
                @auth
                    @if(auth()->user()->role === 'visitor')
                    <a href="{{ route('user.dashboard') }}/"
                       class="ml-2 border border-deli-500/50 hover:border-deli-400 text-deli-400 hover:text-deli-300 rounded px-3 py-2 text-sm transition">
                        マイページ
                    </a>
                    @else
                    <a href="{{ route('manage.dashboard') }}/"
                       class="ml-2 border border-surface-300 hover:border-gold-400 text-[#B0AEAD] hover:text-gold-400 rounded px-3 py-2 text-sm transition">
                        管理画面
                    </a>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}/"
                       class="ml-2 border border-amber-600/50 hover:border-amber-400 text-amber-500 hover:text-amber-400 rounded px-3 py-2 text-sm transition">
                        Admin
                    </a>
                    @endif
                    @endif
                @else
                    <a href="{{ route('visitor.register') }}/"
                       class="ml-2 border border-deli-500/50 hover:border-deli-400 text-deli-400 hover:text-deli-300 rounded px-3 py-2 text-sm transition">
                        会員登録
                    </a>
                    <a href="{{ route('login') }}/"
                       class="ml-2 border border-surface-300 hover:border-gold-400 text-[#B0AEAD] hover:text-gold-400 rounded px-3 py-2 text-sm transition">
                        店舗掲載
                    </a>
                @endauth
            </nav>
            <button class="md:hidden text-[#B0AEAD] hover:text-gold-400" aria-label="メニューを開く" x-data @click="$dispatch('toggle-menu')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
        <div x-data="{ open: false }" @toggle-menu.window="open = !open">
            <template x-if="open">
                <div class="md:hidden bg-surface-800 border-t border-surface-400 px-4 pb-4 pt-2">
                    <a href="{{ url("/{$fa}/shop-list/") }}/" class="block py-2.5 text-[#B0AEAD] hover:text-gold-400 border-b border-surface-400 transition">店舗一覧</a>
                    <a href="{{ url("/{$fa}/girl-list/") }}/" class="block py-2.5 text-deli-400 hover:text-deli-300 border-b border-surface-400 transition">キャスト検索</a>
                    <a href="{{ route('article.index') }}/" class="block py-2.5 text-[#B0AEAD] hover:text-gold-400 border-b border-surface-400 transition">コラム</a>
                    @auth
                        @if(auth()->user()->role === 'visitor')
                        <a href="{{ route('user.dashboard') }}/" class="block py-2.5 text-deli-400 hover:text-deli-300 text-sm transition">マイページ</a>
                        @else
                        <a href="{{ route('manage.dashboard') }}/" class="block py-2.5 text-[#B0AEAD] hover:text-gold-400 text-sm transition">管理画面</a>
                        @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}/" class="block py-2.5 text-amber-500 hover:text-amber-400 text-sm transition">Admin</a>
                        @endif
                        @endif
                    @else
                        <a href="{{ route('visitor.register') }}/" class="block py-2.5 text-deli-400 hover:text-deli-300 text-sm transition">会員登録</a>
                        <a href="{{ route('login') }}/" class="block py-2.5 text-[#B0AEAD] hover:text-gold-400 text-sm transition">店舗掲載</a>
                    @endauth
                </div>
            </template>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-surface-800 border-t border-surface-400 mt-16">
        <div class="max-w-6xl mx-auto px-4 py-10">
            {{-- メインリンクグリッド --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8 text-sm">
                <nav aria-label="店舗を探す">
                    <p class="text-gold-400 font-bold mb-3 text-xs tracking-widest uppercase">店舗を探す</p>
                    <ul class="space-y-2">
                        <li><a href="{{ url("/{$fa}/shop-list/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">デリヘル・風俗一覧</a></li>
                        <li><a href="{{ url("/{$fa}/shop-list/deriheru/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">デリヘル</a></li>
                        <li><a href="{{ url("/{$fa}/shop-list/hoteheru/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">ホテヘル</a></li>
                        <li><a href="{{ url("/{$fa}/shop-list/aroma/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">アロマエステ</a></li>
                        <li><a href="{{ url("/{$fa}/shop-list/seikan/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">性感・回春</a></li>
                        <li><a href="{{ url("/{$fa}/shop-list/machiawase/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">待ち合わせ型</a></li>
                    </ul>
                </nav>
                <nav aria-label="キャストを探す">
                    <p class="text-gold-400 font-bold mb-3 text-xs tracking-widest uppercase">キャストを探す</p>
                    <ul class="space-y-2">
                        <li><a href="{{ url("/{$fa}/girl-list/") }}/" class="block py-1.5 text-deli-400 hover:text-deli-300 transition">キャスト一覧</a></li>
                        <li><a href="{{ url("/{$fa}/girl-list/standby/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">本日出勤</a></li>
                        <li><a href="{{ url("/{$fa}/girl-list/new/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">新人</a></li>
                        <li><a href="{{ url("/{$fa}/girl-list/diary/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">写メ日記</a></li>
                        <li><a href="{{ url("/{$fa}/girl-list/review/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">口コミ</a></li>
                    </ul>
                </nav>
                <nav aria-label="タイプで探す">
                    <p class="text-gold-400 font-bold mb-3 text-xs tracking-widest uppercase">タイプで探す</p>
                    <ul class="space-y-2">
                        <li><a href="{{ url("/{$fa}/girl-list/type/kirei/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">キレイ系</a></li>
                        <li><a href="{{ url("/{$fa}/girl-list/type/kawaii/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">カワイイ系</a></li>
                        <li><a href="{{ url("/{$fa}/girl-list/type/sexy/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">セクシー系</a></li>
                        <li><a href="{{ url("/{$fa}/girl-list/type/jukujo/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">熟女系</a></li>
                        <li><a href="{{ url("/{$fa}/girl-list/type/hitozuma/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">人妻系</a></li>
                        <li><a href="{{ url("/{$fa}/girl-list/type/model/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">モデル系</a></li>
                        <li><a href="{{ url("/{$fa}/girl-list/type/rori/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">ロリ系</a></li>
                        <li><a href="{{ url("/{$fa}/girl-list/type/gal/") }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">ギャル系</a></li>
                    </ul>
                </nav>
                <nav aria-label="サービス">
                    <p class="text-gold-400 font-bold mb-3 text-xs tracking-widest uppercase">サービス</p>
                    <ul class="space-y-2">
                        <li><a href="{{ route('login') }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">店舗掲載のご案内</a></li>
                        <li><a href="{{ route('terms') }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">利用規約</a></li>
                        <li><a href="{{ route('privacy') }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">プライバシーポリシー</a></li>
                        <li><a href="{{ route('inquiry') }}/" class="block py-1.5 text-[#B0AEAD] hover:text-[#E8E4DC] transition">お問い合わせ</a></li>
                    </ul>
                </nav>
            </div>

            {{-- グループサイト --}}
            <div class="border-t border-surface-400 pt-5 mb-5">
                <p class="text-gold-400 font-bold mb-2 text-xs tracking-widest uppercase">グループサイト</p>
                <div class="flex flex-col">
                <a href="https://fuzoku-list.com/" target="_blank" rel="noopener noreferrer" class="block py-1.5 text-sm text-[#B0AEAD] hover:text-[#E8E4DC] transition">風俗リスト</a>
                <a href="https://www.mens-v.com/" target="_blank" rel="noopener noreferrer" class="block py-1.5 text-sm text-[#B0AEAD] hover:text-[#E8E4DC] transition">風俗情報メンズバリュー</a>
                <a href="https://www.up-stage.info/" target="_blank" rel="noopener noreferrer" class="block py-1.5 text-sm text-[#B0AEAD] hover:text-[#E8E4DC] transition">男性求人アップステージ</a>
                </div>
            </div>

            {{-- エリアから探す（ジャンル別・小エリア・都道府県フィルタ・30日PV順 top10） --}}
            @php
                $footerGenresRaw = \Illuminate\Support\Facades\Cache::get('delicon:footer_genre_prefs', []);

                // コントローラーから渡された都道府県スラッグを優先し、なければ$faから解決
                $footerPrefSlug = $footerPrefSlug ?? null;
                if (!$footerPrefSlug && $fa !== 'all') {
                    $footerPrefSlug = \Illuminate\Support\Facades\Cache::remember("slug:footer_pref:{$fa}", 86400, function () use ($fa) {
                        $pref = \Illuminate\Support\Facades\DB::table('prefectures')->where('slug', $fa)->value('slug');
                        if ($pref) return $pref;
                        return \Illuminate\Support\Facades\DB::table('areas')
                            ->join('prefectures', 'prefectures.id', '=', 'areas.prefecture_id')
                            ->where('areas.slug', $fa)
                            ->value('prefectures.slug');
                    });
                }

                $footerGenres = collect($footerGenresRaw)->map(function ($g) use ($footerPrefSlug) {
                    $areas = collect($g['areas'] ?? []);
                    if ($footerPrefSlug) {
                        $areas = $areas->filter(fn($a) => ($a['pref_slug'] ?? null) === $footerPrefSlug);
                    }
                    $areas = $areas->take(10)->map(fn($a) => (object) $a)->values();
                    return (object) ['name' => $g['name'], 'slug' => $g['slug'], 'areas' => $areas];
                })->filter(fn($g) => $g->areas->isNotEmpty());
            @endphp
            @if($footerGenres->isNotEmpty())
            <div class="border-t border-surface-400 pt-5 mb-5">
                <p class="text-gold-400 font-bold mb-3 text-xs tracking-widest uppercase">エリアから探す</p>
                <div class="space-y-2">
                    @foreach($footerGenres as $genre)
                    @foreach($genre->areas as $area)
                    <a href="{{ url('/' . $area->slug . '/shop-list/') }}/" class="block py-1 text-sm text-[#B0AEAD] hover:text-[#E8E4DC] transition">{{ $area->name }}</a>
                    @endforeach
                    @endforeach
                </div>
            </div>
            @endif
            <div class="border-t border-surface-400 pt-6 text-xs text-[#8A8A9E] leading-relaxed mb-6">
                <p>当サイトに掲載されている店舗情報は各掲載店舗が管理・提供するものです。掲載内容の正確性について当サイトは保証しておらず、掲載情報に起因するいかなるトラブルや損害についても責任を負いかねます。</p>
            </div>
            <div class="border-t border-surface-400 pt-6 text-center text-xs text-[#8A8A9E]">
                <p class="text-[#E8E4DC] font-bold tracking-widest mb-2">デリヘル<span class="text-gold-400">リスト</span></p>
                <p>© {{ date('Y') }} デリヘルリスト All Rights Reserved.</p>
                <p class="mt-2 text-deli-400 font-semibold">⚠ 本サイトは18歳以上の方を対象としています。</p>
            </div>
        </div>
    </footer>

    <script @nonce>
    document.addEventListener('error', function(e) {
        var t = e.target;
        if (t.tagName !== 'IMG') return;
        if (t.classList.contains('img-onerror-hide')) { t.style.display='none'; }
        if (t.classList.contains('img-onerror-cast')) { if (t.src !== location.origin+'/img/no-cast.svg') t.src='/img/no-cast.svg'; }
    }, true);
    </script>
    @stack('scripts')
</body>
</html>
