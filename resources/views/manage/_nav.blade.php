@php
    $current = request()->routeIs('manage.*') ? request()->route()->getName() : '';
    // 未対応応募カウント（バナー＋タブバッジ共用）
    $unreadCount = 0;
    if (auth()->check()) {
        $_u = auth()->user();
        $_sid = $_u->isPartner() && session()->has('acting_shop_id')
            ? session('acting_shop_id')
            : (session('managing_shop_id') ?? $_u->shops()->wherePivot('role', 'owner')->value('shops.id'));
        if ($_sid) {
            $unreadCount = max(
                \App\Models\ApplicationMessage::whereHas('application', fn($q) => $q->where('shop_id', $_sid))
                    ->where('sender', 'applicant')->whereNull('read_at')->count(),
                \App\Models\Application::where('shop_id', $_sid)->where('status', 'new')->count()
            );
        }
    }
@endphp

{{-- 未対応応募バナー（応募管理ページ以外で表示） --}}
@if($unreadCount > 0 && !request()->routeIs('manage.applications.*'))
<div class="bg-red-50 border-b border-red-200">
    <div class="max-w-4xl mx-auto px-4 py-2 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <a href="{{ route('manage.applications.index') }}" class="text-sm text-red-700 font-medium hover:underline">
            未対応の応募が <span class="font-bold">{{ $unreadCount }}</span> 件あります → 確認する
        </a>
    </div>
</div>
@endif

{{-- 代理操作中バナー --}}
@if(auth()->user()->isPartner() && session()->has('acting_shop_id'))
@php $actingShop = \App\Models\Shop::find(session('acting_shop_id')); @endphp
<div class="bg-amber-50 border-b border-amber-200">
    <div class="max-w-4xl mx-auto px-4 py-2 flex items-center justify-between">
        <p class="text-sm text-amber-800">
            <span class="font-bold">代理操作中：{{ $actingShop?->name }}</span>
            <span class="text-amber-600 ml-2">（{{ auth()->user()->partner?->company_name }}）</span>
        </p>
        <form action="{{ route('manage.partner.stopActing') }}" method="POST">
            @csrf
            <button type="submit" class="text-xs text-amber-700 hover:underline font-medium">← 店舗一覧に戻る</button>
        </form>
    </div>
</div>
@endif

<nav class="bg-white border-b border-gray-200 mb-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="flex gap-1 overflow-x-auto text-sm py-1">
            @php
                $links = [
                    'manage.dashboard'          => 'ダッシュボード',
                    'manage.shop.edit'          => '基本情報',
                    'manage.shop.image'         => 'メイン画像',
                    'manage.business.edit'      => '営業情報',
                    'manage.cast.index'         => 'キャスト求人',
                    'manage.staff.index'        => 'スタッフ求人',
                    'manage.applications.index' => '応募管理',
                    'manage.paid-plan'          => '掲載プラン',
                    'manage.contact'            => 'お問い合わせ',
                    'manage.password.edit'      => 'パスワード変更',
                ];
            @endphp
            @foreach($links as $route => $label)
                <a href="{{ route($route) }}"
                   @class([
                       'whitespace-nowrap px-3 py-3 border-b-2 transition',
                       'border-business-600 text-business-700 font-medium' => request()->routeIs($route) || ($route === 'manage.applications.index' && request()->routeIs('manage.applications.*')),
                       'border-transparent text-gray-500 hover:text-gray-700' => !(request()->routeIs($route) || ($route === 'manage.applications.index' && request()->routeIs('manage.applications.*'))),
                   ])>
                    {{ $label }}
                    @if($route === 'manage.applications.index' && $unreadCount > 0)
                        <span class="ml-1 text-xs bg-red-500 text-white rounded-full px-1.5 py-0.5 font-bold">{{ $unreadCount }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</nav>
