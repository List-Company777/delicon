@extends('layouts.app')

@section('title', '掲載プラン')

@section('content')
<div class="bg-business-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold text-lg">店舗管理</h1>
        <div class="flex items-center gap-4 text-sm">
            <span class="opacity-70">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}/" method="POST">
                @csrf
                <button type="submit" class="opacity-70 hover:opacity-100 transition">ログアウト</button>
            </form>
        </div>
    </div>
</div>

@include('manage._nav')

<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

    @if(session('plan_applied'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            有料プランの申し込みを送信しました。管理者が確認後、予算が追加されます。
        </div>
    @endif
    @if(session('bid_price_updated'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            入札単価を更新しました。
        </div>
    @endif

    @if($shop->status !== 'active')
        <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
            <p class="text-sm">掲載審査が完了すると有料掲載の設定ができます。</p>
        </div>
    @else

    {{-- 残高・入札単価 --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-sm font-bold text-gray-700 mb-4">予算・入札単価</h2>
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-1">予算残高</p>
                <p class="text-2xl font-bold text-gray-800">
                    {{ number_format($shop->budget_balance) }}<span class="text-sm font-normal text-gray-500 ml-1">円</span>
                </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-1">現在の入札単価</p>
                @if(!$shop->hasBudget())
                    <p class="text-2xl font-bold text-gray-400">無料</p>
                    <p class="text-xs text-gray-400 mt-1">基本掲載中（費用なし）</p>
                @else
                    <p class="text-2xl font-bold text-business-700">
                        {{ number_format($shop->bid_price) }}<span class="text-sm font-normal text-gray-500 ml-1">円</span>
                    </p>
                @endif
            </div>
        </div>

        {{-- 入札単価変更 --}}
        @if($shop->hasBudget())
            <form action="{{ route('manage.bid-price.update') }}/" method="POST">
                @csrf
                @method('PATCH')
                <label class="text-xs text-gray-600 block mb-1">入札単価を変更する（30〜9,990円・10円単位）</label>
                @error('bid_price')
                    <p class="text-xs text-red-600 mb-1">{{ $message }}</p>
                @enderror
                <div class="flex gap-2 items-center">
                    <input type="number" name="bid_price" value="{{ $shop->bid_price }}"
                           min="30" max="{{ min(9990, $shop->budget_balance) }}" step="10"
                           class="border border-gray-300 rounded px-3 py-1.5 text-sm w-32 focus:outline-none focus:border-business-500">
                    <span class="text-sm text-gray-500">円</span>
                    <button type="submit"
                            class="bg-business-700 hover:bg-business-600 text-white text-xs px-4 py-1.5 rounded transition">
                        更新
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-1">残高（{{ number_format($shop->budget_balance) }}円）を超えない範囲で設定できます</p>
            </form>
        @endif
    </div>

    {{-- クリック数 --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-sm font-bold text-gray-700 mb-4">クリック数</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-1">求人クリック（今月）</p>
                <p class="text-xl font-bold text-gray-800">
                    {{ number_format($totalJobClicksMonth) }}<span class="text-xs font-normal text-gray-500 ml-1">件</span>
                </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-1">求人クリック（30日）</p>
                <p class="text-xl font-bold text-gray-800">
                    {{ number_format($totalJobClicks30d) }}<span class="text-xs font-normal text-gray-500 ml-1">件</span>
                </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-1">夜遊びクリック（今月）</p>
                <p class="text-xl font-bold text-gray-800">
                    {{ number_format($totalShopClicksMonth) }}<span class="text-xs font-normal text-gray-500 ml-1">件</span>
                </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-1">夜遊びクリック（30日）</p>
                <p class="text-xl font-bold text-gray-800">
                    {{ number_format($totalShopClicks30d) }}<span class="text-xs font-normal text-gray-500 ml-1">件</span>
                </p>
            </div>
        </div>

        {{-- 積み上げ棒グラフ（Chart.js） --}}
        <div class="relative h-44">
            <canvas id="clickChart"></canvas>
        </div>
        <div class="flex items-center gap-4 mt-2 justify-end">
            <span class="flex items-center gap-1 text-xs text-gray-500">
                <span class="w-3 h-3 rounded-sm inline-block" style="background:rgba(91,55,135,0.75)"></span>求人
            </span>
            <span class="flex items-center gap-1 text-xs text-gray-500">
                <span class="w-3 h-3 rounded-sm inline-block" style="background:rgba(234,128,40,0.75)"></span>夜遊び
            </span>
            <span class="text-xs text-gray-400">過去30日間・重複除外済み</span>
        </div>
    </div>

    {{-- 有料プラン申し込み --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-sm font-bold text-gray-700 mb-4">予算を追加する（有料プラン）</h2>

        @if($pendingApplication)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 text-sm text-yellow-800">
                有料プランの申し込みを受け付けました（審査中）。管理者が確認後、予算が追加されます。
            </div>
        @else
            <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm">
                <p class="font-bold text-red-700 mb-1">チャージ後の返金はできません</p>
                <p class="text-red-600 text-sm">一度チャージされた予算残高は、掲載取りやめ・退会・残高未消化の場合を含め、<span class="font-bold">いかなる理由があっても返金に応じることができません</span>。<a href="{{ route('advertiser') }}/" target="_blank" class="underline">掲載規約 第4条</a>もあわせてご確認ください。</p>
            </div>
            <div class="mb-4 text-xs text-gray-500 leading-relaxed">
                予算追加額と希望入札単価を入力して申し込んでください。<br>
                申し込み後、振込先をメールでご案内します。入金確認後に予算が追加されます。<br>
                <span class="text-gray-600">※振込金額は予算追加額に消費税10%を加算した金額です。</span><br>
                <span class="text-gray-600">※ホットリンクオプションをご利用の場合、クリック時の消費予算は入札単価に自動で20円加算されます。</span>
            </div>
            <div x-data="{
                amount: {{ old('amount', 0) }},
                bidPrice: {{ old('bid_price_requested', $shop->bid_price > 10 ? $shop->bid_price : 0) }},
                confirming: false,
                get tax() { return Math.round(this.amount * 0.1); },
                get total() { return Math.round(this.amount * 1.1); },
                get isValid() { return this.amount >= 10000 && this.bidPrice >= 30 && this.bidPrice <= 9990; },
                showConfirm() { if (this.isValid) this.confirming = true; }
            }">
                {{-- 入力フォーム --}}
                <form action="{{ route('manage.plan.apply') }}/" method="POST" class="space-y-4"
                      x-show="!confirming" @submit.prevent="showConfirm()">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">予算追加額（円）<span class="text-gray-400">※最低10,000円〜</span></label>
                            <input type="number" name="amount" x-model.number="amount"
                                   min="10000" max="999999" step="1000" placeholder="例：10000"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-business-500">
                            <p class="text-xs mt-1" x-show="amount >= 10000">
                                <span class="text-gray-500">振込金額（税込）：</span>
                                <span class="font-bold text-gray-800" x-text="'¥' + total.toLocaleString()"></span>
                                <span class="text-gray-400" x-text="'（消費税 ¥' + tax.toLocaleString() + '）'"></span>
                            </p>
                            @error('amount')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">希望入札単価（円）<span class="text-gray-400">※30円〜・10円刻み</span></label>
                            <input type="number" name="bid_price_requested" x-model.number="bidPrice"
                                   min="30" max="9990" step="10" placeholder="例：50"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-business-500">
                            <p class="text-xs text-gray-400 mt-1">単価が高いほど検索結果で上位に表示されます</p>
                            @error('bid_price_requested')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <button type="submit"
                            class="bg-business-700 hover:bg-business-600 text-white text-sm px-6 py-2 rounded transition">
                        内容を確認する
                    </button>
                </form>

                {{-- 確認画面 --}}
                <div x-show="confirming" x-cloak>
                    <div class="border border-gray-200 rounded-lg divide-y divide-gray-100 mb-5 text-sm">
                        <div class="flex justify-between px-4 py-3">
                            <span class="text-gray-500">予算追加額</span>
                            <span class="font-bold text-gray-800" x-text="'¥' + amount.toLocaleString()"></span>
                        </div>
                        <div class="flex justify-between px-4 py-3">
                            <span class="text-gray-500">消費税（10%）</span>
                            <span class="text-gray-700" x-text="'¥' + tax.toLocaleString()"></span>
                        </div>
                        <div class="flex justify-between px-4 py-3 bg-gray-50">
                            <span class="text-gray-700 font-bold">振込金額（税込）</span>
                            <span class="font-bold text-gray-900 text-base" x-text="'¥' + total.toLocaleString()"></span>
                        </div>
                        <div class="flex justify-between px-4 py-3">
                            <span class="text-gray-500">希望入札単価</span>
                            <span class="font-bold text-gray-800" x-text="bidPrice + ' 円'"></span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">上記の内容で申し込みます。申し込み後に振込先をメールでご案内します。</p>
                    <div class="flex items-center gap-3">
                        <form action="{{ route('manage.plan.apply') }}/" method="POST">
                            @csrf
                            <input type="hidden" name="amount" :value="amount">
                            <input type="hidden" name="bid_price_requested" :value="bidPrice">
                            <button type="submit"
                                    class="bg-business-700 hover:bg-business-600 text-white text-sm px-6 py-2 rounded transition">
                                申し込みを確定する
                            </button>
                        </form>
                        <button type="button" @click="confirming = false"
                                class="text-sm text-gray-500 hover:text-gray-700 underline">
                            戻って修正する
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @endif {{-- active --}}

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js" @nonce></script>
<script @nonce>
new Chart(document.getElementById('clickChart'), {
    type: 'bar',
    data: {
        labels: @json($labels),
        datasets: [
            {
                label: '求人',
                data: @json($jobCounts),
                backgroundColor: 'rgba(91, 55, 135, 0.75)',
                borderRadius: 2,
            },
            {
                label: '夜遊び',
                data: @json($shopCounts),
                backgroundColor: 'rgba(234, 128, 40, 0.75)',
                borderRadius: 2,
            },
        ]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            x: { stacked: true, ticks: { font: { size: 10 }, maxRotation: 0 } },
            y: { stacked: true, beginAtZero: true, ticks: { precision: 0, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.05)' } }
        },
        maintainAspectRatio: false,
    }
});
</script>
@endpush
