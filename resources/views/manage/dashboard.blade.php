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
            <form action="{{ route('logout') }}/" method="POST">
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
                <form method="POST" action="{{ route('manage.switch-shop', $s->id) }}/">
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
        <p class="text-sm text-business-900 leading-relaxed">「<span class="font-bold">夜ビジ：デリヘルリスト</span>」は、ナイトビジネス全体を盛り上げるための営業支援サイトです。基本無料で利用できますので系列店やお知り合いのお店にご紹介をお願いいたします。</p>
    </div>

    {{-- メールバウンス警告 --}}
    @if(auth()->user()->email_bounced_at)
    <div class="bg-red-50 border border-red-300 rounded-xl px-5 py-4 mb-6 flex items-start gap-3">
        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div class="flex-1">
            <p class="text-sm font-bold text-red-800">登録メールアドレスにメールが届いていません</p>
            <p class="text-xs text-red-700 mt-1">
                <span class="font-mono bg-red-100 px-1 rounded">{{ auth()->user()->email }}</span>
                宛のメールが返送されています。<br>
                新しいメールアドレスへの変更、またはご不要の場合はそのままにしてください。
            </p>
            <a href="{{ route('manage.contact') }}/" class="inline-block mt-2 text-xs font-medium text-red-700 underline hover:text-red-900">
                メールアドレス変更のお問い合わせはこちら →
            </a>
        </div>
    </div>
    @endif

    {{-- メール認証完了メッセージ --}}
    @if(session('verified'))
    <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-4 mb-6 flex items-start gap-3">
        <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm font-bold text-green-800">登録が完了しました！</p>
            <p class="text-xs text-green-700 mt-0.5">ようこそ、デリヘルリストへ。まずは店舗情報を入力してください。</p>
        </div>
    </div>
    @endif

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
            <a href="{{ route('manage.applications.show', $thread->id) }}/"
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
                        キャスト情報を追加すると、デリヘルリスト上に店舗ページが公開されます。
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- 店舗名 + 全体ステータス --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            {{-- 上段：掲載店舗 店名 業種 --}}
            <div class="flex items-center gap-3 min-w-0 mb-3">
                <span class="text-xs text-gray-400 shrink-0">掲載店舗</span>
                <h2 class="text-base font-bold text-gray-800 truncate">{{ $shop->name }}</h2>
                @if($shop->genre)
                    <span class="text-xs text-gray-500 shrink-0">{{ $shop->genre->name }}</span>
                @endif
            </div>
            {{-- 下段：ステータス・申請ボタン --}}
            @if($shop->status === 'active')
                <span class="text-xs px-3 py-1 rounded-full font-medium bg-green-100 text-green-700">掲載中</span>
            @elseif($shop->status === 'pending')
                <span class="text-xs px-3 py-1 rounded-full font-medium bg-yellow-100 text-yellow-700">申請中（審査待ち）</span>
            @else
                @php $applyReady = $shop->address_locality && $shop->address; @endphp
                <div class="flex items-center gap-3">
                    <span class="text-xs px-3 py-1 rounded-full font-medium bg-gray-100 text-gray-500">非公開</span>
                    @if($applyReady)
                    <button type="button" onclick="document.getElementById('permit-modal').classList.remove('hidden')"
                            class="text-xs px-4 py-1.5 bg-business-700 hover:bg-business-600 text-white rounded-full font-medium transition">
                        掲載申請
                    </button>
                    @else
                    <a href="{{ route('manage.shop.edit') }}/"
                       class="text-xs px-4 py-1.5 bg-gray-300 text-gray-500 rounded-full font-medium cursor-not-allowed"
                       title="住所情報を入力してから申請できます">
                        掲載申請
                    </a>
                    @endif
                </div>
                @error('apply')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
                @if(! $applyReady)
                <p class="text-xs text-amber-600 mt-1">店舗情報・求人情報を全て入力してから、掲載申請をお願いします。</p>
                @endif
            @endif
        </div>

        {{--
        キャスト求人ブロック（将来使用予定・現在非表示）
        @php
            $castJobs = $shop->jobs->filter(fn($j) => in_array($j->search_group, ['female', 'both']));
            $activeCastJobs = $castJobs->where('status', 'active');
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
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
            <a href="{{ route('manage.cast.index') }}/" class="text-xs text-business-600 mt-3 hover:underline block">編集する →</a>
        </div>
        --}}
        {{-- 掲載権限 --}}
        @if($shop)
        @php
            $planLabels = [
                1 => ['label' => 'VIP',       'color' => 'bg-yellow-100 text-yellow-800 border-yellow-300'],
                2 => ['label' => 'ミドル',    'color' => 'bg-purple-100 text-purple-800 border-purple-300'],
                3 => ['label' => 'ベーシック','color' => 'bg-blue-100 text-blue-800 border-blue-300'],
                4 => ['label' => '無料上位',  'color' => 'bg-green-100 text-green-800 border-green-300'],
                5 => ['label' => '無料',      'color' => 'bg-gray-100 text-gray-600 border-gray-300'],
            ];
            $planInfo = $planLabels[$shop->plan] ?? $planLabels[5];
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-sm font-bold text-gray-700 mb-3">現在の掲載権限</h3>
            <span class="inline-block border px-4 py-1.5 rounded-full text-sm font-bold {{ $planInfo['color'] }}">
                {{ $planInfo['label'] }}
            </span>
            @if($shop->plan === 4)
            <p class="mt-3 text-xs text-gray-500 leading-relaxed">
                貴社HPへのバナー設置により上位表示されています。バナーが無い場合は下位表示となりますので予めご了承ください。
            </p>
            @elseif($shop->plan === 3 && $shop->is_banner_plan)
            <p class="mt-3 text-xs text-gray-500 leading-relaxed">
                貴社HPへのバナー設置によりベーシック掲載されています。バナーが無い場合は下位表示となりますので予めご了承ください。
            </p>
            @endif
        </div>
        @endif
        {{-- 有料掲載にすると変わること（無料店のみ） --}}
        @if(!$shop->hasBudget() && !$shop->isXmlActive())
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-sm font-bold text-gray-700 mb-3">有料掲載にすると変わること</h3>
            <ul class="space-y-2">
                <li class="flex items-start gap-2 text-xs text-gray-600"><span class="text-business-600 shrink-0 mt-0.5">✓</span>検索結果でカード形式（画像付き）・上位に表示（無料はテキストリストのみ）</li>
                <li class="flex items-start gap-2 text-xs text-gray-600"><span class="text-business-600 shrink-0 mt-0.5">✓</span>ランキングスコアにプランボーナスが加算され、注目度が上がる</li>
                <li class="flex items-start gap-2 text-xs text-gray-600"><span class="text-business-600 shrink-0 mt-0.5">✓</span>オフィシャルHPへのリンクを店舗ページに掲載</li>
                <li class="flex items-start gap-2 text-xs text-gray-600"><span class="text-business-600 shrink-0 mt-0.5">✓</span>口コミへの削除申請が可能（運営に依頼）<span class="text-gray-400">※内容の有利不利を問わず、正当な評価と判断されるものは削除できません</span></li>
                <li class="flex items-start gap-2 text-xs text-gray-600"><span class="text-business-600 shrink-0 mt-0.5">✓</span>近隣エリアの無料掲載店ページに「おすすめ店舗」として、無料店の在籍女性ページに「おすすめキャスト」として優先表示</li>
            </ul>
            <div class="mt-4">
                <button type="button"
                        onclick="alert('有料掲載のお申し込みは担当代理店の作業が必要です。\nお取引のある代理店様にご相談ください。')"
                        class="text-xs bg-business-700 hover:bg-business-600 text-white font-bold px-4 py-2 rounded-lg transition">有料掲載を始める →</button>
            </div>
        </div>
        @endif







        {{-- 新人通知登録人数 --}}
        @if($shop)
        <div class="bg-surface-500 border border-surface-300 rounded-xl p-5 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-[#E8E4DC] flex items-center gap-2">
                        <span class="w-1 h-4 bg-deli-500 rounded-full"></span>
                        新人通知の登録者数
                    </h2>
                    <p class="text-xs text-[#6A6A7E] mt-1">「この店舗の新人通知を受け取る」を登録しているユーザー数です。</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-3xl font-bold text-deli-400">{{ number_format($notifyCount) }}</p>
                    <p class="text-xs text-[#6A6A7E]">人</p>
                </div>
            </div>
            @if($notifyCount > 0)
            <p class="text-xs text-[#8A8A9E] mt-3 border-t border-surface-400 pt-3">
                新人キャストを登録すると、このユーザーに通知が届きます（通知機能は準備中）。
            </p>
            @else
            <p class="text-xs text-[#6A6A7E] mt-3 border-t border-surface-400 pt-3">
                まだ登録者はいません。魅力的な店舗ページを充実させて登録を増やしましょう。
            </p>
            @endif
        </div>
        @endif

        {{-- ユーザーの遊びやすい曜日・時間帯 --}}
        @if(!empty($scheduleStats) && $scheduleStats['total'] > 0)
        @php
            $dayLabels  = ['mon'=>'月','tue'=>'火','wed'=>'水','thu'=>'木','fri'=>'金','sat'=>'土','sun'=>'日'];
            $timeLabels = ['morning'=>'午前','afternoon'=>'昼間','evening'=>'夕方','night'=>'夜','midnight'=>'深夜'];
            $maxDay  = max($scheduleStats['days'])  ?: 1;
            $maxTime = max($scheduleStats['times']) ?: 1;
        @endphp
        <div class="bg-surface-500 border border-surface-300 rounded-xl p-5 mb-6">
            <h2 class="text-sm font-bold text-[#E8E4DC] mb-1 flex items-center gap-2">
                <span class="w-1 h-4 bg-gold-500 rounded-full"></span>
                ユーザーの遊びやすい曜日・時間帯
            </h2>
            <p class="text-xs text-[#6A6A7E] mb-4">{{ number_format($scheduleStats['total']) }}名の登録ユーザーが回答した集計です。出勤を組む際の参考にどうぞ。</p>

            <div class="mb-4">
                <p class="text-xs font-bold text-[#9A96A0] mb-2">曜日</p>
                <div class="flex gap-1.5 items-end">
                    @foreach($scheduleStats['days'] as $day => $cnt)
                    @php $pct = $maxDay > 0 ? round($cnt / $maxDay * 100) : 0; @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-[10px] text-[#6A6A7E]">{{ $cnt > 0 ? $cnt : '' }}</span>
                        <div class="w-full rounded-t transition" style="height:{{ max(4, $pct * 0.4) }}px; background:{{ $pct >= 80 ? '#E05A5A' : ($pct >= 50 ? '#E09050' : '#4A6A8A') }}"></div>
                        <span class="text-xs {{ $pct >= 80 ? 'text-deli-400 font-bold' : 'text-[#6A6A7E]' }}">{{ $dayLabels[$day] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="text-xs font-bold text-[#9A96A0] mb-2">時間帯</p>
                <div class="space-y-1.5">
                    @foreach($scheduleStats['times'] as $time => $cnt)
                    @php $pct = $maxTime > 0 ? round($cnt / $maxTime * 100) : 0; @endphp
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-[#8A8A9E] w-12 shrink-0">{{ $timeLabels[$time] }}</span>
                        <div class="flex-1 bg-surface-600 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all"
                                 style="width:{{ $pct }}%; background:{{ $pct >= 80 ? '#E05A5A' : ($pct >= 50 ? '#E09050' : '#4A6A8A') }}"></div>
                        </div>
                        <span class="text-xs text-[#6A6A7E] w-6 text-right">{{ $cnt }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- 公開ページへのリンク --}}
        @if($shop->status === 'active' && $shop->detail?->status === 'active')
            <div class="text-center">
                <a href="{{ route('shop.show', $shop->id) }}/"
                   target="_blank"
                   class="text-sm text-business-700 hover:underline">
                    公開中の店舗ページを確認 →
                </a>
            </div>
        @endif

    @endif
</div>

{{-- 他サービス紹介 --}}
<div class=bg-white rounded-xl shadow-sm p-6 mb-6>
    <h3 class=text-sm font-bold text-gray-700 mb-4>株式会社リストの他サービスもご活用ください</h3>
    <div class=space-y-3>

        <div class=flex items-start gap-3 p-4 rounded-lg border border-gray-100 hover:border-rose-200 hover:bg-rose-50 transition>
            <div class=shrink-0 w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xs font-bold>風</div>
            <div>
                <a href=https://fuzoku-list.com/ target=_blank rel=noopener
                   class=text-sm font-bold text-gray-800 hover:text-rose-600 hover:underline transition>
                    風俗リスト（fuzoku-list.com）
                </a>
                <p class=text-xs text-gray-500 mt-0.5>デリヘル・ヘルス・ソープ・箱ヘルなど風俗系店舗専門の求人・店舗情報サイト。当サービスと同時掲載で集客を最大化できます。無料から掲載スタート可能です。</p>
                <a href=https://fuzoku-list.com/register/ target=_blank rel=noopener
                   class=inline-block mt-2 text-xs text-rose-600 hover:underline font-medium>無料で掲載登録する →</a>
            </div>
        </div>

        <div class=flex items-start gap-3 p-4 rounded-lg border border-gray-100 hover:border-blue-200 hover:bg-blue-50 transition>
            <div class=shrink-0 w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold>男</div>
            <div>
                <a href=https://www.up-stage.info/ target=_blank rel=noopener
                   class=text-sm font-bold text-gray-800 hover:text-blue-600 hover:underline transition>
                    アップステージ（up-stage.info）
                </a>
                <p class=text-xs text-gray-500 mt-0.5>ホスト・ボーイ・黒服など男性ナイトワーク専門の求人・スカウトサイト。男性スタッフの採用をお考えの店舗様はぜひご活用ください。求人の無料掲載も対応しています。</p>
                <a href=https://www.up-stage.info/register/ target=_blank rel=noopener
                   class=inline-block mt-2 text-xs text-blue-600 hover:underline font-medium>求人を掲載する →</a>
            </div>
        </div>

        <div class=flex items-start gap-3 p-4 rounded-lg border border-gray-100 hover:border-purple-200 hover:bg-purple-50 transition>
            <div class=shrink-0 w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xs font-bold>M</div>
            <div>
                <a href=https://mens-v.com/ target=_blank rel=noopener
                   class=text-sm font-bold text-gray-800 hover:text-purple-600 hover:underline transition>
                    メンズバリュー（mens-value.com）
                </a>
                <p class=text-xs text-gray-500 mt-0.5>メンズエステ・リラクゼーション・アロマなど男性向けサービス店舗の求人・集客サイト。エステ系・リラクゼーション系の採用・集客にご活用ください。</p>
                <a href=https://mens-v.com/register/ target=_blank rel=noopener
                   class=inline-block mt-2 text-xs text-purple-600 hover:underline font-medium>無料で掲載登録する →</a>
            </div>
        </div>

    </div>
</div>


{{-- 届出書モーダル --}}
<div id="permit-modal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center px-4 hidden"
     onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
        <h2 class="text-base font-bold text-gray-800 mb-1">掲載申請</h2>
        <p class="text-xs text-gray-500 mb-5">以下のいずれかを選択して申請してください。</p>

        <form action="{{ route('manage.apply') }}/" method="POST" enctype="multipart/form-data" id="permit-form">
            @csrf

            <div class="space-y-3 mb-5">
                {{-- 届出書アップロード --}}
                <label class="flex gap-3 p-4 border-2 rounded-xl cursor-pointer transition has-[:checked]:border-business-600 has-[:checked]:bg-business-50 border-gray-200"
                       id="label-uploaded">
                    <input type="radio" name="permit_type" value="uploaded" class="mt-0.5 shrink-0 accent-business-600"
                           onchange="document.getElementById('upload-area').classList.remove('hidden');document.getElementById('agree-area').classList.add('hidden')">
                    <div>
                        <p class="text-sm font-bold text-gray-700">届出書・許可証を添付して申請</p>
                        <p class="text-xs text-gray-500 mt-0.5">警察への届出受理証や許可証のコピーをPDF・画像でアップロードしてください。</p>
                    </div>
                </label>

                <div id="upload-area" class="hidden pl-4">
                    <label class="block text-xs text-gray-600 mb-1">ファイルを選択（PDF / JPG / PNG、10MBまで）</label>
                    <input type="file" name="permit_file" accept=".pdf,.jpg,.jpeg,.png"
                           class="block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-business-100 file:text-business-700 hover:file:bg-business-200">
                    @error('permit_file')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 届出不要チェック --}}
                <label class="flex gap-3 p-4 border-2 rounded-xl cursor-pointer transition has-[:checked]:border-business-600 has-[:checked]:bg-business-50 border-gray-200"
                       id="label-not-required">
                    <input type="radio" name="permit_type" value="not_required" class="mt-0.5 shrink-0 accent-business-600"
                           onchange="document.getElementById('agree-area').classList.remove('hidden');document.getElementById('upload-area').classList.add('hidden')">
                    <div>
                        <p class="text-sm font-bold text-gray-700">当店の営業形態では届出・許可は不要です</p>
                        <p class="text-xs text-gray-500 mt-0.5">無店舗型性風俗特殊営業の届出が不要な業種など、法令上届出義務がない場合に選択してください。</p>
                    </div>
                </label>

                <div id="agree-area" class="hidden pl-4">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" name="permit_agree" value="1" class="mt-0.5 accent-business-600">
                        <span class="text-xs text-gray-600">当店の営業形態において、関係法令に基づく届出・許可は不要であることを確認しました。虚偽の申告は掲載停止の対象となる場合があります。</span>
                    </label>
                    @error('permit_agree')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @error('permit_type')
            <p class="text-xs text-red-600 mb-3">{{ $message }}</p>
            @enderror

            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 bg-business-700 hover:bg-business-600 text-white font-bold py-2.5 rounded-xl text-sm transition">
                    申請する
                </button>
                <button type="button" onclick="document.getElementById('permit-modal').classList.add('hidden')"
                        class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:bg-gray-50 transition">
                    キャンセル
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script @nonce>
// バリデーションエラー時はモーダルを自動で開く
@if($errors->has('permit_type') || $errors->has('permit_file') || $errors->has('permit_agree'))
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('permit-modal').classList.remove('hidden');
    @if(old('permit_type') === 'uploaded')
    document.querySelector('input[value="uploaded"]').checked = true;
    document.getElementById('upload-area').classList.remove('hidden');
    @elseif(old('permit_type') === 'not_required')
    document.querySelector('input[value="not_required"]').checked = true;
    document.getElementById('agree-area').classList.remove('hidden');
    @endif
});
@endif
</script>
@endpush
@endsection
