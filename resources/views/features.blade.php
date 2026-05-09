<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>デリコンが全面リニューアル | デリヘルリスト</title>
    <meta name="description" content="デリコンが全面リニューアル。サイト名もデリヘルリストに変わります。ユーザー登録・充実した検索機能・店舗向け管理機能・SEO強化など、旧サイトからの主な追加機能をご紹介します。">
    <meta name="robots" content="noindex,follow">
    @vite(['resources/css/app.css'])
    <style>
        body { background-color: #1A1A2E; }
        .hero-gradient { background: linear-gradient(135deg, #1A1A2E 0%, #2A1828 50%, #1A1A2E 100%); }
        .section-num { font-size: 4rem; font-weight: 900; line-height: 1; opacity: .08; position: absolute; top: -0.5rem; left: -0.5rem; color: #C02040; }
    </style>
</head>
<body class="text-[#E8E4DC] font-sans antialiased">

{{-- ヘッダー --}}
<header class="bg-[#12121E]/90 backdrop-blur-sm border-b border-[#2A2A3E] sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}/" class="text-xl font-black tracking-tight text-white">デリヘルリスト</a>
            <span class="text-xs text-[#8A8A9E] border border-[#2A2A3E] rounded px-2 py-0.5 hidden sm:inline">新機能のご紹介</span>
        </div>
        <a href="{{ route('girl.list', ['area_slug' => 'all']) }}/"
           class="bg-deli-500 hover:bg-deli-400 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
            サイトを見る →
        </a>
    </div>
</header>

{{-- ヒーロー --}}
<section class="hero-gradient py-16 md:py-24 border-b border-[#2A2A3E]">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <div class="inline-block bg-deli-900/40 border border-deli-700/40 rounded-full px-4 py-1 text-deli-300 text-xs font-bold mb-6 tracking-widest">
            RENEWED
        </div>
        <h1 class="text-3xl md:text-5xl font-black leading-tight mb-6">
            デリコンが<span class="text-deli-400">全面リニューアル</span>。
        </h1>
        <p class="text-[#B0AEAD] text-base md:text-lg leading-relaxed max-w-2xl mx-auto">
            ユーザー向けの会員機能・充実した検索・店舗管理ツール・SEO強化など、<br class="hidden md:inline">
            旧サイトから大幅にパワーアップしました。主な追加・改善点をご紹介します。<br class="hidden md:inline">なお、サイト名も<strong class="text-white">「デリヘルリスト」</strong>に変わります。
        </p>
    </div>
</section>

<div class="max-w-4xl mx-auto px-4 py-12 space-y-28">

    {{-- ① ユーザー登録機能 --}}
    <section id="user">
        <div class="flex items-center gap-4 mb-8">
            <span class="flex-shrink-0 w-10 h-10 rounded-full bg-deli-500 text-white font-black text-lg flex items-center justify-center">①</span>
            <div>
                <p class="text-deli-400 text-sm font-bold tracking-widest mb-0.5">NEW FEATURE</p>
                <h2 class="text-2xl md:text-3xl font-black">ユーザー登録機能を追加</h2>
            </div>
        </div>
        <p class="text-[#B0AEAD] mb-8 leading-relaxed">
            無料の会員登録（ニックネーム＋メールアドレス）で、以下の機能が使えるようになります。
            登録することでサイトをより便利に使えるだけでなく、<strong class="text-[#E8E4DC]">店舗・女性への愛着が高まり、リピート率向上</strong>にも貢献します。
        </p>
        <div class="grid sm:grid-cols-2 gap-4">
            @php
                $userFeatures = [
                    ['icon' => '♡', 'title' => 'お気に入り登録',       'desc' => '気になる女性をお気に入り保存。マイページからすぐ確認できます。'],
                    ['icon' => '🔔', 'title' => '新人通知メール',       'desc' => '好みのタイプ・体型・エリアに合う新人が登録されたら自動でメール通知。お気に入り店舗の新人も通知されます。'],
                    ['icon' => '📅', 'title' => 'お気に入り出勤通知',   'desc' => 'お気に入りの女性がシフトを登録したら出勤予定をメールでお知らせ。'],
                    ['icon' => '👁', 'title' => '閲覧履歴',             'desc' => '過去に見た女性の履歴をマイページで確認できます。'],
                    ['icon' => '⭐', 'title' => '口コミの投稿・閲覧',         'desc' => '未登録ユーザーには口コミがぼかし表示。登録後に投稿・閲覧ができるようになります。'],
                    ['icon' => '⚙', 'title' => '好み設定',             'desc' => '年齢・タイプ・体型・エリア・曜日・時間帯の好みを設定してレコメンド表示。'],
                    ['icon' => '📍', 'title' => '店舗新人通知',         'desc' => '気になる店舗を通知登録。その店舗に新人が加わったらメールでお知らせ。'],
                ];
            @endphp
            @foreach($userFeatures as $f)
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

    {{-- ② 検索機能 --}}
    <section id="search" class="mt-9">
        <div class="flex items-center gap-4 mb-8">
            <span class="flex-shrink-0 w-10 h-10 rounded-full bg-deli-500 text-white font-black text-lg flex items-center justify-center">②</span>
            <div>
                <p class="text-deli-400 text-sm font-bold tracking-widest mb-0.5">ENHANCED SEARCH</p>
                <h2 class="text-2xl md:text-3xl font-black">検索機能が大幅に充実</h2>
            </div>
        </div>
        <p class="text-[#B0AEAD] mb-8 leading-relaxed">
            女性を探す方法が格段に増えました。タブ切り替えと絞り込みフィルターで、ユーザーが求める女性に素早くたどり着けます。
        </p>

        {{-- タブ検索 --}}
        <h3 class="text-sm font-bold text-[#8A8A9E] uppercase tracking-widest mb-4">タブ別検索</h3>
        <div class="grid sm:grid-cols-2 gap-4 mb-10">
            @php
                $searchTabs = [
                    ['icon' => '👩', 'title' => '女性一覧',   'desc' => 'エリア別に在籍女性を一覧表示。写真・年齢・スペックで一目で比較できます。'],
                    ['icon' => '🟢', 'title' => '出勤中',     'desc' => '本日出勤している女性を一覧表示。お客様がついている場合もありますが、出勤スケジュールを確認してから店舗に連絡できます。'],
                    ['icon' => '✨', 'title' => '新人',       'desc' => '入店から30日以内の新人女性を一覧。フレッシュな顔ぶれを見逃さない。'],
                    ['icon' => '📷', 'title' => '写メ日記',   'desc' => '女性が投稿した写メ日記を一覧表示。素顔や雰囲気が伝わりファン化しやすい。'],
                    ['icon' => '💬', 'title' => '口コミ',     'desc' => '会員が投稿した体験談・口コミを一覧で確認。信頼性の高い情報で選択できます。'],
                    ['icon' => '🏆', 'title' => '人気ランキング', 'desc' => '独自の基準で算出したスコアによる人気ランキングTOP30を掲載。今一番注目されている女性が一目でわかります。'],
                ];
            @endphp
            @foreach($searchTabs as $t)
            <div class="bg-[#1E1E30] border border-[#2A2A3E] rounded-xl p-5 flex gap-4">
                <span class="text-2xl shrink-0 mt-0.5">{{ $t['icon'] }}</span>
                <div>
                    <p class="font-bold text-[#E8E4DC] mb-1">{{ $t['title'] }}</p>
                    <p class="text-sm text-[#8A8A9E] leading-relaxed">{{ $t['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- 絞り込み --}}
        <h3 class="text-sm font-bold text-[#8A8A9E] uppercase tracking-widest mb-4">絞り込みフィルター</h3>
        <div class="bg-[#1E1E30] border border-[#2A2A3E] rounded-xl p-6">
            <div class="grid sm:grid-cols-2 gap-x-8 gap-y-3">
                @php
                    $filters = [
                        ['label' => '年齢',   'detail' => '10代〜70代以上（熟女・還暦・おばあちゃんなどの専用ページあり）'],
                        ['label' => '身長',   'detail' => '〜150cm・151〜160・161〜170・171cm〜（長身・小柄の専用ページあり）'],
                        ['label' => 'カップ', 'detail' => 'A・B / C / D / E・F / G以上'],
                        ['label' => '体型',   'detail' => '巨乳・爆乳・貧乳・スレンダー・グラマー・ちょいポチャ・激ポチャ・美乳・美脚など'],
                        ['label' => '特集LP', 'detail' => '熟女系・超熟女・人妻系・モデル系・ニューハーフ・ドM・ギャル・清楚系・AV女優'],
                    ];
                @endphp
                @foreach($filters as $f)
                <div class="flex gap-3">
                    <span class="text-deli-400 font-bold text-sm shrink-0 w-16">{{ $f['label'] }}</span>
                    <span class="text-sm text-[#8A8A9E] leading-relaxed">{{ $f['detail'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ③ 店舗向け機能 --}}
    <section id="shop" class="mt-9">
        <div class="flex items-center gap-4 mb-8">
            <span class="flex-shrink-0 w-10 h-10 rounded-full bg-deli-500 text-white font-black text-lg flex items-center justify-center">③</span>
            <div>
                <p class="text-deli-400 text-sm font-bold tracking-widest mb-0.5">FOR SHOPS</p>
                <h2 class="text-2xl md:text-3xl font-black">店舗向け管理機能が充実</h2>
            </div>
        </div>
        <p class="text-[#B0AEAD] mb-8 leading-relaxed">
            管理画面から女性の情報・出勤・写メ日記などをリアルタイムで更新できます。
            ユーザーの動向も把握できるため、<strong class="text-[#E8E4DC]">集客効果を最大化するための判断材料</strong>が揃います。
        </p>
        <div class="grid sm:grid-cols-2 gap-4">
            @php
                $shopFeatures = [
                    ['icon' => '📋', 'title' => '出勤登録・シフト管理',   'desc' => '出勤日・時間帯をカレンダー形式で登録。「出勤中」タブに自動で表示されます。女性からの出勤申請を受け付けることも可能です。'],
                    ['icon' => '✨', 'title' => '新人登録',               'desc' => '新人フラグを立てると新人タブに掲載。入店から30日間は新人バッジが表示されます。入店予定日を事前に設定しておくと、登録ユーザーに入店前からメールが届くため、体験入店の客付きが良くなり継続率アップにつながります。'],
                    ['icon' => '📷', 'title' => '写メ日記投稿',           'desc' => '女性ごとに写メ日記を投稿できます。ファン化・リピート促進に効果的です。女性本人にURLを渡して直接投稿してもらうことも可能です。'],
                    ['icon' => '♡',  'title' => 'お気に入り登録者の分析', 'desc' => '管理画面で在籍女性のファン一覧と遊びやすい曜日・時間帯を確認。出勤計画に活用できます。女性の写メ日記投稿画面にも表示されるので、女性のモチベーションアップや、出勤時間の調整相談の資料にもなります。'],
                    ['icon' => '💬', 'title' => '口コミ管理',             'desc' => '寄せられた口コミを管理画面で確認。有料プランの店舗は不当な書き込みへの削除申請が可能です。'],
                    ['icon' => '📷', 'title' => '写メ日記管理',           'desc' => '在籍女性が投稿した写メ日記を管理画面で一覧確認。不適切な投稿はすぐに削除できます。'],
                    ['icon' => '🔔', 'title' => 'ユーザーへの自動通知',   'desc' => '新人登録・シフト登録でお気に入りユーザーに自動メール通知。集客コストゼロで呼び込めます。'],
                ];
            @endphp
            @foreach($shopFeatures as $f)
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

    {{-- ④ SEO・スマホ最適化 --}}
    <section id="seo" class="mt-9">
        <div class="flex items-center gap-4 mb-8">
            <span class="flex-shrink-0 w-10 h-10 rounded-full bg-deli-500 text-white font-black text-lg flex items-center justify-center">④</span>
            <div>
                <p class="text-deli-400 text-sm font-bold tracking-widest mb-0.5">SEO &amp; PERFORMANCE</p>
                <h2 class="text-2xl md:text-3xl font-black">SEO強化・スマホ最適化</h2>
            </div>
        </div>
        <p class="text-[#B0AEAD] mb-8 leading-relaxed">
            旧サイトはPHP8非互換で動作が不安定でしたが、新サイトはPHP8.4＋Laravel最新版で構築。
            表示速度・SEO対策・スマホ対応を全面的に見直しました。
        </p>
        <div class="grid sm:grid-cols-2 gap-4">
            @php
                $seoFeatures = [
                    ['icon' => '🔍', 'title' => 'カテゴリ別LPページ',       'desc' => '「還暦風俗」「巨乳デリヘル」「長身デリヘル」など需要の高いキーワードに対応した専用ページを設置。Googleからの自然流入を増やします。'],
                    ['icon' => '📱', 'title' => 'スマートフォン完全対応',    'desc' => 'スマホ閲覧を前提に設計。フロートボタンや縦スクロールに最適化したUIで操作性が大幅に向上。'],
                    ['icon' => '⚡', 'title' => '表示速度の大幅改善',        'desc' => '画像の遅延読み込み・キャッシュ最適化・不要なリソースの削除により、ページ表示が高速化。直帰率改善に貢献します。'],
                    ['icon' => '🗺', 'title' => 'サイトマップ・構造化データ', 'desc' => '全ページのサイトマップを自動生成。Google向けの構造化データ（パンくず）も実装し、検索結果での見栄えを改善。'],
                    ['icon' => '🌐', 'title' => 'エリア×カテゴリの網羅',    'desc' => '都道府県×エリア×女性タイプの組み合わせで検索ページを生成。「大阪 熟女デリヘル」などのロングテールキーワードに対応。'],
                    ['icon' => '🛡', 'title' => 'セキュリティ強化',          'desc' => 'Laravel最新版によるCSRF保護・XSS対策・SQLインジェクション防止など、現代的なセキュリティ基準に準拠。'],
                ];
            @endphp
            @foreach($seoFeatures as $f)
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

    {{-- ⑤ エリア別サブドメインサイトの統合 --}}
    <section id="subdomain" class="mt-9">
        <div class="flex items-center gap-4 mb-8">
            <span class="flex-shrink-0 w-10 h-10 rounded-full bg-[#3A3A4E] text-[#8A8A9E] font-black text-lg flex items-center justify-center">⑤</span>
            <div>
                <p class="text-[#8A8A9E] text-sm font-bold tracking-widest mb-0.5">CONSOLIDATION</p>
                <h2 class="text-2xl md:text-3xl font-black">エリア別サブドメインサイトは新サイトに統合</h2>
            </div>
        </div>
        <div class="bg-[#1E1E30] border border-[#2A2A3E] rounded-xl p-6 space-y-4">
            <p class="text-[#B0AEAD] leading-relaxed">
                従来は <code class="bg-[#2A2A3E] text-deli-300 px-1.5 py-0.5 rounded text-sm">shinjuku.delicon.jp</code> のようなエリア別のサブドメインサイトが個別に存在していましたが、
                新サイトでは <strong class="text-[#E8E4DC]">1つのサイト内でエリア別ページ（例：<code class="bg-[#2A2A3E] text-deli-300 px-1.5 py-0.5 rounded text-sm">delicon.jp/shinjuku/girl-list/</code>）</strong> として統合しました。
            </p>
            <ul class="space-y-2 text-sm text-[#8A8A9E]">
                <li class="flex gap-2"><span class="text-emerald-400 shrink-0">✓</span> 各サブドメインのURLは新サイトの対応エリアページへ自動転送（301リダイレクト）されます</li>
                <li class="flex gap-2"><span class="text-emerald-400 shrink-0">✓</span> Googleに蓄積されたリンクの評価（SEO資産）はリダイレクトにより新サイトへ引き継がれます</li>
                <li class="flex gap-2"><span class="text-amber-400 shrink-0">!</span> ブックマークや外部サイトからのリンクは自動的に転送されますので、差し替えは不要です</li>
            </ul>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-gradient-to-br from-deli-900/40 to-[#1E1E30] border border-deli-700/30 rounded-2xl p-8 md:p-12 text-center">
        <h2 class="text-2xl md:text-3xl font-black mb-4">まずはサイトをご覧ください</h2>
        <p class="text-[#B0AEAD] mb-8 leading-relaxed max-w-xl mx-auto">
            掲載内容のご確認・修正・追加機能のご要望は、管理代理店または運営事務局までお気軽にお問い合わせください。
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('girl.list', ['area_slug' => 'all']) }}/"
               class="bg-deli-500 hover:bg-deli-400 text-white font-bold px-8 py-3 rounded-xl transition">
                女性一覧を見る
            </a>
            <a href="{{ route('shop.list', ['area_slug' => 'all']) }}/"
               class="bg-[#2A2A3E] hover:bg-[#34344E] text-[#E8E4DC] font-bold px-8 py-3 rounded-xl border border-[#3A3A4E] transition">
                店舗一覧を見る
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
