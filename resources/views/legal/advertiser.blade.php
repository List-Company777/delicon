@extends('layouts.app')

@section('title', '掲載規約')
@section('canonical', route('advertiser') . '/')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">掲載規約</h1>
    <p class="text-xs text-gray-400 mb-10">最終更新日：2026年4月29日</p>

    <div class="bg-white rounded-xl shadow-sm p-8 space-y-8 text-sm text-gray-700 leading-relaxed">

        <p>本掲載規約（以下「本規約」）は、株式会社リスト（以下「当社」）が運営するナイトワークリスト（以下「本サービス」）に求人・営業情報を掲載する事業者（以下「掲載者」）と当社との間に適用される規約です。掲載者は本規約に同意のうえで本サービスをご利用ください。</p>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第1条（サービスの内容）</h2>
            <p>本サービスは、ナイトワーク業態における求人情報・店舗営業情報の掲載・公開を行うプラットフォームを提供するものです。当社は求人の媒介・あっせんを行うものではなく、掲載者と求職者・営業担当者との直接的な接触・取引に関与しません。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第2条（登録および審査）</h2>
            <p class="mb-2">掲載者は所定の登録フォームに必要事項を入力し、当社の審査を経てサービスを利用できます。当社は以下の場合に登録を拒否または取り消すことができます。</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>登録情報に虚偽があると判断した場合</li>
                <li>本規約に違反するおそれがあると判断した場合</li>
                <li>過去に利用停止・禁止の措置を受けた事業者である場合</li>
                <li>風俗営業等規制法その他関連法令に基づく適正な許可・届出を有しない違法な営業形態であると当社が判断した場合</li>
                <li>性的サービスを提供するアダルト系業態（ソープランド・デリヘル・ファッションヘルス等）に該当する場合</li>
                <li>その他、当社が不適切と判断した場合</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第3条（掲載プランおよびクリック課金）</h2>
            <p class="mb-2">本サービスでは、無料プランと有料プランの2種類の掲載プランを提供します。</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600 mb-3">
                <li><span class="font-medium text-gray-700">無料プラン：</span>費用なしで求人・店舗情報を掲載できます。検索結果における表示優先度は有料プランより低くなります。</li>
                <li><span class="font-medium text-gray-700">有料プラン：</span>事前に予算をチャージし、求人・店舗ページがクリックされるたびに設定した入札単価が予算残高から差し引かれます（クリック課金制）。</li>
            </ul>
            <p class="mb-2">有料プランの利用にあたっては以下をご了承ください。</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>入札単価・月次予算は管理画面から設定・変更できます。</li>
                <li>予算残高がなくなった場合、掲載は継続されますが検索表示の優先度が無料プランと同等に変更されます。</li>
                <li>月次予算を設定している場合、毎月1日に予算残高へのチャージが行われます。</li>
                <li>同一のIPアドレスから同一ページへの短時間（1時間以内）における重複クリックは課金対象外とし、二重課金を防止しています。</li>
                <li>有料プランの申し込みは当社の審査を経て有効となります。</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第4条（料金の支払いおよび返金不可）</h2>
            <p class="mb-2">有料プランの料金に関して、以下の事項を十分にご確認のうえお申し込みください。</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600 mb-3">
                <li>有料プランのご利用には事前の予算チャージが必要です。チャージ金額に消費税10%を加算した金額をお振込みいただきます。銀行振込の場合の振込手数料はお客様のご負担となります。</li>
                <li>クリック課金はクリック発生時点で確定します。</li>
                <li>月次予算が設定されている場合、毎月1日の自動チャージ分についても同様です。</li>
            </ul>
            <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-3">
                <p class="font-bold text-red-700 mb-1">【重要】返金不可について</p>
                <p class="text-red-700 text-sm">チャージ済みの予算残高およびクリック課金済みの費用は、<span class="font-bold">いかなる理由があっても返金に応じることができません</span>。掲載の取りやめ・退会・サービス利用停止の場合も同様です。予算残高が残っている場合でも返金はできません。</p>
                <p class="text-red-600 text-sm mt-2">ただし、当社の重大な過失による明らかな二重課金・誤課金が確認された場合はこの限りではありません。</p>
            </div>
            <p class="text-gray-600 text-sm">申し込みの送信をもって、上記の返金不可ポリシーに同意いただいたものとみなします。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第5条（ID・パスワードの管理）</h2>
            <p>掲載者は、登録したメールアドレス・パスワードおよびLINEアカウントを自己の責任で適切に管理するものとします。これらの情報を第三者に譲渡・共有することはできません。当該情報を用いて行われた一切の行為は、掲載者本人の行為とみなします。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第6条（求人掲載件数）</h2>
            <p class="mb-2">掲載できる求人情報の件数は、プランによって以下のとおり異なります。</p>
            <div class="overflow-x-auto">
                <table class="w-full text-xs border border-gray-200 rounded">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-3 py-2 border-b border-gray-200">求人種別</th>
                            <th class="text-center px-3 py-2 border-b border-gray-200">無料プラン</th>
                            <th class="text-center px-3 py-2 border-b border-gray-200">有料プラン</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-3 py-2">キャスト求人</td>
                            <td class="text-center px-3 py-2">1件</td>
                            <td class="text-center px-3 py-2">3件</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2">スタッフ求人</td>
                            <td class="text-center px-3 py-2">1件</td>
                            <td class="text-center px-3 py-2">5件</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第7条（画像の取り扱い）</h2>
            <p>掲載者がアップロードした店舗画像・求人画像は、本サービス内での表示および検索エンジンへのインデックス目的にのみ使用します。当社は掲載者の事前承諾なく、これらの画像を第三者に提供・販売・転用しません。掲載停止・退会後は、合理的な期間内に画像データを削除します。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第8条（掲載情報の責任）</h2>
            <p class="mb-2">掲載者は、掲載する情報について以下を保証するものとします。</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>実在する店舗の正確な情報であること</li>
                <li>労働基準法・職業安定法・風俗営業等の規制及び業務の適正化等に関する法律その他関係法令に準拠した内容であること</li>
                <li>虚偽・誇大・誤解を招く表現を含まないこと</li>
                <li>第三者の著作権・商標権・肖像権等を侵害しないこと</li>
                <li>公序良俗に反する内容を含まないこと</li>
            </ul>
            <p class="mt-2">掲載内容に起因して第三者または当社に損害が生じた場合、掲載者はその損害を賠償する責任を負います。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第9条（禁止事項）</h2>
            <p class="mb-2">掲載者は以下の行為を行ってはなりません。</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>虚偽・架空の求人情報・営業情報の掲載</li>
                <li>法令に違反する内容の掲載</li>
                <li>当社のシステムへの不正アクセス・過負荷行為</li>
                <li>アカウントの第三者への譲渡・貸与</li>
                <li>本サービスを通じた第三者への不当な勧誘・営業行為</li>
                <li>その他、当社が不適切と判断する行為</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第10条（掲載継続確認および自動停止）</h2>
            <p class="mb-2">当社は掲載情報の鮮度維持のため、無料プランの掲載者を対象に以下の掲載継続確認を実施します。</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>最終ログインから3ヶ月以上が経過した場合、登録のメールアドレスまたはLINEに掲載継続確認の連絡を行います。</li>
                <li>確認連絡から2週間以内に応答がない場合、掲載を一時停止します。</li>
                <li>一時停止後も管理画面からログインすることで掲載を再開できます。</li>
            </ul>
            <p class="mt-2">有料プラン利用中の掲載者は本条の自動停止の対象外です。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第11条（掲載の停止・削除）</h2>
            <p>当社は、掲載者が本規約に違反した場合、または以下に該当すると判断した場合、事前の通知なく掲載の停止・削除およびアカウントの停止・削除を行うことができます。これらの措置による損害について、当社は責任を負いません。</p>
            <ul class="list-disc list-inside space-y-1 mt-2 text-gray-600">
                <li>掲載内容が法令・本規約に違反する場合</li>
                <li>登録情報に虚偽があることが判明した場合</li>
                <li>第10条に定める掲載継続確認に応答がなかった場合</li>
                <li>その他、当社がサービスの運営上不適切と判断した場合</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第12条（免責事項）</h2>
            <p class="mb-2">当社は以下について責任を負いません。</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>本サービスを通じた求職者・営業担当者との接触・取引に関する一切</li>
                <li>サービスの中断・停止・終了・内容変更による損害</li>
                <li>掲載情報の検索順位・表示結果・効果（入札単価による順位変動を含む）</li>
                <li>予算残高の消費・残高不足による表示優先度の変動</li>
                <li>外部サービス（LINE・メール配信サービス等）の障害・仕様変更による影響</li>
            </ul>
            <p class="mt-2">当社の故意または重大な過失による場合を除き、当社の損害賠償責任は直接損害に限るものとします。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第13条（規約の改定）</h2>
            <p>当社は必要に応じて本規約を改定することがあります。改定後の規約は本サービス上への掲載をもって効力を生じます。継続利用をもって改定内容に同意したものとみなします。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第14条（準拠法・管轄裁判所）</h2>
            <p>本規約は日本法に準拠します。本サービスに関する紛争については、東京地方裁判所を第一審の専属的合意管轄裁判所とします。</p>
        </section>

        <div class="text-right text-xs text-gray-400">
            <p>株式会社リスト</p>
        </div>

    </div>

    <div class="mt-8 text-center">
        <a href="javascript:history.back()" class="text-sm text-gray-400 hover:text-gray-600">← 戻る</a>
    </div>
</div>
@endsection
