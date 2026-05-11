@extends('layouts.app')

@section('title', 'プライバシーポリシー')
@section('canonical', route('privacy') . '/')
@section('description', 'デリヘルリストのプライバシーポリシー。お客様の個人情報の取り扱いについてご説明します。')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">プライバシーポリシー</h1>
    <p class="text-xs text-gray-400 mb-10">最終更新日：2026年4月29日</p>

    <div class="bg-white rounded-xl shadow-sm p-8 space-y-8 text-sm text-gray-700 leading-relaxed">

        <p>株式会社リスト（以下「当社」）は、デリヘルリスト（以下「本サービス」）の運営において取得する個人情報を以下の方針に基づき適切に管理します。</p>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第1条（収集する個人情報）</h2>
            <p class="mb-2">本サービスでは、以下の情報を収集します。</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>氏名（担当者名）・メールアドレス・電話番号</li>
                <li>LINEアカウント情報（LINE ログイン利用時のユーザーID・表示名）</li>
                <li>店舗名・業種・所在地・郵便番号・営業情報等の掲載情報</li>
                <li>求人応募者の氏名・連絡先・応募メッセージ等の応募情報（応募先店舗の担当者に共有されます）</li>
                <li>求人アラート登録時のメールアドレスおよび検索条件（性別・都道府県・業種等）</li>
                <li>アクセスログ（IPアドレス・ブラウザ情報・参照元URL・Cookie等）</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第2条（利用目的）</h2>
            <p class="mb-2">収集した個人情報は以下の目的に利用します。</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>本サービスの提供・運営・改善</li>
                <li>掲載審査および掲載内容の管理・ご連絡</li>
                <li>本人確認・認証（LINE ログインを含む）</li>
                <li>求人応募情報の店舗担当者への転送</li>
                <li>サービスに関する重要なお知らせの送付</li>
                <li>不正利用の防止・調査</li>
                <li>アクセス解析によるサービス品質の向上</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第3条（第三者への提供）</h2>
            <p class="mb-2">当社は以下の場合を除き、個人情報を第三者に提供しません。</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>ご本人の同意がある場合</li>
                <li>法令に基づく場合（捜査機関からの適法な照会等）</li>
                <li>人の生命・身体・財産の保護のために必要であり、ご本人の同意取得が困難な場合</li>
                <li>国または地方公共団体の法令事務遂行に必要な場合</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第4条（外部サービスの利用）</h2>
            <div class="space-y-4">
                <div>
                    <p class="font-medium text-gray-700 mb-1">LINEログイン・LINE Messaging API（LINE株式会社）</p>
                    <p>本サービスでは、LINE株式会社が提供するLINEログインを認証手段として利用しています。LINEログインを通じて取得するユーザーIDおよび表示名は、アカウント識別の目的にのみ使用します。また、LINEログインで友だち登録された方には、LINE Messaging APIを通じてサービスに関する重要なお知らせ（掲載審査結果・掲載継続確認等）をLINEメッセージでお送りする場合があります。LINEのプライバシーポリシーについては、LINE株式会社の公式サイトをご参照ください。</p>
                </div>
                <div>
                    <p class="font-medium text-gray-700 mb-1">Google タグマネージャー・Google アナリティクス（Google LLC）</p>
                    <p>本サービスでは、Google LLC が提供する「Google タグマネージャー」および「Google アナリティクス 4」を利用しています。これらのツールはCookieを使用してアクセスデータ（閲覧ページ・滞在時間・参照元等）を収集し、Googleのサーバーに送信・処理します。収集されるデータは匿名化されており、個人を特定するものではありません。Google アナリティクスのデータ収集を無効にする場合は、Google アナリティクス オプトアウト アドオン（ブラウザ拡張機能）をご利用ください。Googleのプライバシーポリシーについては、Google LLC の公式サイトをご参照ください。</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第5条（安全管理措置）</h2>
            <p>当社は個人情報の漏洩・滅失・毀損を防止するため、適切な安全管理措置を講じます。パスワードは不可逆な形で暗号化して保存し、SSL/TLSにより通信を保護しています。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第6条（個人情報の開示・訂正・削除）</h2>
            <p>ご本人から個人情報の開示・訂正・利用停止・削除の請求があった場合、本人確認のうえ、個人情報保護法の定めに従い合理的な期間内に対応します。開示請求には手数料（1件1,000円）を申し受ける場合があります。お問い合わせ窓口までご連絡ください。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第7条（Cookieの利用）</h2>
            <p>本サービスはセッション管理・利便性向上・アクセス解析（Google アナリティクス）のためにCookieを使用します。ブラウザの設定によりCookieを無効化することができますが、一部機能が正常に動作しなくなる場合があります。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第8条（個人情報の保持期間）</h2>
            <p>当社は、利用目的の達成に必要な範囲内で個人情報を保持します。主な保持期間の目安は以下のとおりです。</p>
            <ul class="list-disc list-inside space-y-1 mt-2 text-gray-600">
                <li>掲載者の登録情報：退会またはアカウント削除から1年間</li>
                <li>求人応募情報・メッセージ：応募日から1年間</li>
                <li>アクセスログ：収集から6ヶ月間</li>
            </ul>
            <p class="mt-2">保持期間経過後は、速やかに削除または匿名化します。ただし、法令による保存義務がある場合はその期間に従います。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第9条（プライバシーポリシーの改定）</h2>
            <p>当社は必要に応じて本ポリシーを改定することがあります。重要な変更がある場合はサービス上でお知らせします。改定後の内容はサイトへの掲載をもって効力を生じます。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第10条（お問い合わせ窓口）</h2>
            <div class="bg-gray-50 rounded-lg p-4 text-gray-600">
                <p class="font-medium text-gray-700 mb-2">株式会社リスト</p>
                <p>受付時間：平日 10:00〜19:00</p>
                <p>電話：03-5206-6966</p>
                <p>メール：info@list-company.net</p>
            </div>
        </section>

    </div>

    <div class="mt-8 text-center">
        <a href="javascript:history.back()" class="text-sm text-gray-400 hover:text-gray-600">← 戻る</a>
    </div>
</div>
@endsection
