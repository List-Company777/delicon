<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'デリヘル情報') | デリコン</title>
    <meta name="description" content="@yield('description', '全国のデリヘル情報を掲載。デリヘル店のシステム・料金・在籍キャストのプロフィールが検索できる総合情報サイト。')">
    @hasSection('canonical')
    <link rel="canonical" href="@yield('canonical')">
    @endif
    @hasSection('robots')
    <meta name="robots" content="@yield('robots')">
    @endif
    <meta property="og:site_name" content="デリコン">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', 'デリヘル情報') | デリコン">
    <meta property="og:description" content="@yield('description', '全国のデリヘル情報を掲載。デリヘル店のシステム・料金・在籍キャストのプロフィールが検索できる総合情報サイト。')">
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
    <meta name="apple-mobile-web-app-title" content="デリコン">
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
                'name'            => 'デリコン',
                'url'             => url('/') . '/',
                'foundingDate'    => '2026',
                'foundingLocation'=> ['@type' => 'Place', 'name' => '日本'],
                'logo'            => ['@type' => 'ImageObject', '@id' => url('/') . '#logo', 'url' => asset('images/logo.svg'), 'width' => 600, 'height' => 120],
                'address'         => ['@type' => 'PostalAddress', 'addressCountry' => 'JP', 'addressRegion' => '東京都', 'postalCode' => '104-0061', 'addressLocality' => '中央区', 'streetAddress' => '銀座3-10-9 KEC銀座ビル701'],
                'contactPoint'    => ['@type' => 'ContactPoint', 'telephone' => '+81352066966', 'contactType' => 'customer service', 'areaServed' => 'JP', 'availableLanguage' => ['ja']],
                'parentOrganization' => ['@type' => 'Organization', 'name' => '株式会社リスト', 'url' => 'https://list-company.net/'],
            ],
            [
                '@type'           => 'WebSite',
                '@id'             => url('/') . '#website',
                'url'             => url('/') . '/',
                'name'            => 'デリコン',
                'inLanguage'      => 'ja',
                'publisher'       => ['@id' => url('/') . '#org'],
                'isAccessibleForFree' => true,
                'potentialAction' => ['@type' => 'SearchAction', 'target' => ['@type' => 'EntryPoint', 'urlTemplate' => url('/') . '/shops/?q={search_term_string}'], 'query-input' => 'required name=search_term_string'],
            ],
        ],
    ];
