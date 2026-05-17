<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>デリヘル 無料掲載｜女性求人・スタッフ求人も月額0円【デリヘルリスト】</title>
    <meta name="description" content="デリヘルリストへの無料掲載のご案内。デリヘルなど風俗店の店舗情報・女性求人・スタッフ求人を月額0円で掲載できます。登録5分、最短即日掲載。">
    <link rel="canonical" href="{{ url('/keisai/') }}">
    <meta property="og:title" content="デリヘル 無料掲載｜女性求人・スタッフ求人も月額0円【デリヘルリスト】">
    <meta property="og:description" content="デリヘルの店舗情報・女性求人・スタッフ求人を無料掲載。月額0円・登録費0円。登録5分、最短即日。">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/keisai/') }}">
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name'  => '本当に無料で掲載できますか？',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => '店舗情報ページの作成・女性求人・スタッフ求人の掲載はすべて月額0円・登録費0円です。有料オプションは存在しますが、基本機能は永久無料でご利用いただけます。',
                ],
            ],
            [
                '@type' => 'Question',
                'name'  => '掲載できる店舗の種類は何ですか？',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => 'デリバリーヘルス（デリヘル）・オナクラ・風俗エステなど、デリバリー系・店舗型を問わず風俗店であればご掲載いただけます。',
                ],
            ],
            [
                '@type' => 'Question',
                'name'  => '登録にはどれくらい時間がかかりますか？',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => '店舗情報の入力から掲載まで最短5分で完了します。審査は通常即日〜1営業日以内に完了し、承認後すぐに掲載が開始されます。',
                ],
            ],
            [
                '@type' => 'Question',
                'name'  => '掲載後に修正や更新はできますか？',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => 'はい、管理画面からいつでも店舗情報・求人内容を修正・追加できます。更新回数に制限はありません。',
                ],
            ],
            [
                '@type' => 'Question',
                'name'  => '女性求人とスタッフ求人は両方掲載できますか？',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => 'はい、店舗情報ページ・女性求人（キャスト募集）・スタッフ求人（ドライバー・受付など）のすべてを同時に掲載できます。',
                ],
            ],
            [
                '@type' => 'Question',
                'name'  => '退会・掲載停止はいつでもできますか？',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => 'はい、いつでも管理画面から掲載停止・退会が可能です。違約金や解約手数料は一切かかりません。',
                ],
            ],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @vite(['resources/css/app.css'])
    <style>
        .gradient-hero {
            background: linear-gradient(135deg, #0C2B30 0%, #0B132B 50%, #451226 100%);
        }
        .card-glow:hover {
            box-shadow: 0 0 24px rgba(176, 208, 211, 0.12);
        }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 font-sans">

<header role="banner" class="bg-gray-900/90 backdrop-blur-sm border-b border-gray-800 sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="{{ url('/') }}" class="flex items-center gap-2">
            <span class="text-lg font-black tracking-tight text-white">デリヘルリスト</span>
            <span class="hidden sm:inline text-xs text-teal-300 border border-teal-700/50 rounded px-2 py-0.5 bg-teal-900/20">無料掲載受付中</span>
        </a>
        <nav aria-label="ページ内ナビゲーション" class="hidden md:flex items-center gap-6 text-sm text-gray-400">
            <a href="#features" class="hover:text-white transition">掲載内容</a>
            <a href="#why" class="hover:text-white transition">選ばれる理由</a>
            <a href="#flow" class="hover:text-white transition">掲載の流れ</a>
            <a href="#faq" class="hover:text-white transition">よくある質問</a>
        </nav>
        <a href="{{ route('register') }}"
           class="bg-business-700 hover:bg-business-600 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
            無料で登録する
        </a>
    </div>
</header>

<main id="main-content">

{{-- ヒーロー --}}
<section class="gradient-hero py-16 md:py-24" aria-labelledby="hero-heading">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <div class="inline-block bg-business-900/60 border border-business-700/40 rounded-full px-5 py-1.5 text-teal-300 text-xs font-bold mb-6 tracking-widest uppercase">
            店舗掲載・女性求人・スタッフ求人 すべて月額0円
        </div>
        <h1 id="hero-heading" class="text-3xl md:text-5xl font-black leading-tight mb-6 text-white">
            デリヘルの<br>
            <span class="text-teal-300">集客・採用を、無料で始めよう</span>
        </h1>
        <p class="text-gray-300 text-base md:text-lg leading-relaxed max-w-2xl mx-auto mb-10">
            デリヘルリストは、デリバリーヘルス（デリヘル）など風俗店向けの掲載プラットフォームです。<br>
            店舗情報ページの作成から女性求人・スタッフ求人まで、すべて無料でご利用いただけます。
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}"
               class="bg-teal-500 hover:bg-teal-400 text-gray-950 font-black text-base px-8 py-4 rounded-xl transition shadow-lg shadow-teal-900/40">
                今すぐ無料で掲載する
            </a>
            <a href="#flow"
               class="border border-gray-600 hover:border-teal-500 text-gray-300 hover:text-teal-300 font-bold text-base px-8 py-4 rounded-xl transition">
                掲載の流れを見る
            </a>
        </div>
        <p class="mt-6 text-xs text-gray-500">登録費0円・月額0円・違約金なし</p>
    </div>
