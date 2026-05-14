@extends('layouts.app')

@section('title', '代理店パートナー募集 | 夜ビズ')
@section('robots', 'noindex, follow')

@section('content')

{{-- Hero --}}
<div class="bg-gradient-to-br from-gray-900 via-purple-950 to-gray-900 text-white py-20 px-4">
    <div class="max-w-3xl mx-auto text-center">
        <p class="text-purple-300 text-sm font-medium tracking-widest mb-3">新規開拓 / 顧客掘り起こしに最適</p>
        <h1 class="text-3xl md:text-4xl font-bold leading-tight mb-6">
            売らなくていい。<br>
            <span class="text-purple-300">店舗運営支援ツール。</span>
        </h1>
        <p class="text-gray-300 text-base md:text-lg leading-relaxed">
            夜ビズは、ナイトビジネス運営をトータル支援する新しいプラットフォームです。<br>
            代理店様には「断られない営業ツール」として、今すぐ活用していただけます。
        </p>
        <p class="text-purple-200 text-sm mt-4">アップステージでご協力度合いの高い代理店様に先にお声がけしています。</p>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 py-14 space-y-16 text-sm text-gray-700">

    {{-- 夜ビズとは --}}
    <section>
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <span class="w-1 h-6 bg-purple-600 rounded-full inline-block"></span>
            夜ビズとは
        </h2>
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4 leading-relaxed">
            <p>
                <strong>夜ビズ</strong>は、ナイトビジネス店舗の運営を支援するプラットフォームです。初期段階では、完全無料で<strong>集客・求人・スタッフ採用</strong>を一括でカバーします。
            </p>
            <p>
                〇ndeedと同様のSEO構造を持ち、エリア×業種（職種）の組み合わせで検索上位を狙えます。<strong>基本掲載は無料</strong>で、月額固定費は一切かかりません。
            </p>
            <p>
                SEO技術にはアップステージで培った当社の技術をふんだんに用いて、検索エンジンからの流入を得られるように工夫をしています。
            </p>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-purple-50 rounded-lg p-4 text-center">
                    <div class="text-2xl mb-2">🌙</div>
                    <div class="font-bold text-purple-800 text-sm">夜ビズ</div>
                    <div class="text-xs text-gray-500 mt-1">メインブランド</div>
                </div>
                <a href="{{ route('top') }}/" target="_blank" class="bg-purple-50 rounded-lg p-4 text-center block hover:bg-purple-100 transition">
                    <div class="text-2xl mb-2">💼</div>
                    <div class="font-bold text-purple-800 text-sm">デリヘルリスト</div>
                    <div class="text-xs text-gray-500 mt-1">水商売系特化サイト</div>
                </a>
                <a href="https://fuzoku-list.com/" target="_blank" rel="noopener" class="bg-pink-50 rounded-lg p-4 text-center block hover:bg-pink-100 transition">
                    <div class="text-2xl mb-2">🌸</div>
                    <div class="font-bold text-pink-800 text-sm">風俗リスト</div>
                    <div class="text-xs text-gray-500 mt-1">風俗系特化サイト</div>
                </a>
            </div>
        </div>
    </section>

    {{-- 代理店にとってのメリット --}}
    <section>
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <span class="w-1 h-6 bg-purple-600 rounded-full inline-block"></span>
            代理店にとってのメリット
        </h2>
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-2">① 新規開拓 / 過去客の掘り起こしに使える</h3>
                <p class="leading-relaxed">
                    広告代理店の売上はどれだけクライアントとの接点を持てるか？で決まります。「広告を買ってください」ではなく「<strong>基本無料で使えるツールができました</strong>」という入口なので、断られる理由がありません。
                    まだお付き合いのない店舗への飛び込みでも、会話を始めることができます。
                </p>
                <div class="bg-purple-50 border-l-4 border-purple-500 rounded-r-lg px-5 py-4 text-purple-900 font-bold text-base">
                    今まで入れなかった店に、入れます。
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-2">② 先行者が有利になる構造</h3>
                <p class="leading-relaxed">
                    掲載店舗が増えるほど、無料枠では埋もれ始めます。今のうちに担当エリアの店舗を登録しておいた代理店が、
                    自然とそのエリアの窓口ポジションを持つことになります。
                    <strong>先に動いた代理店が、エリアの主導権を持つことになります。</strong>
                </p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-2">③ 既存顧客の深耕と乗り換え防止</h3>
                <p class="leading-relaxed">
                    現在アップステージをご利用中の店舗には、夜ビズでも掲載権限に応じた上位表示となります。スタッフ求人だけでなく、キャスト求人、営業面でも上位表示になるので、クライアント満足度を高めることが可能です。
                    「集客・求人・営業情報をまとめて管理できる窓口」として、クライアントとの関係を深め、継続することができます。
                </p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-2">④ ストック型の継続収益</h3>
                <p class="leading-relaxed">
                    単発の広告販売から脱却し、管理代行として店舗の「WEB担当ポジション」を確保。
                    スタッフ求人では収益が単発になりがちですが、営業・キャスト求人では継続的な収益源となるため、貴社の収益性向上に繋がる可能性が高まります。
                </p>
            </div>
        </div>
    </section>

    {{-- ホットリンク --}}
    <section>
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <span class="w-1 h-6 bg-purple-600 rounded-full inline-block"></span>
            集客ホットリンク機能（有料）
        </h2>
        <div class="bg-white rounded-xl shadow-sm p-6 leading-relaxed space-y-3">
            <p>
                有料のホットリンク機能を使うことで、店舗の自社HPや営業サイト（シティ〇ブンなど）へ直接誘導できます。今使っている媒体を継続しながら、夜ビズは「集客の入口を増やすブースター」として機能します。
            </p>
            <p>
                ホットリンク機能を入れると、検索結果一覧からのアクセスや、店舗詳細ページのリンクからのアクセスが指定したURLに飛ぶようになります。
            </p>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-amber-800 text-xs">
                <strong>営業トークへの活用：</strong><br>
                「今使っている営業サイト（シティ〇ブンなど）や自社HPへのアクセスを増やせるツールができたんですけど、興味ありますか？」
            </div>
        </div>
    </section>

    {{-- 有料掲載のしくみ --}}
    <section>
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <span class="w-1 h-6 bg-purple-600 rounded-full inline-block"></span>
            有料掲載のしくみ
        </h2>
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-6 leading-relaxed">

            {{-- フロー図 --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 text-center text-sm font-medium">
                <div class="bg-purple-50 rounded-xl px-5 py-4 w-full sm:w-auto">
                    <div class="text-2xl mb-1">💴</div>
                    <div class="text-purple-800 font-bold">① チャージ</div>
                    <div class="text-xs text-gray-500 mt-1">予算を事前入金</div>
                </div>
                <div class="text-gray-300 text-2xl hidden sm:block">→</div>
                <div class="bg-purple-50 rounded-xl px-5 py-4 w-full sm:w-auto">
                    <div class="text-2xl mb-1">👆</div>
                    <div class="text-purple-800 font-bold">② クリック発生</div>
                    <div class="text-xs text-gray-500 mt-1">求人・店舗ページへのアクセス</div>
                </div>
                <div class="text-gray-300 text-2xl hidden sm:block">→</div>
                <div class="bg-purple-50 rounded-xl px-5 py-4 w-full sm:w-auto">
                    <div class="text-2xl mb-1">📉</div>
                    <div class="text-purple-800 font-bold">③ 予算消費</div>
                    <div class="text-xs text-gray-500 mt-1">入札単価分が残高から差し引かれる</div>
                </div>
                <div class="text-gray-300 text-2xl hidden sm:block">→</div>
                <div class="bg-gray-50 rounded-xl px-5 py-4 w-full sm:w-auto border border-dashed border-gray-300">
                    <div class="text-2xl mb-1">🔄</div>
                    <div class="text-gray-600 font-bold">残高ゼロで無料プランへ</div>
                    <div class="text-xs text-gray-400 mt-1">再チャージで復帰</div>
                </div>
            </div>

            {{-- 無料 vs 有料 掲載枠比較 --}}
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-center border-collapse">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-200 px-4 py-2 text-left text-gray-600"></th>
                            <th class="border border-gray-200 px-4 py-2 text-gray-600">無料プラン</th>
                            <th class="border border-gray-200 px-4 py-2 text-purple-700 bg-purple-50">有料プラン</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <tr>
                            <td class="border border-gray-200 px-4 py-2 text-left font-medium">営業情報</td>
                            <td class="border border-gray-200 px-4 py-2">1件</td>
                            <td class="border border-gray-200 px-4 py-2 bg-purple-50 font-bold text-purple-700">1件</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-200 px-4 py-2 text-left font-medium">キャスト求人</td>
                            <td class="border border-gray-200 px-4 py-2">1件</td>
                            <td class="border border-gray-200 px-4 py-2 bg-purple-50 font-bold text-purple-700">3件</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-200 px-4 py-2 text-left font-medium">スタッフ求人</td>
                            <td class="border border-gray-200 px-4 py-2">1件</td>
                            <td class="border border-gray-200 px-4 py-2 bg-purple-50 font-bold text-purple-700">5件</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- 掲載順位イメージ --}}
            <div>
                <p class="text-xs font-bold text-gray-600 mb-3">掲載順位のイメージ</p>
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-purple-600 text-white text-xs flex items-center justify-center font-bold flex-shrink-0">1</span>
                        <div class="flex-1 bg-purple-50 border border-purple-200 rounded-lg px-4 py-2 text-xs text-purple-800 font-medium">有料プラン ― 入札単価が高い順</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-gray-400 text-white text-xs flex items-center justify-center font-bold flex-shrink-0">2</span>
                        <div class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-xs text-gray-700">無料プラン・画像あり</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-gray-300 text-white text-xs flex items-center justify-center font-bold flex-shrink-0">3</span>
                        <div class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-xs text-gray-400">無料プラン・画像なし</div>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">※ 有料プラン内では入札単価の高い店舗が優先表示されます。</p>
                <p class="text-xs text-amber-600 mt-1 font-medium">※ 同スコアの場合は先着登録順となります。早期登録がそのまま掲載順位の優位性につながります。</p>
            </div>

            {{-- 補足 --}}
            <ul class="space-y-2 text-gray-600 text-xs">
                <li class="flex items-start gap-2"><span class="text-purple-400 mt-0.5">▸</span> 入札単価は30円〜設定可能（風俗系は50円〜）。単価が高いほど検索上位に表示されやすくなります。</li>
                <li class="flex items-start gap-2"><span class="text-purple-400 mt-0.5">▸</span> 集客ホットリンク機能を利用した場合は、クリックごとに入札単価＋20円が消費されます。</li>
                <li class="flex items-start gap-2"><span class="text-purple-400 mt-0.5">▸</span> 残高がゼロになると自動で無料プランに切り替わり、掲載は継続されます。</li>
                <li class="flex items-start gap-2"><span class="text-purple-400 mt-0.5">▸</span> 同一IPからの重複クリック（1時間以内）は課金対象外（自動除外）です。</li>
                <li class="flex items-start gap-2"><span class="text-purple-400 mt-0.5">▸</span> 検索結果一覧でのクリック、無料店舗詳細の下部に表示される関連リンクのクリックが課金対象となります。その他のクリックは課金になりません。</li>
            </ul>
        </div>
    </section>

    {{-- 手数料体系 --}}
    <section>
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <span class="w-1 h-6 bg-purple-600 rounded-full inline-block"></span>
            手数料体系とお金の流れ
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            {{-- 紹介代理店 --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-center mb-4">
                    <span class="inline-block bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full">紹介代理店</span>
                    <div class="text-3xl font-bold text-blue-700 mt-2">10<span class="text-base font-normal">%</span></div>
                    <div class="text-xs text-gray-500">売上バック</div>
                </div>
                <div class="space-y-2 text-xs text-gray-600 leading-relaxed">
                    <p class="font-medium text-gray-700">お金の流れ</p>
                    <div class="flex items-center gap-2">
                        <span class="bg-gray-100 rounded px-2 py-1">店舗</span>
                        <span>→</span>
                        <span class="bg-purple-100 rounded px-2 py-1">夜ビズ</span>
                        <span>→</span>
                        <span class="bg-blue-100 rounded px-2 py-1">代理店（10%）</span>
                    </div>
                    <p class="text-gray-500 pt-2">店舗への登録案内のみ。管理義務、売上回収などはありません。手数料は店舗からの入金があったものについて都度発生しますが、お支払いは5,000円以上からの対応とさせていただきます。</p>
                </div>
            </div>

            {{-- 管理代行代理店 --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border-2 border-purple-200">
                <div class="text-center mb-4">
                    <span class="inline-block bg-purple-100 text-purple-800 text-xs font-bold px-3 py-1 rounded-full">管理代行代理店</span>
                    <div class="flex items-end justify-center gap-1 mt-2">
                        <span class="text-3xl font-bold text-purple-700">20</span>
                        <span class="text-base font-normal text-purple-700">%</span>
                        <span class="text-gray-300 text-lg mx-1">→</span>
                        <span class="text-3xl font-bold text-purple-500">30</span>
                        <span class="text-base font-normal text-purple-500">%</span>
                    </div>
                    <div class="text-xs text-gray-500">マージン（スライド制）</div>
                </div>

                {{-- スライド制ティア --}}
                <div class="mb-4 bg-purple-50 rounded-lg p-3">
                    <p class="text-xs font-bold text-purple-800 mb-2 text-center">管理店舗数に応じてマージンアップ</p>
                    <div class="grid grid-cols-4 gap-1 text-center text-xs">
                        <div class="bg-white rounded px-1 py-2">
                            <div class="font-bold text-purple-700">20%</div>
                            <div class="text-gray-400 text-[10px] mt-0.5">スタート</div>
                        </div>
                        <div class="bg-white rounded px-1 py-2">
                            <div class="font-bold text-purple-600">22%</div>
                            <div class="text-gray-400 text-[10px] mt-0.5">200店舗</div>
                        </div>
                        <div class="bg-white rounded px-1 py-2">
                            <div class="font-bold text-purple-600">25%</div>
                            <div class="text-gray-400 text-[10px] mt-0.5">500店舗</div>
                        </div>
                        <div class="bg-white rounded px-1 py-2 ring-1 ring-purple-300">
                            <div class="font-bold text-purple-800">30%</div>
                            <div class="text-gray-400 text-[10px] mt-0.5">1,000店舗</div>
                        </div>
                    </div>
                    <p class="text-[10px] text-purple-600 mt-2 text-center">100店舗ごとに +1% ・ 上限 30%</p>
                </div>

                <div class="space-y-2 text-xs text-gray-600 leading-relaxed">
                    <p class="font-medium text-gray-700">お金の流れ</p>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="bg-gray-100 rounded px-2 py-1">店舗</span>
                        <span>→</span>
                        <span class="bg-purple-100 rounded px-2 py-1">代理店（集金）</span>
                        <span>→</span>
                        <span class="bg-gray-100 rounded px-2 py-1">夜ビズ（70〜80%）</span>
                    </div>
                    <p class="text-gray-500 pt-2">店舗への登録案内、売上回収、アカウント管理をお任せします。クライアントの満足度向上の観点から、情報充実義務を負っていただきます。</p>
                    <p class="text-gray-400 text-xs">※ 管理が不適当と判断した場合、紹介代理店への振替可能性あり</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 管理代行の義務 --}}
    <section>
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <span class="w-1 h-6 bg-purple-600 rounded-full inline-block"></span>
            管理代行代理店の運営義務
        </h2>
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4 leading-relaxed">
            <p>管理代行代理店として契約するにあたり、担当店舗について以下の情報を適切に入力・維持管理することを義務とします。</p>
            <ul class="space-y-3">
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 w-5 h-5 rounded-full bg-purple-100 text-purple-700 text-xs flex items-center justify-center font-bold flex-shrink-0">1</span>
                    <div>
                        <span class="font-medium text-gray-800">営業情報の充実</span>
                        <p class="text-gray-500 text-xs mt-0.5">業種・料金・営業時間・設備情報など、来店・応募判断に必要な情報を随時更新</p>
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 w-5 h-5 rounded-full bg-purple-100 text-purple-700 text-xs flex items-center justify-center font-bold flex-shrink-0">2</span>
                    <div>
                        <span class="font-medium text-gray-800">キャスト求人の管理</span>
                        <p class="text-gray-500 text-xs mt-0.5">女性向け求人情報の作成・更新・応募対応</p>
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 w-5 h-5 rounded-full bg-purple-100 text-purple-700 text-xs flex items-center justify-center font-bold flex-shrink-0">3</span>
                    <div>
                        <span class="font-medium text-gray-800">スタッフ求人の管理</span>
                        <p class="text-gray-500 text-xs mt-0.5">男性向け求人情報の作成・更新・応募対応</p>
                    </div>
                </li>
            </ul>
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 w-5 h-5 rounded-full bg-purple-100 text-purple-700 text-xs flex items-center justify-center font-bold flex-shrink-0">4</span>
                    <div>
                        <span class="font-medium text-gray-800">閉店店舗の削除</span>
                        <p class="text-gray-500 text-xs mt-0.5">閉店が確認された店舗のアカウントは速やかに閉鎖すること。閉店店舗は管理件数のカウント対象外となります。</p>
                    </div>
                </li>
            </ul>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-amber-800 text-xs mt-2">
                管理状況が著しく不十分と当社が判断した場合、管理代行契約を解除し、紹介代理店へ変更することがあります。
            </div>
        </div>
    </section>

    {{-- 営業トーク例 --}}
    <section>
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <span class="w-1 h-6 bg-purple-600 rounded-full inline-block"></span>
            そのまま使える営業トーク例
        </h2>
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 mb-2">基本（新規・既存どちらでも）</p>
                <p class="leading-relaxed text-gray-800">
                    「無料で営業情報と求人が出せる新しいサイトができました。まずは枠だけ押さえませんか？」
                </p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 mb-2">ホットリンク活用（既存媒体に依存している店舗向け）</p>
                <p class="leading-relaxed text-gray-800">
                    「今使っている営業サイト（シティ〇ブンなど）や自社HPにお客を流せる仕組みです。今の広告をやめる必要はありません。」
                </p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 mb-2">求人強化（人手不足に悩む店舗向け）</p>
                <p class="leading-relaxed text-gray-800">
                    「求人も出せて、反響が出たら上位表示に上げることもできます。キャストもスタッフも両方対応しています。」
                </p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 mb-2">クロージング</p>
                <p class="leading-relaxed text-gray-800">
                    「まずは無料登録だけでもOKです。掲載が増えてきたら無料枠では埋もれ始めます。先に枠を確保しておきましょう。」
                </p>
            </div>
        </div>
    </section>

    {{-- 今後の展開 --}}
    <section>
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <span class="w-1 h-6 bg-purple-600 rounded-full inline-block"></span>
            今後の展開
        </h2>
        <div class="bg-white rounded-xl shadow-sm p-6 leading-relaxed space-y-3">
            <p>
                ナイトビジネス支援プラットフォームとしての付加価値を高めるため、店舗運営に役立つメニューを順次拡充していく予定です。
            </p>
            <ul class="space-y-2 text-gray-600">
                <li class="flex items-center gap-2"><span class="text-purple-400">▸</span> 求人原稿作成代行</li>
                <li class="flex items-center gap-2"><span class="text-purple-400">▸</span> AI画像加工サービス</li>
                <li class="flex items-center gap-2"><span class="text-purple-400">▸</span> 名刺・ショップカード制作</li>
                <li class="flex items-center gap-2"><span class="text-purple-400">▸</span> SNS・動画運用サポート</li>
                <li class="flex items-center gap-2"><span class="text-purple-400">▸</span> HP・LP制作</li>
                <li class="flex items-center gap-2"><span class="text-purple-400">▸</span> 行政書士連携（許認可・法務サポート）</li>
            </ul>
            <p class="text-xs text-gray-400">上記はすべて検討・予定段階です。提供時期・条件は別途ご案内します。</p>
        </div>
    </section>

    {{-- 第一次パートナー募集 --}}
    <section>
        <div class="bg-gray-900 text-white rounded-2xl p-8 space-y-4 text-center">
            <p class="text-purple-300 text-xs font-medium tracking-widest">FIRST PARTNER RECRUITMENT</p>
            <h2 class="text-xl font-bold">第一次パートナー募集</h2>
            <p class="text-gray-300 leading-relaxed text-sm max-w-xl mx-auto">
                アップステージで一定以上ご協力いただいている代理店様だけのご案内ページです。
            </p>
            <p class="text-gray-300 leading-relaxed text-sm max-w-xl mx-auto">
                日本全国のナイトビジネス店舗すべての掲載が目標ですが、代理店様の数は必要なだけ絞りたいと考えています。
            </p>
            <p class="text-white font-bold text-base max-w-xl mx-auto border-t border-gray-700 pt-4 mt-2">
                共に市場を作り上げてくださる代理店様だけ、ご契約ください。
            </p>
        </div>
    </section>

    {{-- まとめ --}}
    <section>
        <div class="bg-gradient-to-br from-purple-900 to-gray-900 text-white rounded-2xl p-8 text-center space-y-4">
            <h2 class="text-xl font-bold">夜ビズは、代理店の「営業ツール」です</h2>
            <ul class="text-sm text-purple-200 space-y-2 text-left inline-block">
                <li class="flex items-center gap-2"><span class="text-purple-400">✓</span> 無料で始められる ― 店舗に断られる理由がない</li>
                <li class="flex items-center gap-2"><span class="text-purple-400">✓</span> 既存媒体と共存できる ― 今の営業を否定しない</li>
                <li class="flex items-center gap-2"><span class="text-purple-400">✓</span> 集客・求人を同時に強化できる</li>
                <li class="flex items-center gap-2"><span class="text-purple-400">✓</span> 先に動けば、エリアの主導権を持てる</li>
                <li class="flex items-center gap-2"><span class="text-purple-400">✓</span> 継続収益モデルを作れる</li>
                <li class="flex items-center gap-2"><span class="text-purple-400">✓</span> 管理店舗が増えるほどマージンが上がる（20% → 最大30%）</li>
            </ul>
        </div>
    </section>

</div>

@endsection
