@extends('layouts.app')

@section('title', '特定商取引法に基づく表記')
@section('canonical', route('tokutei') . '/')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">特定商取引法に基づく表記</h1>
    <p class="text-xs text-gray-400 mb-10">最終更新日：2026年4月22日</p>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden text-sm text-gray-700">
        <table class="w-full">
            <tbody class="divide-y divide-gray-100">
                <tr>
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-5 py-4 w-36 align-top whitespace-nowrap">販売者</th>
                    <td class="px-5 py-4">株式会社リスト</td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-5 py-4 align-top whitespace-nowrap">所在地</th>
                    <td class="px-5 py-4">東京都中央区銀座3-10-9 KEC銀座ビル701</td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-5 py-4 align-top whitespace-nowrap">代表者</th>
                    <td class="px-5 py-4">高司　浩</td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-5 py-4 align-top whitespace-nowrap">電話番号</th>
                    <td class="px-5 py-4">
                        03-5206-6966
                        <p class="text-xs text-gray-400 mt-1">※ お問い合わせはメール・お問い合わせフォームを優先してください</p>
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-5 py-4 align-top whitespace-nowrap">メール</th>
                    <td class="px-5 py-4">nwl-support@nightwork-list.com</td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-5 py-4 align-top whitespace-nowrap">運営会社HP</th>
                    <td class="px-5 py-4">
                        <a href="https://list-company.net" target="_blank" rel="noopener" class="text-blue-600 hover:underline">https://list-company.net</a>
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-5 py-4 align-top whitespace-nowrap">販売価格</th>
                    <td class="px-5 py-4 space-y-3">
                        <div>
                            <p class="font-medium text-gray-700 mb-1">無料プラン</p>
                            <p>無料（費用なし）</p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700 mb-1">有料プラン（クリック課金制）</p>
                            <ul class="list-disc list-inside space-y-1 text-gray-600">
                                <li>予算チャージ最低額：10,000円（税抜）／ 11,000円（税込）</li>
                                <li>入札単価：30円〜9,990円（10円刻み、税抜）</li>
                                <li>求人・店舗ページがクリックされるたびに設定した入札単価が予算残高から差し引かれます</li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-5 py-4 align-top whitespace-nowrap">その他費用</th>
                    <td class="px-5 py-4 text-gray-600">インターネット接続に伴う通信費等は、お客様のご負担となります。</td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-5 py-4 align-top whitespace-nowrap">支払方法</th>
                    <td class="px-5 py-4">
                        銀行振込 / クレジットカード
                        <p class="text-xs text-gray-400 mt-1">※ 利用可能な支払方法は申し込み時にご案内します</p>
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-5 py-4 align-top whitespace-nowrap">支払時期</th>
                    <td class="px-5 py-4 text-gray-600">
                        <ul class="list-disc list-inside space-y-1">
                            <li>銀行振込：当社が指定する振込先に、申し込み後7営業日以内にお支払いください</li>
                            <li>クレジットカード：申し込み手続き完了時に決済</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-5 py-4 align-top whitespace-nowrap">役務提供時期</th>
                    <td class="px-5 py-4 text-gray-600">入金確認後、速やかに予算残高を追加します。追加完了後、即時クリック課金が有効になります。</td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-5 py-4 align-top whitespace-nowrap">返金について</th>
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-800 mb-1">返金には一切応じられません。</p>
                        <p class="text-gray-600">本サービスはデジタルコンテンツおよび役務提供の性質上、チャージ済みの予算残高およびクリック課金済みの費用は、掲載の取りやめ・退会・残高未消化の場合を含め、いかなる理由があっても返品・返金に応じることができません。</p>
                        <p class="text-gray-500 text-xs mt-2">※ 当社の重大な過失による明らかな誤課金が確認された場合はこの限りではありません。</p>
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-5 py-4 align-top whitespace-nowrap">解約について</th>
                    <td class="px-5 py-4 text-gray-600">
                        <p>有料プランは月次自動更新ではなく、都度チャージ制です。追加申し込みを行わない限り、チャージ残高がなくなった時点で自動的に無料プランに移行します。退会手続きは管理画面からいつでも可能ですが、残高の返金はできません。</p>
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-5 py-4 align-top whitespace-nowrap">特記事項</th>
                    <td class="px-5 py-4 text-gray-600">
                        <ul class="list-disc list-inside space-y-1">
                            <li>サービス内容・価格は予告なく変更される場合があります。</li>
                            <li>本サービスは18歳未満の方はご利用いただけません。</li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