</section>

{{-- 数字で見るデリヘルリスト --}}
<section class="bg-gray-900 py-10 border-y border-gray-800" aria-label="デリヘルリスト実績">
    <div class="max-w-4xl mx-auto px-4">
        <dl class="grid grid-cols-3 gap-6 text-center">
            <div>
                <dt class="text-xs text-gray-500 mb-1">掲載店舗数</dt>
                <dd class="text-2xl md:text-4xl font-black text-teal-300">1,000<span class="text-base font-bold">店+</span></dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500 mb-1">月間閲覧数</dt>
                <dd class="text-2xl md:text-4xl font-black text-teal-300">30万<span class="text-base font-bold">PV+</span></dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500 mb-1">登録所要時間</dt>
                <dd class="text-2xl md:text-4xl font-black text-teal-300">5<span class="text-base font-bold">分</span></dd>
            </div>
        </dl>
    </div>
</section>

{{-- 掲載できる内容 --}}
<section id="features" class="py-16 md:py-24" aria-labelledby="features-heading">
    <div class="max-w-5xl mx-auto px-4">
        <header class="text-center mb-12">
            <h2 id="features-heading" class="text-2xl md:text-3xl font-black text-white mb-3">
                デリヘルリストに無料掲載できる内容
            </h2>
            <p class="text-gray-400 text-sm">デリヘル・風俗店の集客と採用を、まるごと無料でサポートします</p>
        </header>
        <div class="grid md:grid-cols-3 gap-6">
            <article class="card-glow bg-gray-900 border border-gray-800 rounded-2xl p-6 transition">
                <div class="w-12 h-12 bg-teal-900/40 rounded-xl flex items-center justify-center mb-4" aria-hidden="true">
                    <svg class="w-6 h-6 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h3 class="font-black text-white text-lg mb-2">店舗情報ページ</h3>
                <p class="text-gray-400 text-sm leading-relaxed">店舗概要・コース料金・エリア・写真を掲載。デリヘル利用者への集客に活用できます。</p>
            </article>
            <article class="card-glow bg-gray-900 border border-gray-800 rounded-2xl p-6 transition">
                <div class="w-12 h-12 bg-teal-900/40 rounded-xl flex items-center justify-center mb-4" aria-hidden="true">
                    <svg class="w-6 h-6 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <h3 class="font-black text-white text-lg mb-2">女性求人（キャスト募集）</h3>
                <p class="text-gray-400 text-sm leading-relaxed">日給・時給・待遇・エリアを指定して女性キャストを募集できます。未経験歓迎・体験入店など詳細条件も掲載可能。</p>
            </article>
            <article class="card-glow bg-gray-900 border border-gray-800 rounded-2xl p-6 transition">
                <div class="w-12 h-12 bg-teal-900/40 rounded-xl flex items-center justify-center mb-4" aria-hidden="true">
                    <svg class="w-6 h-6 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="font-black text-white text-lg mb-2">スタッフ求人</h3>
                <p class="text-gray-400 text-sm leading-relaxed">ドライバー・受付・管理スタッフなどバックオフィス求人も無料掲載。即戦力スタッフの採用をサポートします。</p>
            </article>
        </div>
    </div>
