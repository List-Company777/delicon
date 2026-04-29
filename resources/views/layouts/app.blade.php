<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ナイトワークリスト') | ナイトワークリスト</title>
    <meta name="description" content="@yield('description', 'キャバクラ・ホスト・ガールズバーの求人・夜遊び情報を掲載。エリア・職種から簡単検索。')">
    @hasSection('canonical')
    <link rel="canonical" href="@yield('canonical')">
    @endif
    @hasSection('robots')
    <meta name="robots" content="@yield('robots')">
    @endif
    {{-- OGP / Twitter Card --}}
    <meta property="og:site_name" content="ナイトワークリスト">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'ナイトワークリスト') | ナイトワークリスト">
    <meta property="og:description" content="@yield('description', 'キャバクラ・ホスト・ガールズバーの求人・夜遊び情報を掲載。エリア・職種から簡単検索。')">
    <meta property="og:url" content="@hasSection('canonical')@yield('canonical')@else{{ url()->current() }}@endif">
    <meta property="og:image" content="@yield('ogp_image', asset('android-chrome-192x192.png'))">
    <meta name="twitter:card" content="@yield('twitter_card', 'summary')">
    <meta name="twitter:image" content="@yield('ogp_image', asset('android-chrome-192x192.png'))">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    {{-- 外部ドメインへの事前接続（GA/GTM） --}}
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
                'name'            => 'ナイトワークリスト',
                'url'             => url('/') . '/',
                'foundingDate'    => '2026',
                'foundingLocation'=> ['@type' => 'Place', 'name' => '日本'],
                'logo'            => [
                    '@type'  => 'ImageObject',
                    '@id'    => url('/') . '#logo',
                    'url'    => asset('images/logo.svg'),
                    'width'  => 600,
                    'height' => 120,
                ],
                'address' => [
                    '@type'           => 'PostalAddress',
                    'addressCountry'  => 'JP',
                    'addressRegion'   => '東京都',
                    'postalCode'      => '104-0061',
                    'addressLocality' => '中央区',
                    'streetAddress'   => '銀座3-10-9 KEC銀座ビル701',
                ],
                'contactPoint' => [
                    '@type'             => 'ContactPoint',
                    'telephone'         => '+81352066966',
                    'contactType'       => 'customer service',
                    'areaServed'        => 'JP',
                    'availableLanguage' => ['ja'],
                ],
                'parentOrganization' => [
                    '@type' => 'Organization',
                    'name'  => '株式会社リスト',
                    'url'   => 'https://list-company.net/',
                ],
            ],
            [
                '@type'           => 'WebSite',
                '@id'             => url('/') . '#website',
                'url'             => url('/') . '/',
                'name'            => 'ナイトワークリスト',
                'inLanguage'      => 'ja',
                'publisher'       => ['@id' => url('/') . '#org'],
                'isAccessibleForFree' => true,
                'potentialAction' => [
                    '@type'       => 'SearchAction',
                    'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => url('/') . '/search/?keyword={search_term_string}'],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ],
    ];
@endphp
    <script type="application/ld+json" @nonce>{!! json_encode($ldSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-FE65XYN5VT" @nonce></script>
    <script @nonce>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-FE65XYN5VT');
    </script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

    {{-- ヘッダー --}}
    <header class="bg-gray-900 text-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('top') }}/" class="text-xl font-bold tracking-wide hover:opacity-80 transition">
                ナイトワーク<span class="text-yellow-400">リスト</span>
            </a>
            <nav class="hidden md:flex items-center gap-6 text-sm">
                <a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => 'all', 'job_slug' => 'all']) }}/"
                   class="text-business-300 hover:text-business-200 font-medium transition">
                    夜遊びリスト
                </a>
                <a href="{{ route('search.directory', ['gender' => 'male', 'area_slug' => 'all', 'job_slug' => 'all']) }}/"
                   class="text-male-300 hover:text-male-200 font-medium transition">
                    男性ナイトワーク
                </a>
                <a href="{{ route('search.directory', ['gender' => 'female', 'area_slug' => 'all', 'job_slug' => 'all']) }}/"
                   class="text-female-400 hover:text-female-300 font-medium transition">
                    女性ナイトワーク
                </a>
                <a href="{{ route('article.index') }}"
                   class="text-gray-300 hover:text-white font-medium transition">
                    コラム
                </a>
                @auth
                    <a href="{{ route('manage.dashboard') }}/"
                       class="text-gray-300 hover:text-white transition border border-gray-600 hover:border-gray-400 rounded px-3 py-1 text-xs">
                        管理画面
                    </a>
                @else
                    <a href="{{ route('login') }}/"
                       class="text-gray-300 hover:text-white transition border border-gray-600 hover:border-gray-400 rounded px-3 py-1 text-xs">
                        店舗ログイン
                    </a>
                @endauth
            </nav>
            {{-- スマホ用ハンバーガー --}}
            <button class="md:hidden text-gray-300 hover:text-white" aria-label="メニューを開く" x-data @click="$dispatch('toggle-menu')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
        {{-- スマホメニュー（x-if でDOMから除外して getComputedStyle 強制リフロー回避） --}}
        <div x-data="{ open: false }" @toggle-menu.window="open = !open">
            <template x-if="open">
                <div class="md:hidden bg-gray-800 px-4 pb-4">
                    <a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => 'all', 'job_slug' => 'all']) }}/"
                       class="block py-2 text-business-300 font-medium">夜遊びリスト</a>
                    <a href="{{ route('search.directory', ['gender' => 'male', 'area_slug' => 'all', 'job_slug' => 'all']) }}/"
                       class="block py-2 text-male-300 font-medium">男性ナイトワーク</a>
                    <a href="{{ route('search.directory', ['gender' => 'female', 'area_slug' => 'all', 'job_slug' => 'all']) }}/"
                       class="block py-2 text-female-400 font-medium">女性ナイトワーク</a>
                    <a href="{{ route('article.index') }}"
                       class="block py-2 text-gray-300 font-medium">コラム</a>
                    <div class="border-t border-gray-700 mt-2 pt-2">
                        @auth
                            <a href="{{ route('manage.dashboard') }}/"
                               class="block py-2 text-gray-300 text-sm">管理画面</a>
                        @else
                            <a href="{{ route('login') }}/"
                               class="block py-2 text-gray-300 text-sm">店舗ログイン</a>
                        @endauth
                    </div>
                </div>
            </template>
        </div>
    </header>

    {{-- メインコンテンツ --}}
    <main>
        @yield('content')
    </main>

    {{-- フッター --}}
    <footer class="bg-gray-900 text-gray-400 mt-16">
        <div class="max-w-6xl mx-auto px-4 py-10">

            {{-- グループサイト --}}
            <div class="mb-8">
                <h3 class="text-white font-bold text-sm mb-3">グループサイト</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <a href="https://up-stage.info/" target="_blank" rel="noopener" class="block group">
                        <picture>
                            <img src="{{ asset('images/group/upstage-banner.svg') }}" alt="アップステージ"
                                 class="w-full rounded opacity-80 group-hover:opacity-100 transition-opacity"
                                 width="970" height="250"
                                 loading="lazy" decoding="async" fetchpriority="low">
                        </picture>
                        <span class="block text-xs text-center mt-1 text-gray-500 group-hover:text-gray-300 transition-colors">男性求人アップステージ</span>
                    </a>
                    <a href="https://genbars.jp/" target="_blank" rel="noopener" class="block group">
                        <picture>
                            <img src="{{ asset('images/group/genbars-banner.svg') }}" alt="ゲンバーズ"
                                 class="w-full rounded opacity-80 group-hover:opacity-100 transition-opacity"
                                 width="970" height="250"
                                 loading="lazy" decoding="async" fetchpriority="low">
                        </picture>
                        <span class="block text-xs text-center mt-1 text-gray-500 group-hover:text-gray-300 transition-colors">現場求人ゲンバーズ</span>
                    </a>
                    <a href="https://cabacrown.net/" target="_blank" rel="noopener" class="block group">
                        <picture>
                            <img src="{{ asset('images/group/kyabakurun-banner.svg') }}" alt="キャバクラウン"
                                 class="w-full rounded opacity-80 group-hover:opacity-100 transition-opacity"
                                 width="970" height="250"
                                 loading="lazy" decoding="async" fetchpriority="low">
                        </picture>
                        <span class="block text-xs text-center mt-1 text-gray-500 group-hover:text-gray-300 transition-colors">ナイトワークキャバクラウン</span>
                    </a>
                </div>
            </div>
            <div class="border-t border-gray-700 mb-8"></div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8 text-sm">
                <div>
                    <p class="text-white font-bold mb-3">夜遊びリスト</p>
                    <ul class="space-y-1">
                        <li><a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => 'all', 'job_slug' => 'all']) }}/"
                               class="text-business-400 hover:text-business-300 font-medium transition">夜遊び情報を全国で探す</a></li>
                        <li><a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => 'shinjuku', 'job_slug' => 'all']) }}/"
                               class="hover:text-business-300 transition">新宿の夜遊び情報</a></li>
                        <li><a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => 'ikebukuro', 'job_slug' => 'all']) }}/"
                               class="hover:text-business-300 transition">池袋の夜遊び情報</a></li>
                        <li><a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => 'shibuya', 'job_slug' => 'all']) }}/"
                               class="hover:text-business-300 transition">渋谷の夜遊び情報</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-white font-bold mb-3">男性ナイトワーク</p>
                    <ul class="space-y-1">
                        <li><a href="{{ route('search.directory', ['gender' => 'male', 'area_slug' => 'all', 'job_slug' => 'all']) }}/"
                               class="text-male-400 hover:text-male-300 font-medium transition">男性ナイトワーク求人を探す</a></li>
                        <li><a href="{{ route('search.directory', ['gender' => 'male', 'area_slug' => 'shinjuku', 'job_slug' => 'all']) }}/"
                               class="hover:text-male-300 transition">新宿の男性求人</a></li>
                        <li><a href="{{ route('search.directory', ['gender' => 'male', 'area_slug' => 'ikebukuro', 'job_slug' => 'all']) }}/"
                               class="hover:text-male-300 transition">池袋の男性求人</a></li>
                        <li><a href="{{ route('search.directory', ['gender' => 'male', 'area_slug' => 'shibuya', 'job_slug' => 'all']) }}/"
                               class="hover:text-male-300 transition">渋谷の男性求人</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-white font-bold mb-3">女性ナイトワーク</p>
                    <ul class="space-y-1">
                        <li><a href="{{ route('search.directory', ['gender' => 'female', 'area_slug' => 'all', 'job_slug' => 'all']) }}/"
                               class="text-female-400 hover:text-female-300 font-medium transition">女性ナイトワーク求人を探す</a></li>
                        <li><a href="{{ route('search.directory', ['gender' => 'female', 'area_slug' => 'shinjuku', 'job_slug' => 'all']) }}/"
                               class="hover:text-female-400 transition">新宿の女性求人</a></li>
                        <li><a href="{{ route('search.directory', ['gender' => 'female', 'area_slug' => 'ikebukuro', 'job_slug' => 'all']) }}/"
                               class="hover:text-female-400 transition">池袋の女性求人</a></li>
                        <li><a href="{{ route('search.directory', ['gender' => 'female', 'area_slug' => 'shibuya', 'job_slug' => 'all']) }}/"
                               class="hover:text-female-400 transition">渋谷の女性求人</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-white font-bold mb-3">サービスについて</p>
                    <ul class="space-y-1">
                        <li><a href="{{ route('company') }}/" class="hover:text-white transition">運営会社</a></li>
                        <li><a href="{{ route('login') }}/" class="hover:text-white transition">店舗掲載のご案内</a></li>
                        <li><a href="{{ route('terms') }}/" class="hover:text-white transition">サービス利用規約</a></li>
                        <li><a href="{{ route('advertiser') }}/" class="hover:text-white transition">掲載規約</a></li>
                        <li><a href="{{ route('privacy') }}/" class="hover:text-white transition">プライバシーポリシー</a></li>
                        <li><a href="{{ route('tokutei') }}/" class="hover:text-white transition">特定商取引法に基づく表記</a></li>
                        <li><a href="{{ route('inquiry') }}/" class="hover:text-white transition">お問い合わせ</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 pt-6 text-xs text-gray-400 leading-relaxed mb-6">
                <p>当サイトに掲載されている店舗情報および求人情報は、各掲載店舗によって管理・提供されたものです。掲載内容の正確性・完全性について当サイトは保証しておらず、掲載情報に起因するいかなるトラブルや損害についても責任を負いかねます。求人へご応募の方は、面接や職場見学を通じて業務内容・勤務条件を十分にご確認のうえご判断ください。</p>
            </div>
            <div class="border-t border-gray-700 pt-6 text-center text-xs">
                <p>© {{ date('Y') }} ナイトワークリスト All Rights Reserved.</p>
                <p class="mt-1 text-gray-500">本サイトは18歳以上の方を対象としています。</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
