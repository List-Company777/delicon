@extends('layouts.app')

@section('title', 'サービス利用規約')
@section('canonical', route('terms') . '/')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">サービス利用規約</h1>
    <p class="text-xs text-gray-400 mb-10">最終更新日：2026年4月18日</p>

    <div class="bg-white rounded-xl shadow-sm p-8 space-y-8 text-sm text-gray-700 leading-relaxed">

        <p>本規約は、株式会社リスト（以下「当社」）が運営するナイトワークリスト（以下「本サービス」）の利用条件を定めるものです。本サービスをご利用の方（以下「ユーザー」）は本規約に同意したものとみなします。</p>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第1条（サービスの内容）</h2>
            <p class="mb-2">本サービスは、キャバクラ・ホスト・ガールズバー等のナイトワーク業態における求人情報・店舗営業情報、および飲食・遊興目的の夜遊び店舗情報を掲載・提供するプラットフォームです。</p>
            <p>当社は職業安定法に定める職業紹介事業者ではなく、求人の媒介・あっせんを行うものではありません。本サービスが提供する求人応募機能は、求職者と店舗との間の連絡手段を提供するものに過ぎず、採用・雇用契約の成立には一切関与しません。ユーザーと店舗との間の雇用条件・業務内容等については、双方が直接確認・合意するものとします。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第2条（利用資格）</h2>
            <p class="mb-2">本サービスは以下の条件を満たす方のみご利用いただけます。</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>18歳以上であること</li>
                <li>本規約の全条項に同意していること</li>
                <li>過去に当社から利用停止・禁止の措置を受けていないこと</li>
                <li>日本国内に在住していること</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第3条（禁止事項）</h2>
            <p class="mb-2">ユーザーは以下の行為を行ってはなりません。</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>虚偽の情報の送信・掲載</li>
                <li>他のユーザーまたは第三者への誹謗中傷・嫌がらせ</li>
                <li>当社または第三者の著作権・商標権等の知的財産権の侵害</li>
                <li>本サービスのシステムへの不正アクセス・過負荷行為</li>
                <li>本サービスを通じた違法な勧誘・営業行為</li>
                <li>コンピュータウイルス等の有害プログラムの送信</li>
                <li>その他、当社が不適切と判断する行為</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第4条（求人応募に関する注意事項）</h2>
            <p class="mb-2">求人情報に応募される方は以下をご了承ください。</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>18歳未満の方は求人への応募を行うことができません。</li>
                <li>応募フォームに入力した氏名・連絡先・メッセージ等の情報は、応募先店舗の担当者に共有されます。</li>
                <li>採用・不採用の判断は店舗が独自に行うものであり、当社は関与しません。</li>
                <li>面接・勤務開始前に、業務内容・勤務条件・給与等について店舗と十分に確認してください。</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第5条（ユーザーの責任）</h2>
            <p>ユーザーは本サービスの利用に関して発生した損害について、自己の責任と費用で解決するものとします。ユーザーの行為により当社または第三者に損害が生じた場合、ユーザーはその損害を賠償する責任を負います。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第6条（免責事項）</h2>
            <p class="mb-2">当社は以下について責任を負いません。</p>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>本サービス上の掲載情報の正確性・完全性・有用性</li>
                <li>サービスの中断・停止・終了・内容変更による損害</li>
                <li>ユーザーと店舗との間のトラブル・取引・雇用契約に関する一切</li>
                <li>求人応募機能を通じた連絡・面接・採用結果に関する一切</li>
                <li>外部サービス（LINE・メール配信サービス・アクセス解析ツール等）の障害・仕様変更による影響</li>
                <li>ユーザーの環境に起因する本サービスの利用障害</li>
            </ul>
            <p class="mt-2">当社の故意または重大な過失による場合を除き、当社の損害賠償責任は直接損害に限るものとします。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第7条（サービスの変更・停止・終了）</h2>
            <p>当社は事前の通知なく、本サービスの内容を変更し、または提供を一時停止・終了することがあります。これによりユーザーに生じた損害について、当社は責任を負いません。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第8条（利用停止・禁止）</h2>
            <p>当社は、ユーザーが本規約に違反した場合または違反のおそれがあると判断した場合、事前の通知なく当該ユーザーのサービス利用を停止し、または禁止する措置を講じることができます。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第9条（規約の改定）</h2>
            <p>当社は必要に応じて本規約を改定することがあります。改定後の規約は本サービス上への掲載をもって効力を生じ、以降の利用をもって改定内容に同意したものとみなします。</p>
        </section>

        <section>
            <h2 class="font-bold text-gray-800 mb-3">第10条（準拠法・管轄裁判所）</h2>
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
