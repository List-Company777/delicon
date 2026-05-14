<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>夜ビズ｜ナイトビジネス運営支援プラットフォーム</title>
    <meta name="description" content="夜ビズは、キャバクラ・ホストクラブ・ガールズバーなどのナイトビジネス向け無料掲載・求人プラットフォームです。集客情報の掲載・キャスト求人・スタッフ求人が基本無料。">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/yorubiz/') . '/' }}">
    <meta property="og:title" content="夜ビズ｜ナイトビジネス運営支援プラットフォーム">
    <meta property="og:description" content="集客情報の掲載・キャスト求人・スタッフ求人が基本無料。ナイトビジネスの売上アップと採用をまとめてサポート。">
    <meta property="og:url" content="/">
    <meta property="og:type" content="website">
    @vite(['resources/css/app.css'])
    <style>
        .gradient-yoru {
            background: linear-gradient(135deg, #0C2B30 0%, #0B132B 50%, #451226 100%);
        }
        .card-glow:hover {
            box-shadow: 0 0 24px rgba(176, 208, 211, 0.15);
        }
        .badge-free {
            background: linear-gradient(135deg, #B0D0D3, #4ECDC4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 font-sans">

{{-- ヘッダー --}}
<header class="bg-gray-900/90 backdrop-blur-sm border-b border-gray-800 sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-xl font-black tracking-tight text-white">夜ビズ</span>
            <span class="hidden sm:inline text-xs text-gray-400 border border-gray-700 rounded px-2 py-0.5">ナイトビジネス運営支援</span>
        </div>
        <a href="#register"
           class="bg-business-700 hover:bg-business-600 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
            無料で登録する
        </a>
    </div>
</header>

{{-- ヒーロー --}}
<section class="gradient-yoru py-16 md:py-24">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <div class="inline-block bg-business-900/60 border border-business-700/40 rounded-full px-4 py-1 text-business-300 text-xs font-bold mb-6 tracking-widest uppercase">
            Night Business Platform
        </div>
        <h1 class="text-3xl md:text-5xl font-black leading-tight mb-6 text-white">
            ナイトビジネスの<br>
            <span class="text-teal-300">集客・採用</span>を、<br class="md:hidden">
            まとめて無料で。
        </h1>
        <p class="text-gray-300 text-base md:text-lg leading-relaxed max-w-2xl mx-auto mb-10">
            夜ビズは、キャバクラ・ホストクラブ・ガールズバーなどのナイトビジネス向け掲載プラットフォームです。<br>
            <strong class="text-white">集客情報の掲載・キャスト求人・スタッフ求人が基本無料</strong>。登録5分で集客・採用をスタートできます。
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="#register"
               class="bg-business-700 hover:bg-business-600 text-white font-black py-4 px-10 rounded-xl text-lg transition shadow-lg">
                今すぐ無料登録
            </a>
            <a href="#features"
               class="border border-gray-600 hover:border-gray-400 text-gray-300 hover:text-white font-bold py-4 px-10 rounded-xl text-lg transition">
                サービス詳細を見る
            </a>
        </div>
    </div>
</section>

{{-- 3つの無料機能 --}}
<section id="features" class="py-16 md:py-20 bg-gray-900">
    <div class="max-w-5xl mx-auto px-4">
        <div class="text-center mb-12">
            <p class="text-business-300 text-sm font-bold tracking-widest uppercase mb-3">Free Features</p>
            <h2 class="text-2xl md:text-3xl font-black text-white">3つの機能が<span class="text-business-300">基本無料</span></h2>
            <p class="text-gray-400 mt-3 text-sm">登録するだけで、売上アップのための集客と採用の両面をカバーします。</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- コンセプト掲載 --}}
            <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 card-glow transition">
                <div class="w-12 h-12 rounded-xl bg-business-900 border border-business-700/50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-business-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div class="text-xs font-bold text-business-300 mb-1 tracking-widest">FREE</div>
                <h3 class="text-lg font-bold text-white mb-2">集客情報の掲載</h3>
                <p class="text-gray-400 text-sm leading-relaxed">店舗情報・料金プラン・セット内容をページとして無料掲載。エリア・業種から夜遊びを探すユーザーへ直接アプローチし、来店につなげます。</p>
            </div>

            {{-- キャスト求人 --}}
            <div class="bg-gray-800 rounded-2xl p-6 border border-female-700/40 card-glow transition">
                <div class="w-12 h-12 rounded-xl bg-female-900/50 border border-female-700/40 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-female-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="text-xs font-bold text-female-400 mb-1 tracking-widest">FREE</div>
                <h3 class="text-lg font-bold text-white mb-2">キャスト求人掲載</h3>
                <p class="text-gray-400 text-sm leading-relaxed">キャバ嬢・ホスト・コンカフェ嬢など、フロントスタッフの求人を無料掲載。体験入店や日払い条件など詳細に記載でき、応募フォームが自動設置されます。</p>
            </div>

            {{-- スタッフ求人 --}}
            <div class="bg-gray-800 rounded-2xl p-6 border border-male-700/40 card-glow transition">
                <div class="w-12 h-12 rounded-xl bg-male-900/50 border border-male-700/40 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-male-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div class="text-xs font-bold text-male-300 mb-1 tracking-widest">FREE</div>
                <h3 class="text-lg font-bold text-white mb-2">スタッフ求人掲載</h3>
                <p class="text-gray-400 text-sm leading-relaxed">黒服・送迎ドライバー・受付・WEBスタッフなど、バックオフィス・運営スタッフの採用も無料で。複数職種を同時掲載できます。</p>
            </div>

        </div>

        {{-- 補足 --}}
        <div class="mt-8 bg-gray-800/50 border border-gray-700 rounded-xl p-5 text-sm text-gray-400 leading-relaxed">
            <span class="text-white font-bold">有料オプション（任意）：</span>
            入札制の優先表示・ホットリンク（公式サイト直リンク）など、より上位への露出強化オプションもご用意しています。基本機能は<strong class="text-business-300">完全無料</strong>でご利用いただけます。
        </div>
    </div>
</section>

{{-- 夜ビズが選ばれる理由 --}}
<section class="py-16 md:py-20 bg-gray-950">
    <div class="max-w-5xl mx-auto px-4">
        <div class="text-center mb-12">
            <p class="text-business-300 text-sm font-bold tracking-widest uppercase mb-3">Why YORUBIZ</p>
            <h2 class="text-2xl md:text-3xl font-black text-white">店舗に選ばれる<span class="text-business-300"> 4つの理由</span></h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            {{-- SEO --}}
            <div class="bg-gray-900 rounded-2xl p-6 border border-gray-800">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-business-900 border border-business-700/40 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-business-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white mb-1">エリア×業種でSEO上位を狙える</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">Indeedと同様のSEO構造を採用。「新宿 キャバクラ 求人」などのエリア×業種×職種の組み合わせで検索上位に表示されやすい設計です。</p>
                    </div>
                </div>
            </div>

            {{-- LINE通知 --}}
            <div class="bg-gray-900 rounded-2xl p-6 border border-gray-800">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-business-900 border border-business-700/40 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-business-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white mb-1">応募通知をメール＋LINEで受け取れる</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">求人への応募があった瞬間に、メールとLINEに通知が届きます。見逃しゼロ。スマホだけで採用管理が完結します。</p>
                    </div>
                </div>
            </div>

            {{-- 早期登録 --}}
            <div class="bg-gray-900 rounded-2xl p-6 border border-gray-800">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-business-900 border border-business-700/40 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-business-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white mb-1">早期登録が掲載順位の永続的な優位性に</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">無料プラン内では先着登録順が掲載順位に反映されます。今登録するほど、後発店舗より常に上位に表示されます。</p>
                    </div>
                </div>
            </div>

            {{-- ホットリンク --}}
            <div class="bg-gray-900 rounded-2xl p-6 border border-gray-800">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-business-900 border border-business-700/40 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-business-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white mb-1">自社サイトへの集客ブースターになる</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">有料のホットリンク機能を使えば、自社HPや他媒体に直接誘導できます。今使っている媒体を継続しながら、集客の入口を増やせます。</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- 業種別 登録導線 --}}
