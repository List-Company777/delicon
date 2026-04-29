@extends('layouts.app')

@section('title', '店舗管理ダッシュボード')

@section('content')
<div class="bg-business-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold text-lg">店舗管理</h1>
        <div class="flex items-center gap-4 text-sm">
            <span class="opacity-70">
                @if($shop)
                    {{ $shop->name }}<span class="opacity-60 ml-1 text-xs">#{{ $shop->id }}</span>
                @else
                    {{ auth()->user()->name }}
                @endif
            </span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="opacity-70 hover:opacity-100 transition">ログアウト</button>
            </form>
        </div>
    </div>
</div>

@include('manage._nav')

{{-- 複数店舗セレクタ --}}
@if(isset($ownedShops) && $ownedShops->count() > 1)
<div class="bg-gray-50 border-b border-gray-200">
    <div class="max-w-4xl mx-auto px-4 py-2 flex items-center gap-2 flex-wrap">
        <span class="text-xs text-gray-500 shrink-0">管理する店舗：</span>
        @foreach($ownedShops as $s)
            @if($s->id === $shop?->id)
                <span class="text-xs px-3 py-1 rounded-full bg-business-700 text-white font-medium">{{ $s->name }}</span>
            @else
                <form method="POST" action="{{ route('manage.switch-shop', $s->id) }}">
                    @csrf
                    <button type="submit" class="text-xs px-3 py-1 rounded-full bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 transition">{{ $s->name }}</button>
                </form>
            @endif
        @endforeach
    </div>
</div>
@endif

