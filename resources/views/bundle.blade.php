<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2サイトセット掲載 | デリヘルリスト × 風俗メンズバ！</title>
    <meta name="description" content="デリヘルリスト（delicon.jp）と風俗メンズバ！（mens-v.com）の2サイトセット掲載プランのご案内。1サイト分の料金で2サイトに掲載できます。">
    <meta name="robots" content="noindex,follow">
    @vite(['resources/css/app.css'])
    <style>
        body { background-color: #1A1A2E; }
        .hero-gradient { background: linear-gradient(135deg, #1A1A2E 0%, #1A2828 50%, #1A1A2E 100%); }
        .mv-accent { color: #4A9EBF; }
    </style>
</head>
<body class="text-[#E8E4DC] font-sans antialiased">

{{-- ヘッダー --}}
<header class="bg-[#12121E]/90 backdrop-blur-sm border-b border-[#2A2A3E] sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}/" class="text-xl font-black tracking-tight text-white">デリヘルリスト</a>
            <span class="text-xs text-[#8A8A9E] border border-[#2A2A3E] rounded px-2 py-0.5 hidden sm:inline">× 風俗メンズバ！ セット掲載</span>
        </div>
        <a href="{{ route('inquiry') }}"
           class="bg-deli-500 hover:bg-deli-400 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
            掲載のお問い合わせ →
        </a>
    </div>
</header>

{{-- ヒーロー --}}
<section class="hero-gradient py-16 md:py-24 border-b border-[#2A2A3E]">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <div class="inline-block bg-deli-900/40 border border-deli-700/40 rounded-full px-4 py-1 text-deli-300 text-xs font-bold mb-6 tracking-widest">
            SET PLAN
        </div>
        <h1 class="text-2xl sm:text-3xl md:text-5xl font-black leading-tight mb-6">
            <span class="text-deli-400">デリヘルリスト</span> × <span class="mv-accent">風俗メンズバ！</span><br class="hidden sm:block">
            2サイト同時掲載
        </h1>
        <p class="text-[#B0AEAD] text-base md:text-lg leading-relaxed max-w-2xl mx-auto mb-8">
            歴史ある2つのドメインに同時掲載。<br class="hidden md:inline">
            <strong class="text-white">1サイト分の料金で、2倍の検索露出</strong>を実現します。
        </p>
        <div class="inline-block bg-deli-500/10 border border-deli-500/40 rounded-2xl px-8 py-4">
            <p class="text-deli-300 text-sm font-bold mb-1">セット特別価格</p>
            <p class="text-white text-2xl font-black">1サイト分の料金で2サイト掲載</p>
        </div>
    </div>
</section>

{{-- SEO実績バナー --}}
<section class="border-b border-[#2A2A3E] bg-[#12121E]/60">
    <div class="max-w-4xl mx-auto px-4 py-10 grid sm:grid-cols-2 gap-6">
        {{-- delicon --}}
        <div class="bg-[#1E1E30] border border-deli-700/40 rounded-2xl p-6">
            <p class="text-deli-400 text-xs font-bold tracking-widest mb-4">DELICON.JP → デリヘルリスト</p>
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <span class="text-2xl shrink-0">📅</span>
                    <div>
                        <p class="text-white font-black text-lg leading-snug">ドメインエイジ <span class="text-deli-400 text-2xl">約21年</span></p>
                        <p class="text-[#8A8A9E] text-sm">2004年から運営。積み上げたSEO資産をそのまま活用できます。</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-2xl shrink-0">📈</span>
                    <div>
                        <p class="text-white font-black text-lg leading-snug">月間 <span class="text-deli-400 text-2xl">1万クリック以上</span></p>
                        <p class="text-[#8A8A9E] text-sm">Googleからの検索流入。デリヘル・風俗系キーワードで安定した集客。</p>
                    </div>
                </div>
            </div>
        </div>
        {{-- mensvalue --}}
        <div class="bg-[#1A2A32] border rounded-2xl p-6" style="border-color: rgba(74,158,191,0.4)">
            <p class="mv-accent text-xs font-bold tracking-widest mb-4">MENS-V.COM → 風俗メンズバ！</p>
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <span class="text-2xl shrink-0">📅</span>
                    <div>
                        <p class="text-white font-black text-lg leading-snug">ドメインエイジ <span class="mv-accent text-2xl">約20年</span></p>
                        <p class="text-[#8A8A9E] text-sm">「風俗」キーワードをメインとした老舗ドメイン。幅広いジャンルをカバー。</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-2xl shrink-0">📈</span>
                    <div>
                        <p class="text-white font-black text-lg leading-snug">リニューアル <span class="mv-accent text-2xl">準備中</span></p>
                        <p class="text-[#8A8A9E] text-sm">新システムへ移行中。リニューアル後はさらなる検索流入増を見込んでいます。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="max-w-4xl mx-auto px-4 py-12 space-y-20">

    {{-- なぜ2サイトか --}}
    <section>
        <div class="flex items-center gap-4 mb-8">
            <span class="flex-shrink-0 w-10 h-10 rounded-full bg-deli-500 text-white font-black text-lg flex items-center justify-center">①</span>
            <div>
                <p class="text-deli-400 text-sm font-bold tracking-widest mb-0.5">WHY 2 SITES</p>
                <h2 class="text-2xl md:text-3xl font-black">「探し方が違うお客様」を両方取り込める</h2>
            </div>
        </div>
        <p class="text-[#B0AEAD] mb-8 leading-relaxed">
            2つのサイトは集まるユーザー層が異なります。セット掲載することで、<strong class="text-white">業種指名で探すお客様と、好みの女性から探すお客様</strong>、双方にアプローチできます。
        </p>
        <div class="grid sm:grid-cols-2 gap-6">
            <div class="bg-[#1E1E30] border border-deli-700/40 rounded-2xl p-6">
                <p class="text-deli-400 text-xs font-bold tracking-widest mb-2">デリヘルリスト</p>
                <p class="text-white font-black text-lg mb-1">デリヘル・メンズエステ<span class="text-deli-400">特化</span></p>
                <p class="text-[#8A8A9E] text-sm leading-relaxed mb-4">「○○でデリヘルを探したい」と業種を決めて検索するお客様が集まります。目的が明確なぶん、問い合わせ・来店につながりやすい層です。</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach(['デリヘル', 'デリバリーヘルス', 'ホテヘル', '店舗型ヘルス', 'メンズエステ', 'ソープランド'] as $kw)
                    <span class="text-xs bg-deli-900/60 text-deli-300 border border-deli-700/40 rounded-full px-2.5 py-1">{{ $kw }}</span>
                    @endforeach
                </div>
            </div>
            <div class="bg-[#1A2A32] border rounded-2xl p-6" style="border-color: rgba(74,158,191,0.4)">
                <p class="mv-accent text-xs font-bold tracking-widest mb-2">風俗メンズバ！</p>
                <p class="text-white font-black text-lg mb-1">風俗全般を<span class="mv-accent">カバー</span></p>
                <p class="text-[#8A8A9E] text-sm leading-relaxed mb-4">「業種は問わず、好みの女性で選びたい」というお客様が集まります。写真・スペック・口コミで比較しながら探す、新規ユーザーの獲得に強い媒体です。</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach(['風俗', 'メンズエステ', 'セクキャバ', 'JKリフレ', 'M性感', '風俗エステ', '交際クラブ'] as $kw)
                    <span class="text-xs rounded-full px-2.5 py-1" style="background:rgba(74,158,191,0.15); color:#4A9EBF; border:1px solid rgba(74,158,191,0.3)">{{ $kw }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- 料金プラン --}}
    <section>
        <div class="flex items-center gap-4 mb-8">
            <span class="flex-shrink-0 w-10 h-10 rounded-full bg-deli-500 text-white font-black text-lg flex items-center justify-center">②</span>
            <div>
                <p class="text-deli-400 text-sm font-bold tracking-widest mb-0.5">PRICING</p>
                <h2 class="text-2xl md:text-3xl font-black">料金プラン</h2>
            </div>
        </div>

        <div class="bg-deli-500/10 border border-deli-500/40 rounded-2xl p-5 mb-8 text-center">
            <p class="text-deli-300 font-bold text-lg">セットプランは <span class="text-white text-2xl">1サイト分の料金</span> で2サイトに掲載</p>
            <p class="text-[#8A8A9E] text-sm mt-1">通常の2サイト合計額の50%OFF。掲載露出は2倍以上になります。</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#2A2A3E]">
                        <th class="text-left py-3 px-4 text-[#6A6A7E] font-bold w-28">プラン</th>
                        <th class="text-center py-3 px-4 text-[#6A6A7E] font-bold">デリヘルリスト<br><span class="font-normal text-xs">単体</span></th>
                        <th class="text-center py-3 px-4 text-[#6A6A7E] font-bold">風俗メンズバ！<br><span class="font-normal text-xs">単体</span></th>
                        <th class="text-center py-3 px-4 text-deli-400 font-bold bg-deli-900/20 rounded-t-lg">2サイトセット<br><span class="font-normal text-xs text-deli-300">おすすめ</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2A2A3E]">
                    <tr>
                        <td class="py-4 px-4 font-bold text-[#E8E4DC]">無料</td>
                        <td class="py-4 px-4 text-center text-[#8A8A9E]">¥0</td>
                        <td class="py-4 px-4 text-center text-[#8A8A9E]">¥0</td>
                        <td class="py-4 px-4 text-center bg-deli-900/10 font-black text-white">¥0</td>
                    </tr>
                    <tr>
                        <td class="py-4 px-4 font-bold text-[#E8E4DC]">ベーシック</td>
                        <td class="py-4 px-4 text-center text-[#8A8A9E]">¥10,000</td>
                        <td class="py-4 px-4 text-center text-[#8A8A9E]">¥10,000</td>
                        <td class="py-4 px-4 text-center bg-deli-900/10">
                            <span class="text-deli-400 font-black text-lg">¥10,000</span>
                            <span class="block text-xs text-emerald-400 mt-0.5">¥10,000 お得</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-4 px-4 font-bold text-[#E8E4DC]">
                            ミドル
                            <span class="ml-1 text-xs bg-deli-900/60 text-deli-300 border border-deli-700/40 rounded px-1.5 py-0.5">人気</span>
                        </td>
                        <td class="py-4 px-4 text-center text-[#8A8A9E]">¥30,000</td>
                        <td class="py-4 px-4 text-center text-[#8A8A9E]">¥30,000</td>
                        <td class="py-4 px-4 text-center bg-deli-900/10">
                            <span class="text-deli-400 font-black text-lg">¥30,000</span>
                            <span class="block text-xs text-emerald-400 mt-0.5">¥30,000 お得</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-4 px-4 font-bold text-deli-300">VIP<br><span class="text-xs text-[#6A6A7E] font-normal">枠数限定</span></td>
                        <td class="py-4 px-4 text-center text-[#8A8A9E]">¥60,000</td>
                        <td class="py-4 px-4 text-center text-[#8A8A9E]">¥60,000</td>
                        <td class="py-4 px-4 text-center bg-deli-900/10">
                            <span class="text-deli-400 font-black text-lg">¥60,000</span>
                            <span class="block text-xs text-emerald-400 mt-0.5">¥60,000 お得</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-xs text-[#6A6A7E] mt-3">※料金はすべて税別表示です。セット価格は風俗メンズバ！のリニューアル完了後に適用開始となります。</p>
    </section>

    {{-- セットプランの掲載内容 --}}
    <section>
        <div class="flex items-center gap-4 mb-8">
            <span class="flex-shrink-0 w-10 h-10 rounded-full bg-deli-500 text-white font-black text-lg flex items-center justify-center">③</span>
            <div>
                <p class="text-deli-400 text-sm font-bold tracking-widest mb-0.5">FEATURES</p>
                <h2 class="text-2xl md:text-3xl font-black">セットプランの掲載内容</h2>
            </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            @php
                $features = [
                    ['icon' => '📋', 'title' => '店舗情報・女性プロフィール掲載', 'desc' => '2サイトにそれぞれ店舗ページを設置。情報は管理画面から一括で管理できます。'],
                    ['icon' => '🔔', 'title' => 'ユーザーへの自動通知', 'desc' => '新人登録・シフト登録でお気に入りユーザーに自動メール通知。追加費用ゼロで集客できます。'],
                    ['icon' => '📷', 'title' => '写メ日記・シフト管理', 'desc' => '女性ごとに写メ日記を投稿。シフトのカレンダー登録で「出勤中」タブに自動表示されます。'],
                    ['icon' => '🔗', 'title' => '自社HPへのリンク掲載', 'desc' => '2サイトから自社オフィシャルHPへリンク。SEO効果も期待できます。'],
                    ['icon' => '💬', 'title' => '口コミ・クーポン機能', 'desc' => '会員ユーザーからの口コミ収集。投稿者へ割引クーポンを送付してリピートを促進。'],
                    ['icon' => '📊', 'title' => 'アクセス解析・ランキング確認', 'desc' => '管理画面でお気に入り登録数・閲覧数・掲載順位を確認できます。'],
                ];
            @endphp
            @foreach($features as $f)
            <div class="bg-[#1E1E30] border border-[#2A2A3E] rounded-xl p-5 flex gap-4">
                <span class="text-2xl shrink-0 mt-0.5">{{ $f['icon'] }}</span>
                <div>
                    <p class="font-bold text-[#E8E4DC] mb-1">{{ $f['title'] }}</p>
                    <p class="text-sm text-[#8A8A9E] leading-relaxed">{{ $f['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-gradient-to-br from-deli-900/40 to-[#1E1E30] border border-deli-700/30 rounded-2xl p-8 md:p-12 text-center">
        <h2 class="text-2xl md:text-3xl font-black mb-4">まずはお気軽にご相談ください</h2>
        <p class="text-[#B0AEAD] mb-8 leading-relaxed max-w-xl mx-auto">
            現在の無料掲載からのプランアップグレード、新規掲載のご相談は<br class="hidden md:inline">
            お問い合わせフォームよりお気軽にどうぞ。
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('inquiry') }}"
               class="w-full sm:w-auto text-center bg-deli-500 hover:bg-deli-400 text-white font-bold px-8 py-3 rounded-xl transition">
                掲載のお問い合わせ
            </a>
            <a href="{{ route('features') }}"
               class="w-full sm:w-auto text-center bg-[#2A2A3E] hover:bg-[#34344E] text-[#E8E4DC] font-bold px-8 py-3 rounded-xl border border-[#3A3A4E] transition">
                デリヘルリストの機能を見る
            </a>
        </div>
    </section>

</div>

{{-- フッター --}}
<footer class="border-t border-[#2A2A3E] mt-16 py-8 text-center text-xs text-[#6A6A7E]">
    <p>&copy; {{ date('Y') }} デリヘルリスト</p>
</footer>

</body>
</html>