<section id="register" class="py-16 md:py-20 bg-gray-950">
    <div class="max-w-5xl mx-auto px-4">
        <div class="text-center mb-12">
            <p class="text-business-300 text-sm font-bold tracking-widest uppercase mb-3">Get Started</p>
            <h2 class="text-2xl md:text-3xl font-black text-white">業種に合わせて<span class="text-business-300">登録サイトを選択</span></h2>
            <p class="text-gray-400 mt-3 text-sm">業種ごとに特化したサイトで掲載・採用管理ができます。</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- デリヘルリスト --}}
            <div class="bg-gray-900 border border-business-700/40 rounded-2xl overflow-hidden card-glow transition">
                <div class="bg-business-900/60 border-b border-business-700/30 px-6 py-4">
                    <div class="flex items-center gap-3 mb-1">
                        <span class="text-2xl font-black text-white">ナイトワーク<span class="text-business-300">リスト</span></span>
                    </div>
                    <p class="text-business-300 text-xs font-bold">delicon.jp</p>
                </div>
                <div class="px-6 py-5">
                    <p class="text-gray-300 text-sm leading-relaxed mb-5">
                        アルコール提供・接客を伴うナイトエンターテインメント業態向け。<br>
                        集客情報（料金・セット内容・店舗紹介）も合わせて掲載できます。
                    </p>
                    <div class="mb-5">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">対象業種（下記はすべてこちら）</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach([
                                'キャバクラ', 'ガールズバー', 'コンカフェ', 'ラウンジ',
                                'スナック', 'バー', 'パブ', 'ホストクラブ',
                                'ボーイズバー', 'メンズコンカフェ', 'クラブ',
                                'ニューハーフ', '無料案内所',
                            ] as $g)
                            <span class="bg-business-900/50 border border-business-700/40 text-business-300 text-xs px-2.5 py-1 rounded-full">{{ $g }}</span>
                            @endforeach
                        </div>
                    </div>
                    <a href="https://delicon.jp/register/"
                       rel="noopener noreferrer"
                       class="block w-full text-center bg-business-700 hover:bg-business-600 text-white font-black py-3.5 rounded-xl transition text-base">
                        デリヘルリストに無料登録
                    </a>
                    <p class="text-center text-xs text-gray-500 mt-2">登録・掲載・求人投稿 すべて無料</p>
                </div>
            </div>

            {{-- 風俗リスト --}}
            <div class="bg-gray-900 border border-female-700/30 rounded-2xl overflow-hidden card-glow transition">
                <div class="bg-female-900/30 border-b border-female-700/20 px-6 py-4">
                    <div class="flex items-center gap-3 mb-1">
                        <span class="text-2xl font-black text-white">風俗<span class="text-female-400">リスト</span></span>
                    </div>
                    <p class="text-female-400 text-xs font-bold">fuzoku-list.com</p>
                </div>
                <div class="px-6 py-5">
                    <p class="text-gray-300 text-sm leading-relaxed mb-5">
                        左記（デリヘルリスト）以外の業態向け特化サイト。<br>
                        性風俗・アドルト系の表現・検索機能に対応します。
                    </p>
                    <div class="mb-5">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">対象業種（例）</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach([
                                'ソープランド', 'ファッションヘルス', 'デリヘル', 'ホテヘル',
                                'メンズエステ', 'セクキャバ', '出会いカフェ', '交際クラブ',
                                '女性用風俗', '売り専', 'ニューハーフ（風俗）', 'その他アドルト系',
                            ] as $g)
                            <span class="bg-female-900/30 border border-female-700/30 text-female-400 text-xs px-2.5 py-1 rounded-full">{{ $g }}</span>
                            @endforeach
                        </div>
                    </div>
                    <a href="https://fuzoku-list.com/register/"
                       rel="noopener noreferrer"
                       class="block w-full text-center bg-female-700 hover:bg-female-600 text-white font-black py-3.5 rounded-xl transition text-base">
                        風俗リストに無料登録
                    </a>
                    <p class="text-center text-xs text-gray-500 mt-2">登録・掲載・求人投稿 すべて無料</p>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- 登録フロー --}}