<div class="max-w-4xl mx-auto px-4 py-8">

    {{-- コンセプトバナー --}}
    <div class="bg-business-50 border border-business-200 rounded-xl px-5 py-4 mb-6">
        <p class="text-sm text-business-900 leading-relaxed">「<span class="font-bold">夜ビジ：ナイトワークリスト</span>」は、ナイトビジネス全体を盛り上げるための営業支援サイトです。基本無料で利用できますので系列店やお知り合いのお店にご紹介をお願いいたします。</p>
    </div>

    {{-- 未読返信通知 --}}
    @if($unreadThreads->isNotEmpty())
    <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 mb-6">
        <div class="flex items-center gap-2 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm font-bold text-red-700">未読の返信が {{ $unreadThreads->count() }} 件あります</p>
        </div>
        <div class="space-y-2">
            @foreach($unreadThreads as $thread)
            <a href="{{ route('manage.applications.show', $thread->id) }}"
               class="flex items-center justify-between bg-white rounded-lg px-4 py-2.5 hover:bg-red-50 transition border border-red-100 group">
                <div class="min-w-0">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-red-700">{{ $thread->applicant_name }}</span>
                    <span class="text-xs text-gray-400 ml-2">{{ $thread->messages->first()?->created_at->format('m/d H:i') }}</span>
                    @if($thread->messages->first())
                        <p class="text-xs text-gray-500 truncate mt-0.5">{{ $thread->messages->first()->body }}</p>
                    @endif
                </div>
                <span class="text-xs text-red-600 font-medium shrink-0 ml-3">確認する →</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if(session('applied'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-6 text-sm">
            掲載申請を送信しました。審査完了後にメールでご連絡します。
        </div>
    @endif

    @if(session('plan_applied'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-6 text-sm">
            有料プランの申し込みを送信しました。管理者が確認後、予算が追加されます。
        </div>
    @endif

    @if(session('bid_price_updated'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-6 text-sm">
            入札単価を更新しました。
        </div>
    @endif

    @if(! $shop)
        <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-500">
            <p class="text-lg font-bold mb-2">店舗が登録されていません</p>
            <p class="text-sm">登録情報をご確認ください</p>
        </div>
    @else

        {{-- XML連携バナー --}}
        @if($shop->xml_source === 'upstage')
        @php
            $xmlStaffCount = $shop->jobs->filter(fn($j) => $j->xml_source !== 'manual' && in_array($j->search_group, ['male', 'both']))->count();
        @endphp
        <div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-4 mb-6">
            <div class="flex items-start gap-3">
                <div class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold">i</div>
                <div>
                    <p class="text-sm font-bold text-blue-800 mb-1"><a href="https://www.up-stage.info/" target="_blank" rel="noopener" class="underline hover:text-blue-900">アップステージ</a>と連携中のお店です</p>
                    <p class="text-xs text-blue-700 leading-relaxed">
                        ボーイ求人{{ $xmlStaffCount }}件が<a href="https://www.up-stage.info/" target="_blank" rel="noopener" class="underline">アップステージ</a>から自動で連携されています。<br>
                        営業情報とキャスト求人を追加すると、ナイトワークリスト上に店舗ページが公開されます。
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- 店舗名 + 全体ステータス --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs text-gray-400 mb-1">掲載店舗</p>
                    <h2 class="text-xl font-bold text-gray-800">{{ $shop->name }}</h2>
                    @if($shop->genre)
                        <p class="text-sm text-gray-500 mt-0.5">{{ $shop->genre->name }}</p>
                    @endif
                </div>
                @if($shop->status === 'active')
                    <span class="text-xs px-3 py-1 rounded-full font-medium bg-green-100 text-green-700">掲載中</span>
                @elseif($shop->status === 'pending')
                    <span class="text-xs px-3 py-1 rounded-full font-medium bg-yellow-100 text-yellow-700">申請中（審査待ち）</span>
                @else
                    @php $applyReady = $shop->address_locality && $shop->address; @endphp
                    <div class="flex items-center gap-3">
                        <span class="text-xs px-3 py-1 rounded-full font-medium bg-gray-100 text-gray-500">非公開</span>
                        @if($applyReady)
                        <form action="{{ route('manage.apply') }}/" method="POST">
                            @csrf
                            <button type="submit"
                                    class="text-xs px-4 py-1.5 bg-business-700 hover:bg-business-600 text-white rounded-full font-medium transition"
                                    onclick="return confirm('掲載申請を送信しますか？')">
                                掲載申請する
                            </button>
                        </form>
                        @else
                        <a href="{{ route('manage.shop.edit') }}"
                           class="text-xs px-4 py-1.5 bg-gray-300 text-gray-500 rounded-full font-medium cursor-not-allowed"
                           title="住所情報を入力してから申請できます">
                            掲載申請する
                        </a>
                        @endif
                    </div>
                    @error('apply')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    @if(! $applyReady)
                    <p class="text-xs text-amber-600 mt-1">
                        申請前に <a href="{{ route('manage.shop.edit') }}" class="underline">店舗情報</a> で市区町村・番地を入力してください。
                    </p>
                    @endif
                @endif
            </div>
        </div>

        {{-- セクション別ステータス --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

            {{-- 営業情報 --}}
            @php $detail = $shop->detail; @endphp
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-gray-700">営業情報</h3>
                    @if($detail)
                        <span @class([
                            'text-xs px-2 py-0.5 rounded-full',
                            'bg-green-100 text-green-700' => $detail->status === 'active',
                            'bg-gray-100 text-gray-400'   => $detail->status !== 'active',
                        ])>{{ $detail->status === 'active' ? '公開中' : '非公開' }}</span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700">未登録</span>
                    @endif
                </div>
                @if($detail)
                    <p class="text-xs text-gray-500">
                        @if($shop->setPrices->isNotEmpty())
                            セット料金：{{ $shop->setPrices->count() }}件登録<br>
                        @elseif($detail->set_price)
                            セット料金：{{ $detail->set_price }}<br>
                        @endif
                        @if($detail->opening_hours) {{ $detail->opening_hours }}〜{{ $detail->closing_hours }} @endif
                    </p>
                @endif
                <a href="{{ route('manage.business.edit') }}" class="text-xs text-business-600 mt-3 hover:underline block">編集する →</a>
            </div>

            {{-- キャスト求人 --}}
            @php
                $castJobs = $shop->jobs->filter(fn($j) => in_array($j->search_group, ['female', 'both']));
                $activeCastJobs = $castJobs->where('status', 'active');
            @endphp
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-gray-700">キャスト求人</h3>
                    @if($castJobs->count() > 0)
                        <span @class([
                            'text-xs px-2 py-0.5 rounded-full',
                            'bg-green-100 text-green-700' => $activeCastJobs->count() > 0,
                            'bg-gray-100 text-gray-400'   => $activeCastJobs->count() === 0,
                        ])>{{ $activeCastJobs->count() > 0 ? '公開中' : '非公開' }}</span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700">未登録</span>
                    @endif
                </div>
                <p class="text-xs text-gray-500">
                    {{ $castJobs->count() }}件登録
                    @if($castJobs->count() > 0)・公開中 {{ $activeCastJobs->count() }}件@endif
                </p>
                <a href="{{ route('manage.cast.index') }}" class="text-xs text-business-600 mt-3 hover:underline block">編集する →</a>
            </div>

            {{-- スタッフ求人 --}}
            @php
                $staffJobs = $shop->jobs->filter(fn($j) => in_array($j->search_group, ['male']));
                $activeStaffJobs = $staffJobs->where('status', 'active');
            @endphp
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-gray-700">スタッフ求人</h3>
                    @if($staffJobs->count() > 0)
                        <span @class([
                            'text-xs px-2 py-0.5 rounded-full',
                            'bg-green-100 text-green-700' => $activeStaffJobs->count() > 0,
                            'bg-gray-100 text-gray-400'   => $activeStaffJobs->count() === 0,
                        ])>{{ $activeStaffJobs->count() > 0 ? '公開中' : '非公開' }}</span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700">未登録</span>
                    @endif
                </div>
                <p class="text-xs text-gray-500">
                    {{ $staffJobs->count() }}件登録
                    @if($staffJobs->count() > 0)・公開中 {{ $activeStaffJobs->count() }}件@endif
                </p>
                <a href="{{ route('manage.staff.index') }}" class="text-xs text-business-600 mt-3 hover:underline block">編集する →</a>
            </div>

        </div>

        {{-- LINE通知設定 --}}
        @if($shop->status === 'active')
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6" x-data>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-gray-700">LINE応募通知</h3>
                @if($shop->line_notify_user_id)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">設定済み</span>
                @else
                    <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700">未設定</span>
                @endif
            </div>

            @if($shop->line_notify_user_id)
                <p class="text-xs text-gray-500 mb-3">応募があるとこのLINEアカウントに通知が届きます。</p>
                <form method="POST" action="{{ route('manage.line-notify.remove') }}"
                      onsubmit="return confirm('LINE通知設定を解除しますか？')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:underline">解除する</button>
                </form>
            @else
                <p class="text-xs text-gray-500 mb-4">設定すると、応募があったときにLINEで通知を受け取れます。</p>

                @if(config('services.line.bot_add_friend_url'))
                <div class="space-y-4">
                    {{-- STEP 1: コードをコピー --}}
                    <div class="flex items-start gap-3">
                        <span class="shrink-0 w-5 h-5 rounded-full text-white text-xs flex items-center justify-center font-bold" style="background-color:#06C755">1</span>
                        <div class="flex-1">
                            <p class="text-xs text-gray-600 mb-2">以下のコードをコピーしてください</p>
                            <div class="flex items-center gap-2">
                                <code class="bg-gray-100 text-gray-800 text-sm font-mono px-3 py-1.5 rounded select-all">NW-{{ $shop->id }}</code>
                                <button type="button"
                                        @click="navigator.clipboard.writeText('NW-{{ $shop->id }}').then(() => { $el.textContent = 'コピーしました'; setTimeout(() => $el.textContent = 'コピー', 1500) })"
                                        class="text-xs text-business-600 border border-business-200 px-2 py-1 rounded hover:bg-business-50 transition">
                                    コピー
                                </button>
                            </div>
                        </div>
                    </div>
                    {{-- STEP 2: 友だち追加してコードを送る --}}
                    <div class="flex items-start gap-3">
                        <span class="shrink-0 w-5 h-5 rounded-full text-white text-xs flex items-center justify-center font-bold" style="background-color:#06C755">2</span>
                        <div>
                            <p class="text-xs text-gray-600 mb-2">公式LINEを友だち追加して、コードをトークに送ってください</p>
                            <a href="{{ config('services.line.bot_add_friend_url') }}"
                               target="_blank" rel="noopener"
                               class="flex items-center justify-center gap-2 text-white text-sm font-bold px-4 py-1 rounded-lg hover:opacity-90 transition w-full"
                               style="background-color:#06C755">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.281.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/></svg>
                                LINEで友だち追加
                            </a>
                        </div>
                    </div>
                </div>
                @else
                    <p class="text-xs text-gray-400">LINE通知は現在準備中です。</p>
                @endif
            @endif

            @if(session('line_notify_removed'))
                <p class="text-xs text-green-600 mt-2">LINE通知設定を解除しました。</p>
            @endif
        </div>
        @endif

        {{-- 有料掲載（リンク誘導） --}}
        @if($shop->status === 'active')
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-700 mb-1">有料掲載・入札単価</h3>
                    <div class="flex items-center gap-4 mt-2">
                        <div>
                            <p class="text-xs text-gray-400">予算残高</p>
                            <p class="text-lg font-bold text-gray-800">{{ number_format($shop->budget_balance) }}<span class="text-xs font-normal text-gray-500 ml-1">円</span></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">入札単価</p>
                            @if($shop->hasBudget())
                                <p class="text-lg font-bold text-business-700">{{ number_format($shop->bid_price) }}<span class="text-xs font-normal text-gray-500 ml-1">円</span></p>
                            @else
                                <p class="text-lg font-bold text-gray-400">無料</p>
                            @endif
                        </div>
                    </div>
                </div>
                <a href="{{ route('manage.paid-plan') }}"
                   class="text-xs text-business-600 hover:underline shrink-0">
                    詳細・設定 →
                </a>
            </div>
        </div>
        @endif

        {{-- 公開ページへのリンク --}}
        @if($shop->status === 'active' && $shop->detail?->status === 'active')
            <div class="text-center">
                <a href="{{ route('shop.show', $shop->id) }}"
                   target="_blank"
                   class="text-sm text-business-700 hover:underline">
                    公開中の店舗ページを確認 →
                </a>
            </div>
        @endif

    @endif
</div>
@endsection
