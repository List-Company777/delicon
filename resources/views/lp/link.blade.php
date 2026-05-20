<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>バナー設置で上位表示キャンペーン｜デリヘルリスト</title>
    <meta name="description" content="公式サイトのトップページにデリヘルリストのバナーを設置し、公開申請いただいた店舗様を検索結果で優先的に上位表示いたします。">
    <link rel="canonical" href="{{ url('/link/') }}">
    <meta property="og:title" content="バナー設置で上位表示キャンペーン｜デリヘルリスト">
    <meta property="og:description" content="公式サイトのトップにデリヘルリストのバナーを設置して公開申請すると、検索上位に優先表示されます。">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/link/') }}">
    @vite(['resources/css/app.css'])
    <style>
        .gradient-hero {
            background: linear-gradient(135deg, #0a0a14 0%, #12102a 50%, #1a0d1a 100%);
        }
        .code-block {
            background: #0d1117;
            color: #a5d6ff;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.6;
            border: 1px solid #30363d;
            border-radius: 8px;
            padding: 14px;
            white-space: pre-wrap;
            word-break: break-all;
            cursor: text;
            user-select: all;
        }
        .code-block:focus { outline: 2px solid #C8A450; }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 font-sans">

{{-- ヘッダー --}}
<header class="bg-gray-900/90 backdrop-blur-sm border-b border-gray-800 sticky top-0 z-50">
    <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="{{ url('/') }}" class="flex items-center gap-2">
            <span class="text-lg font-black tracking-tight text-white">デリヘルリスト</span>
        </a>
        <a href="{{ route('register') }}"
           class="bg-[#C8A450] hover:bg-[#d4b264] text-gray-950 text-sm font-bold px-4 py-2 rounded-lg transition">
            無料で掲載する
        </a>
    </div>
</header>

<main>

{{-- ヒーロー --}}
<section class="gradient-hero py-16 md:py-24">
    <div class="max-w-3xl mx-auto px-4 text-center">
        <div class="inline-block border border-[#C8A450]/50 rounded-full px-5 py-1.5 text-[#C8A450] text-xs font-bold mb-6 tracking-widest">
            無料参加・掲載店舗限定
        </div>
        <h1 class="text-3xl md:text-4xl font-black leading-tight mb-6 text-white">
            バナー設置で<br>
            <span class="text-[#C8A450]">検索順位を優遇します</span>
        </h1>
        <p class="text-gray-300 text-base md:text-lg leading-relaxed max-w-2xl mx-auto mb-8">
            貴店の公式サイト（オフィシャルHP）のトップページに<br>
            デリヘルリストのバナーを設置し、<strong class="text-white">公開申請</strong>いただくと、<br>
            デリヘルリスト内の検索結果で優先的に上位表示いたします。
        </p>
        <p class="text-sm text-gray-500">参加費無料・デリヘルリスト登録店舗様限定</p>
    </div>
</section>

{{-- 仕組み --}}
<section class="py-12 border-b border-gray-800">
    <div class="max-w-3xl mx-auto px-4">
        <h2 class="text-xl font-bold text-white text-center mb-8">参加の流れ</h2>
        <div class="grid md:grid-cols-3 gap-6 text-center">
            <div class="bg-gray-900 rounded-xl p-6 border border-gray-800">
                <div class="text-3xl font-black text-[#C8A450] mb-3">01</div>
                <p class="font-bold text-white mb-2">サイトに登録</p>
                <p class="text-sm text-gray-400">デリヘルリストに店舗アカウントを作成し、店舗情報を登録してください</p>
            </div>
            <div class="bg-gray-900 rounded-xl p-6 border border-gray-800">
                <div class="text-3xl font-black text-[#C8A450] mb-3">02</div>
                <p class="font-bold text-white mb-2">バナーを設置</p>
                <p class="text-sm text-gray-400">公式サイトのトップページ（index.html等）に下記のHTMLコードを貼り付ける</p>
            </div>
            <div class="bg-gray-900 rounded-xl p-6 border border-gray-800 border-[#C8A450]/40 relative">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#C8A450] text-gray-950 text-xs font-bold px-3 py-0.5 rounded-full">最後にこれ</div>
                <div class="text-3xl font-black text-[#C8A450] mb-3">03</div>
                <p class="font-bold text-white mb-2">公開申請を送信</p>
                <p class="text-sm text-gray-400">管理画面から公開申請を送信してください。バナー設置を確認後、上位表示を適用します</p>
            </div>
        </div>
    </div>
</section>

{{-- バナー設置コード --}}
<section class="py-12 border-b border-gray-800" id="banner">
    <div class="max-w-3xl mx-auto px-4">
        <h2 class="text-xl font-bold text-white text-center mb-2">バナー設置用HTMLコード</h2>
        <p class="text-sm text-gray-400 text-center mb-10">いずれか1つを選んで公式サイトのトップページに貼り付けてください</p>

        {{-- 468x60 --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-bold text-gray-300">468 × 60（標準バナー）</p>
                <span class="text-xs text-[#C8A450] border border-[#C8A450]/40 rounded px-2 py-0.5">推奨</span>
            </div>
            <div class="flex justify-center bg-gray-800 rounded-lg p-4 mb-4 overflow-x-auto">
                <a href="https://delicon.jp/" target="_blank" rel="noopener">
                    <img src="https://delicon.mm-mv.net/banner/dcbn_468x60.gif" width="468" height="60" alt="デリヘルリスト" loading="lazy">
                </a>
            </div>
            <p class="text-xs text-gray-500 mb-2">HTMLコード（クリックで全選択）</p>
            <textarea readonly rows="4" class="code-block w-full resize-none" onclick="this.select()"
><a href="https://delicon.jp/" target="_blank" rel="noopener">
<img src="https://delicon.mm-mv.net/banner/dcbn_468x60.gif" width="468" height="60" alt="デリヘルリスト">
</a></textarea>
        </div>

        {{-- 234x60 --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 mb-6">
            <p class="text-sm font-bold text-gray-300 mb-4">234 × 60（ハーフバナー）</p>
            <div class="flex justify-center bg-gray-800 rounded-lg p-4 mb-4">
                <a href="https://delicon.jp/" target="_blank" rel="noopener">
                    <img src="https://delicon.mm-mv.net/banner/dcbn_234x60.gif" width="234" height="60" alt="デリヘルリスト" loading="lazy">
                </a>
            </div>
            <p class="text-xs text-gray-500 mb-2">HTMLコード（クリックで全選択）</p>
            <textarea readonly rows="4" class="code-block w-full resize-none" onclick="this.select()"
><a href="https://delicon.jp/" target="_blank" rel="noopener">
<img src="https://delicon.mm-mv.net/banner/dcbn_234x60.gif" width="234" height="60" alt="デリヘルリスト">
</a></textarea>
        </div>

        {{-- 200x40 --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 mb-6">
            <p class="text-sm font-bold text-gray-300 mb-4">200 × 40（スモールバナー）</p>
            <div class="flex justify-center bg-gray-800 rounded-lg p-4 mb-4">
                <a href="https://delicon.jp/" target="_blank" rel="noopener">
                    <img src="https://delicon.mm-mv.net/banner/dcbn_200x40.gif" width="200" height="40" alt="デリヘルリスト" loading="lazy">
                </a>
            </div>
            <p class="text-xs text-gray-500 mb-2">HTMLコード（クリックで全選択）</p>
            <textarea readonly rows="4" class="code-block w-full resize-none" onclick="this.select()"
><a href="https://delicon.jp/" target="_blank" rel="noopener">
<img src="https://delicon.mm-mv.net/banner/dcbn_200x40.gif" width="200" height="40" alt="デリヘルリスト">
</a></textarea>
        </div>

        {{-- 88x31 --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <p class="text-sm font-bold text-gray-300 mb-4">88 × 31（ボタンバナー）</p>
            <div class="flex justify-center bg-gray-800 rounded-lg p-4 mb-4">
                <a href="https://delicon.jp/" target="_blank" rel="noopener">
                    <img src="https://delicon.mm-mv.net/banner/dcbn_88x31.gif" width="88" height="31" alt="デリヘルリスト" loading="lazy">
                </a>
            </div>
            <p class="text-xs text-gray-500 mb-2">HTMLコード（クリックで全選択）</p>
            <textarea readonly rows="4" class="code-block w-full resize-none" onclick="this.select()"
><a href="https://delicon.jp/" target="_blank" rel="noopener">
<img src="https://delicon.mm-mv.net/banner/dcbn_88x31.gif" width="88" height="31" alt="デリヘルリスト">
</a></textarea>
        </div>
    </div>
</section>

{{-- 設置ルール --}}
<section class="py-12 border-b border-gray-800">
    <div class="max-w-3xl mx-auto px-4">
        <h2 class="text-xl font-bold text-white text-center mb-8">設置ルール・注意事項</h2>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
            <ol class="space-y-4 text-sm text-gray-300">
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[#C8A450]/20 text-[#C8A450] text-xs font-bold flex items-center justify-center">1</span>
                    <span>バナーは必ず貴店の<strong class="text-white">公式サイトのトップページ</strong>（index.html等、最初に表示されるページ）に設置してください。下層ページのみの設置は対象外です。</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[#C8A450]/20 text-[#C8A450] text-xs font-bold flex items-center justify-center">2</span>
                    <span>バナーの<strong class="text-white">リンク先URL・画像の変更・加工はご遠慮ください</strong>。発見次第、優遇措置を解除いたします。</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[#C8A450]/20 text-[#C8A450] text-xs font-bold flex items-center justify-center">3</span>
                    <span>バナー設置後、<strong class="text-white">管理画面から公開申請を送信</strong>してください。弊社にて公式サイトのバナー設置を確認後、上位表示の優遇措置を適用いたします。</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[#C8A450]/20 text-[#C8A450] text-xs font-bold flex items-center justify-center">4</span>
                    <span>バナーが削除・非表示になった場合は、優遇措置を予告なく解除いたします。</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[#C8A450]/20 text-[#C8A450] text-xs font-bold flex items-center justify-center">5</span>
                    <span>不正な設置（画像のみ設置でリンクなし、iframe内での非表示設置など）は対象外となります。</span>
                </li>
            </ol>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-16">
    <div class="max-w-3xl mx-auto px-4 text-center">
        <h2 class="text-xl font-bold text-white mb-3">まずはデリヘルリストに登録を</h2>
        <p class="text-sm text-gray-400 mb-8">
            アカウントを作成し、店舗情報を登録してください。<br>
            その後、バナーを設置して管理画面から公開申請を送信いただくと上位表示が適用されます。
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}"
               class="bg-[#C8A450] hover:bg-[#d4b264] text-gray-950 font-black text-base px-8 py-4 rounded-xl transition">
                無料で店舗を登録する
            </a>
            <a href="{{ url('/login/') }}"
               class="border border-gray-600 hover:border-[#C8A450] text-gray-300 hover:text-[#C8A450] font-bold text-base px-8 py-4 rounded-xl transition">
                すでに登録済みの方はログイン
            </a>
        </div>
    </div>
</section>

</main>

<footer class="bg-gray-900 border-t border-gray-800 py-6 text-center">
    <p class="text-xs text-gray-600 mb-2">
        ※ 上位表示はデリヘルリスト内の検索結果における優遇措置であり、特定の順位を保証するものではありません。<br>
        ※ キャンペーン内容は予告なく変更・終了する場合があります。
    </p>
    <p class="text-xs text-gray-700 mt-4">
        <a href="{{ url('/') }}" class="hover:text-gray-400 transition">デリヘルリスト</a>
        &nbsp;|&nbsp;
        <a href="{{ route('privacy') }}" class="hover:text-gray-400 transition">プライバシーポリシー</a>
    </p>
</footer>

</body>
</html>