<section class="py-16 bg-gray-900">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-12">
            <p class="text-business-300 text-sm font-bold tracking-widest uppercase mb-3">How It Works</p>
            <h2 class="text-2xl md:text-3xl font-black text-white">登録から掲載まで<span class="text-business-300"> 5分</span></h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="w-12 h-12 rounded-full bg-business-700 text-white font-black text-lg flex items-center justify-center mx-auto mb-4">1</div>
                <h3 class="font-bold text-white mb-2">アカウント登録</h3>
                <p class="text-gray-400 text-sm">メールアドレスとパスワードのみ。SNS情報や個人情報は不要です。</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 rounded-full bg-business-700 text-white font-black text-lg flex items-center justify-center mx-auto mb-4">2</div>
                <h3 class="font-bold text-white mb-2">店舗情報を入力</h3>
                <p class="text-gray-400 text-sm">店舗名・エリア・業種・コンセプトなどをフォームに入力するだけ。</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 rounded-full bg-business-700 text-white font-black text-lg flex items-center justify-center mx-auto mb-4">3</div>
                <h3 class="font-bold text-white mb-2">掲載 &amp; 求人スタート</h3>
                <p class="text-gray-400 text-sm">即時掲載。求人も管理画面から追加・編集が自由にできます。</p>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="py-16 md:py-20 bg-gray-950">
    <div class="max-w-3xl mx-auto px-4">
        <div class="text-center mb-10">
            <p class="text-business-300 text-sm font-bold tracking-widest uppercase mb-3">FAQ</p>
            <h2 class="text-2xl font-black text-white">よくある質問</h2>
        </div>
        <div class="space-y-4">
            @foreach([
                ['本当に無料で使えますか？', '基本機能（店舗掲載・キャスト求人・スタッフ求人）はすべて無料です。任意の有料オプション（優先表示・ホットリンク）もありますが、無料のままご利用いただけます。'],
                ['1つの店舗で両方のサイトに登録できますか？', '業種によってサイトが分かれています。対象業種が複数のサイトに該当する場合は、それぞれのサイトに別々のアカウントで登録可能です。'],
                ['求人応募の通知はどこで受け取れますか？', '登録されたメールアドレスとLINEへ応募通知が届きます（LINE通知は管理画面で設定できます）。'],
                ['途中でサービスを解約・退会できますか？', '管理画面からいつでも退会・掲載停止が可能です。違約金などは一切ありません。'],
                ['代理店・管理代行として複数店舗を管理できますか？', '代理店アカウントをご用意しています。複数店舗の一括管理・応募確認が可能です。お問い合わせください。'],
            ] as [$q, $a])
            <details class="bg-gray-900 border border-gray-800 rounded-xl group">
                <summary class="px-5 py-4 cursor-pointer list-none flex items-center justify-between gap-4">
                    <span class="font-bold text-white text-sm">{{ $q }}</span>
                    <span class="text-gray-500 group-open:rotate-180 transition-transform shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </summary>
                <div class="px-5 pb-4 text-gray-400 text-sm leading-relaxed border-t border-gray-800 pt-4">{{ $a }}</div>
            </details>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-16 gradient-yoru">
    <div class="max-w-2xl mx-auto px-4 text-center">
        <h2 class="text-2xl md:text-3xl font-black text-white mb-4">まずは無料で<span class="text-business-300">始めてみませんか</span></h2>
        <p class="text-gray-300 text-sm mb-8">クレジットカード不要。登録5分。いつでも退会可能。</p>
        <a href="https://delicon.jp/register/"
           rel="noopener noreferrer"
           class="inline-block bg-business-700 hover:bg-business-600 text-white font-black py-4 px-12 rounded-xl text-lg transition shadow-lg">
            デリヘルリストに無料登録
        </a>
        <p class="text-gray-400 text-xs mt-4">風俗系の業態は <a href="https://fuzoku-list.com/register/" rel="noopener noreferrer" class="underline hover:text-white transition">風俗リスト（fuzoku-list.com）</a> へ</p>
    </div>
</section>

{{-- フッター --}}
<footer class="bg-gray-900 border-t border-gray-800 py-8">
    <div class="max-w-5xl mx-auto px-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500">
            <div class="flex items-center gap-2">
                <span class="font-black text-white">夜ビズ</span>
                <span class="text-gray-500">— ナイトビジネス運営支援プラットフォーム</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="https://delicon.jp/" class="hover:text-gray-300 transition">デリヘルリスト</a>
                <a href="https://fuzoku-list.com/" rel="noopener noreferrer" class="hover:text-gray-300 transition">風俗リスト</a>
                <a href="{{ route('inquiry') }}/" class="hover:text-gray-300 transition">お問い合わせ</a>
                <a href="{{ route('privacy') }}/" class="hover:text-gray-300 transition">プライバシーポリシー</a>
            </div>
        </div>
        <p class="text-center text-gray-500 text-xs mt-6">&copy; {{ date('Y') }} 夜ビズ / デリヘルリスト. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
