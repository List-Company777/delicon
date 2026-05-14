@extends('layouts.app')

@section('title', '新サイト移行のご案内')
@section('robots', 'noindex,nofollow')
@section('description', '旧サイトから新サイト「デリヘルリスト」への移行に関するご案内です。ログイン方法・ID・パスワードについてご確認ください。')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">

    <h1 class="text-2xl font-bold text-[#E8E4DC] mb-2">新サイト移行のご案内</h1>
    <p class="text-xs text-gray-400 mb-10">デリヘルリスト 運営事務局</p>

    <div class="bg-white rounded-xl shadow-sm p-8 space-y-8 text-sm text-gray-700 leading-relaxed">

        <p>この度は旧サイトをご利用いただきありがとうございました。<br>
        サービスをリニューアルし、新サイト「<strong>デリヘルリスト</strong>」として新たにスタートいたしました。<br>
        引き続きよろしくお願いいたします。</p>

        <section>
            <h2 class="font-bold text-gray-800 text-base mb-4 pb-2 border-b border-gray-100">新サイトへのログイン方法</h2>

            <div class="space-y-5">
                <div class="bg-rose-50 border border-rose-200 rounded-lg p-5">
                    <p class="font-bold text-rose-700 mb-3">ログインURL</p>
                    <a href="https://www.delicon.jp/login/"
                       class="inline-flex items-center gap-2 bg-rose-600 text-white font-bold px-6 py-3 rounded-lg hover:bg-rose-700 transition-colors text-base shadow-sm">
                        🔑 https://www.delicon.jp/login/
                    </a>
                    <p class="mt-2 text-xs text-rose-600">上のボタンをクリックしてログインページへ進んでください</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-5 space-y-4">
                    <div>
                        <p class="font-bold text-gray-700 mb-1">ID（メールアドレス）</p>
                        <p class="text-gray-600">旧サイトにご登録いただいていたメールアドレスをそのままご使用ください。</p>
                    </div>
                    <div>
                        <p class="font-bold text-gray-700 mb-1">パスワード</p>
                        <p class="text-gray-600">旧サイトでご利用いただいていたパスワードをそのままご使用ください。</p>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 text-base mb-4 pb-2 border-b border-gray-100">ログイン後にまずやっていただくこと</h2>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-5 space-y-4">
                <div>
                    <p class="font-bold text-amber-800 mb-2">店舗看板画像の設定</p>
                    <p class="text-gray-600 mb-3">新サイトでは店舗詳細ページのヘッダーに<strong>店舗看板画像</strong>が表示されます。未設定の場合は代替画像が表示されるため、早めにご設定ください。</p>
                    <ul class="space-y-1 text-gray-600">
                        <li>・推奨サイズ：<strong>900×360px</strong>（横長・5:2比率）</li>
                        <li>・対応形式：JPEG・PNG・WebP</li>
                        <li>・ファイルサイズ：5MB以下</li>
                    </ul>
                    <p class="mt-3 text-gray-500 text-xs">設定場所：ログイン後 →「店舗情報」→「看板画像」</p>
                </div>
                <div class="border-t border-amber-200 pt-4">
                    <p class="font-bold text-amber-800 mb-2">ジャンル・エリアの確認</p>
                    <p class="text-gray-600">移行データによっては<strong>ジャンル欄が空白</strong>になっている場合があります。ログイン後に「店舗情報」からご確認・設定をお願いします。</p>
                    <p class="text-gray-600 mt-2">エリアなど変更・追加が必要な場合は、お問い合わせフォームよりご連絡ください。</p>
                    <p class="mt-3 text-gray-500 text-xs">設定場所：ログイン後 →「店舗情報」→「基本情報」→「ジャンル」</p>
                </div>
                <div class="border-t border-amber-200 pt-4">
                    <p class="font-bold text-amber-800 mb-2">在籍女性の管理</p>
                    <p class="text-gray-600 mb-2">旧サイトから移行した在籍データをご確認ください。</p>
                    <ul class="space-y-2 text-gray-600">
                        <li>・<strong>退店した女性の削除</strong>：「在籍女性」一覧から対象の女性を選び、削除してください。</li>
                        <li>・<strong>新規女性の登録</strong>：「在籍女性」→「新規登録」から追加できます。</li>
                    </ul>
                    <p class="mt-3 text-gray-500 text-xs">設定場所：ログイン後 →「在籍女性」</p>
                </div>
                <div class="border-t border-amber-200 pt-4">
                    <p class="font-bold text-amber-800 mb-2">料金目安・特色タグの確認</p>
                    <p class="text-gray-600">「店舗情報」→「基本情報」タブの<strong>料金目安</strong>と<strong>特色タグ</strong>を設定しておくと、検索絞り込みへの対応や店舗の特色がより伝わりやすくなります。ぜひご確認ください。</p>
                    <p class="mt-3 text-gray-500 text-xs">設定場所：ログイン後 →「店舗情報」→「基本情報」タブ</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 text-base mb-4 pb-2 border-b border-gray-100">デリヘルリストへの掲載でSEO対策</h2>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-5 space-y-4 text-sm">
                <div class="flex items-start gap-3">
                    <span class="text-2xl leading-none">📅</span>
                    <p class="text-gray-700"><strong class="text-blue-800">delicon.jp のドメインエイジは約21年です。</strong><br>
                    無料掲載してオフィシャルHPにリンクするだけで、SEO対策になります。</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-2xl leading-none">📈</span>
                    <p class="text-gray-700"><strong class="text-blue-800">直近1か月間で1万クリック以上をGoogleから集めています。</strong><br>
                    今回のリニューアルでさらに増やせる見込みです。</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 text-base mb-3 pb-2 border-b border-gray-100">パスワードをお忘れの場合</h2>
            <p>ログイン画面の「パスワードを忘れた方」よりパスワードの再設定が可能です。<br>
            登録メールアドレス宛に再設定用のURLをお送りします。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 text-base mb-3 pb-2 border-b border-gray-100">お問い合わせ</h2>
            <p>ログインに関するご不明点は、下記よりお気軽にお問い合わせください。</p>
            <div class="mt-3">
                <a href="{{ route('inquiry') }}"
                   class="inline-block text-rose-600 underline hover:no-underline">
                    お問い合わせフォームはこちら
                </a>
            </div>
        </section>

    </div>
</div>
@endsection