</section>

{{-- なぜデリヘルリストに掲載するか --}}
<section id="why" class="bg-gray-900 py-16 md:py-24 border-y border-gray-800" aria-labelledby="why-heading">
    <div class="max-w-5xl mx-auto px-4">
        <header class="text-center mb-12">
            <h2 id="why-heading" class="text-2xl md:text-3xl font-black text-white mb-3">
                デリヘルリストが選ばれる3つの理由
            </h2>
            <p class="text-gray-400 text-sm">デリヘル・風俗業界に特化した掲載プラットフォーム</p>
        </header>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-teal-900/40 border border-teal-700/30 rounded-2xl flex items-center justify-center mx-auto mb-4" aria-hidden="true">
                    <span class="text-2xl font-black text-teal-300">01</span>
                </div>
                <h3 class="font-black text-white text-base mb-2">デリヘル専門の集客導線</h3>
                <p class="text-gray-400 text-sm leading-relaxed">「デリヘル エリア名」「風俗 求人」など利用者・求職者が実際に検索するキーワードに最適化されています。</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-teal-900/40 border border-teal-700/30 rounded-2xl flex items-center justify-center mx-auto mb-4" aria-hidden="true">
                    <span class="text-2xl font-black text-teal-300">02</span>
                </div>
                <h3 class="font-black text-white text-base mb-2">集客と採用を一元管理</h3>
                <p class="text-gray-400 text-sm leading-relaxed">店舗情報ページ・女性求人・スタッフ求人を同一アカウントで管理。問い合わせも一括で確認できます。</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-teal-900/40 border border-teal-700/30 rounded-2xl flex items-center justify-center mx-auto mb-4" aria-hidden="true">
                    <span class="text-2xl font-black text-teal-300">03</span>
                </div>
                <h3 class="font-black text-white text-base mb-2">完全無料・リスクなし</h3>
                <p class="text-gray-400 text-sm leading-relaxed">月額0円・成果報酬なし・違約金なし。費用をかけずに新規チャネルを試せるので、リスクなく始められます。</p>
            </div>
        </div>
    </div>
</section>