@endphp
    <script type="application/ld+json" @nonce>{!! json_encode($ldSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-FE65XYN5VT" @nonce></script>
    <script @nonce>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-FE65XYN5VT');
    </script>
</head>
<body class="bg-surface-700 text-[#E8E4DC] antialiased">

    <header class="bg-surface-800 border-b border-surface-400 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('top') }}/" class="text-lg font-bold tracking-widest hover:opacity-80 transition shrink-0">
                <span class="text-[#E8E4DC]">deli</span><span class="text-gold-400">con</span>
            </a>
            <nav class="hidden md:flex items-center gap-1 text-sm">
                <a href="{{ route('shop.index') }}/"
                   class="px-3 py-2 rounded text-[#B0AEAD] hover:text-gold-400 hover:bg-surface-600 transition">
                    店舗一覧
                </a>
                <a href="{{ route('cast.index') }}/"
                   class="px-3 py-2 rounded text-deli-400 hover:text-deli-300 hover:bg-surface-600 transition">
                    キャスト検索
                </a>
                <a href="{{ route('article.index') }}/"
                   class="px-3 py-2 rounded text-[#B0AEAD] hover:text-gold-400 hover:bg-surface-600 transition">
                    コラム
                </a>
                @auth
                    @if(auth()->user()->role === 'visitor')
                    <a href="{{ route('user.dashboard') }}/"
                       class="ml-2 border border-deli-500/50 hover:border-deli-400 text-deli-400 hover:text-deli-300 rounded px-3 py-1.5 text-xs transition">
                        マイページ
                    </a>
                    @else
                    <a href="{{ route('manage.dashboard') }}/"
                       class="ml-2 border border-surface-300 hover:border-gold-400 text-[#B0AEAD] hover:text-gold-400 rounded px-3 py-1.5 text-xs transition">
                        管理画面
                    </a>
                    @endif
                @else
                    <a href="{{ route('visitor.register') }}/"
                       class="ml-2 border border-deli-500/50 hover:border-deli-400 text-deli-400 hover:text-deli-300 rounded px-3 py-1.5 text-xs transition">
                        会員登録
                    </a>
                    <a href="{{ route('login') }}/"
                       class="ml-2 border border-surface-300 hover:border-gold-400 text-[#B0AEAD] hover:text-gold-400 rounded px-3 py-1.5 text-xs transition">
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
                    <a href="{{ route('shop.index') }}/" class="block py-2.5 text-[#B0AEAD] hover:text-gold-400 border-b border-surface-400 transition">店舗一覧</a>
                    <a href="{{ route('cast.index') }}/" class="block py-2.5 text-deli-400 hover:text-deli-300 border-b border-surface-400 transition">キャスト検索</a>
                    <a href="{{ route('article.index') }}/" class="block py-2.5 text-[#B0AEAD] hover:text-gold-400 border-b border-surface-400 transition">コラム</a>
                    @auth
                        <a href="{{ route('manage.dashboard') }}/" class="block py-2.5 text-[#B0AEAD] hover:text-gold-400 text-sm transition">管理画面</a>
                    @else
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
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8 text-sm">
                <div>
                    <p class="text-gold-400 font-bold mb-3 text-xs tracking-widest uppercase">店舗を探す</p>
                    <ul class="space-y-2">
                        <li><a href="{{ route('shop.index') }}/" class="text-[#8A8A9E] hover:text-[#E8E4DC] transition">全国の店舗一覧</a></li>
                        <li><a href="{{ route('shop.index') }}/?type=7" class="text-[#8A8A9E] hover:text-[#E8E4DC] transition">ホテヘル</a></li>
                        <li><a href="{{ route('shop.index') }}/?type=10" class="text-[#8A8A9E] hover:text-[#E8E4DC] transition">素人系</a></li>
                        <li><a href="{{ route('shop.index') }}/?type=1" class="text-[#8A8A9E] hover:text-[#E8E4DC] transition">人妻・熟女</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-gold-400 font-bold mb-3 text-xs tracking-widest uppercase">キャストを探す</p>
                    <ul class="space-y-2">
                        <li><a href="{{ route('cast.index') }}/" class="text-deli-400 hover:text-deli-300 transition">キャスト一覧</a></li>
                        <li><a href="{{ route('cast.index') }}/?type=1" class="text-[#8A8A9E] hover:text-[#E8E4DC] transition">ロリ系</a></li>
                        <li><a href="{{ route('cast.index') }}/?type=2" class="text-[#8A8A9E] hover:text-[#E8E4DC] transition">キレイ系</a></li>
                        <li><a href="{{ route('cast.index') }}/?type=5" class="text-[#8A8A9E] hover:text-[#E8E4DC] transition">セクシー系</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-gold-400 font-bold mb-3 text-xs tracking-widest uppercase">コラム</p>
                    <ul class="space-y-2">
                        <li><a href="{{ route('article.index') }}/" class="text-[#8A8A9E] hover:text-[#E8E4DC] transition">コラム一覧</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-gold-400 font-bold mb-3 text-xs tracking-widest uppercase">グループサイト</p>
                    <ul class="space-y-2">
                        <li><a href="https://fuzoku-list.com/" target="_blank" rel="noopener noreferrer" class="text-[#8A8A9E] hover:text-[#E8E4DC] transition">風俗情報フウゾクリスト</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-gold-400 font-bold mb-3 text-xs tracking-widest uppercase">サービス</p>
                    <ul class="space-y-2">
                        <li><a href="{{ route('login') }}/" class="text-[#8A8A9E] hover:text-[#E8E4DC] transition">店舗掲載のご案内</a></li>
                        <li><a href="{{ route('terms') }}/" class="text-[#8A8A9E] hover:text-[#E8E4DC] transition">利用規約</a></li>
                        <li><a href="{{ route('privacy') }}/" class="text-[#8A8A9E] hover:text-[#E8E4DC] transition">プライバシーポリシー</a></li>
                        <li><a href="{{ route('tokutei') }}/" class="text-[#8A8A9E] hover:text-[#E8E4DC] transition">特定商取引法</a></li>
                        <li><a href="{{ route('inquiry') }}/" class="text-[#8A8A9E] hover:text-[#E8E4DC] transition">お問い合わせ</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-surface-400 pt-6 text-xs text-[#6A6A7E] leading-relaxed mb-6">
                <p>当サイトに掲載されている店舗情報は各掲載店舗が管理・提供するものです。掲載内容の正確性について当サイトは保証しておらず、掲載情報に起因するいかなるトラブルや損害についても責任を負いかねます。</p>
            </div>
            <div class="border-t border-surface-400 pt-6 text-center text-xs text-[#6A6A7E]">
                <p class="text-[#E8E4DC] font-bold tracking-widest mb-2">deli<span class="text-gold-400">con</span></p>
                <p>© {{ date('Y') }} デリコン All Rights Reserved.</p>
                <p class="mt-2 text-deli-400 font-semibold">⚠ 本サイトは18歳以上の方を対象としています。</p>
            </div>
        </div>
    </footer>

    <script @nonce>
    document.addEventListener('error', function(e) {
        var t = e.target;
        if (t.tagName !== 'IMG') return;
        if (t.classList.contains('img-onerror-hide')) { t.style.display='none'; }
        if (t.classList.contains('img-onerror-cast')) { if (t.src !== location.origin+'/img/no-cast.jpg') t.src='/img/no-cast.jpg'; }
    }, true);
    </script>
    @stack('scripts')
</body>
</html>