{{-- 掲載の流れ --}}
<section id="flow" class="py-16 md:py-24" aria-labelledby="flow-heading">
    <div class="max-w-4xl mx-auto px-4">
        <header class="text-center mb-12">
            <h2 id="flow-heading" class="text-2xl md:text-3xl font-black text-white mb-3">
                デリヘル・風俗店の無料掲載3ステップ
            </h2>
            <p class="text-gray-400 text-sm">登録から掲載開始まで最短5分</p>
        </header>
        <ol class="space-y-6">
            @foreach([
                ['step' => '01', 'title' => 'アカウント登録（無料）', 'desc' => 'メールアドレスとパスワードを入力するだけ。登録費・月額費用は一切かかりません。'],
                ['step' => '02', 'title' => '店舗情報・求人を入力', 'desc' => '店舗名・エリア・コース料金・写真などを入力します。女性求人・スタッフ求人は後から追加もできます。'],
                ['step' => '03', 'title' => '審査完了・掲載開始', 'desc' => '審査は通常即日〜1営業日以内に完了。承認後すぐにデリヘルリストに掲載されます。'],
            ] as $item)
            <li class="flex gap-6 items-start bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <div class="w-12 h-12 bg-teal-900/40 border border-teal-700/40 rounded-xl flex items-center justify-center shrink-0">
                    <span class="text-teal-300 font-black text-sm">{{ $item['step'] }}</span>
                </div>
                <div>
                    <h3 class="font-black text-white text-base mb-1">{{ $item['title'] }}</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">{{ $item['desc'] }}</p>
                </div>
            </li>
            @endforeach
        </ol>
        <div class="text-center mt-10">
            <a href="{{ route('register') }}"
               class="inline-block bg-teal-500 hover:bg-teal-400 text-gray-950 font-black text-base px-10 py-4 rounded-xl transition shadow-lg shadow-teal-900/40">
                今すぐ無料で掲載する
            </a>
            <p class="mt-3 text-xs text-gray-500">月額0円・登録費0円・いつでも退会可能</p>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section id="faq" class="bg-gray-900 py-16 md:py-24 border-t border-gray-800" aria-labelledby="faq-heading">
    <div class="max-w-3xl mx-auto px-4">
        <header class="text-center mb-10">
            <h2 id="faq-heading" class="text-2xl md:text-3xl font-black text-white mb-3">
                無料掲載に関するよくある質問
            </h2>
        </header>
        <div class="space-y-3">
            @foreach([
                ['q' => '本当に無料で掲載できますか？', 'a' => '店舗情報ページの作成・女性求人・スタッフ求人の掲載はすべて月額0円・登録費0円です。有料オプションは存在しますが、基本機能は永久無料でご利用いただけます。'],
                ['q' => '掲載できる店舗の種類は何ですか？', 'a' => 'デリバリーヘルス（デリヘル）・オナクラ・風俗エステなど、デリバリー系・店舗型を問わず風俗店であればご掲載いただけます。'],
                ['q' => '登録にはどれくらい時間がかかりますか？', 'a' => '店舗情報の入力から掲載まで最短5分で完了します。審査は通常即日〜1営業日以内に完了し、承認後すぐに掲載が開始されます。'],
                ['q' => '掲載後に修正や更新はできますか？', 'a' => 'はい、管理画面からいつでも店舗情報・求人内容を修正・追加できます。更新回数に制限はありません。'],
                ['q' => '女性求人とスタッフ求人は両方掲載できますか？', 'a' => 'はい、店舗情報ページ・女性求人（キャスト募集）・スタッフ求人（ドライバー・受付など）のすべてを同時に掲載できます。'],
                ['q' => '退会・掲載停止はいつでもできますか？', 'a' => 'はい、いつでも管理画面から掲載停止・退会が可能です。違約金や解約手数料は一切かかりません。'],
            ] as $item)
            <details class="bg-gray-800 border border-gray-700 rounded-xl group">
                <summary class="flex items-center justify-between gap-4 p-5 cursor-pointer select-none list-none">
                    <h3 class="font-bold text-white text-sm text-left">{{ $item['q'] }}</h3>
                    <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200 group-open:rotate-180"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>
                <p class="px-5 pb-5 text-gray-400 text-sm leading-relaxed">{{ $item['a'] }}</p>
            </details>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-16 md:py-24" aria-labelledby="cta-heading">
    <div class="max-w-2xl mx-auto px-4 text-center">
        <h2 id="cta-heading" class="text-2xl md:text-3xl font-black text-white mb-4">
            デリヘル・風俗店の無料掲載、今すぐ始めませんか？
        </h2>
        <p class="text-gray-400 text-sm mb-8 leading-relaxed">
            月額0円・登録費0円で、店舗情報・女性求人・スタッフ求人をまとめて掲載できます。<br>
            登録5分、最短即日でデリヘルリストに掲載スタート。
        </p>
        <a href="{{ route('register') }}"
           class="inline-block bg-teal-500 hover:bg-teal-400 text-gray-950 font-black text-lg px-12 py-5 rounded-2xl transition shadow-xl shadow-teal-900/30">
            無料で掲載を始める
        </a>
        <p class="mt-4 text-xs text-gray-500">違約金なし・いつでも退会可能</p>
    </div>
</section>

</main>

<footer role="contentinfo" class="bg-gray-950 border-t border-gray-800 py-8">
    <div class="max-w-5xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-4">
        <a href="{{ url('/') }}" class="text-sm font-black text-white hover:text-teal-300 transition">デリヘルリスト</a>
        <nav aria-label="フッターナビゲーション" class="flex flex-wrap justify-center gap-x-6 gap-y-2 text-xs text-gray-500">
            <a href="{{ url('/privacy/') }}" class="hover:text-white transition">プライバシーポリシー</a>
            <a href="{{ url('/terms/') }}" class="hover:text-white transition">利用規約</a>
            <a href="{{ url('/tokutei/') }}" class="hover:text-white transition">特定商取引法</a>
            <a href="{{ url('/company/') }}" class="hover:text-white transition">運営会社</a>
        </nav>
        <p class="text-xs text-gray-600">&copy; {{ date('Y') }} デリヘルリスト</p>
    </div>
</footer>

</body>
</html>
