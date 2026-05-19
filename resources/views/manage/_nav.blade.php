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

{{-- 応募管理バナー（非表示中） --}}

{{-- 代理操作中バナー --}}
@if(auth()->user()->isPartner() && session()->has('acting_shop_id'))
@php $actingShop = \App\Models\Shop::find(session('acting_shop_id')); @endphp
<div class="bg-amber-400 border-b-2 border-amber-500 px-4 py-3">
    <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-start sm:items-center gap-3">
        <div class="flex items-center gap-2 flex-1">
            <span class="text-amber-900 font-bold text-sm">⚠ 代理操作中：{{ $actingShop?->name }}</span>
            <span class="text-amber-800 text-sm">（{{ auth()->user()->partner?->company_name }}）</span>
        </div>
        <form action="{{ route('manage.partner.stopActing') }}/" method="POST" class="shrink-0">
            @csrf
            <button type="submit" class="bg-gray-900 text-white text-sm font-bold px-4 py-2 rounded-lg hover:bg-gray-700 whitespace-nowrap">← 代理店ページに戻る</button>
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
                    'manage.cast-profile.index' => '在籍キャスト',
                    'manage.diaries.index'      => '写メ日記',
                    'manage.shift-requests.index' => 'シフト申請',
                    'manage.review.index'       => '口コミ管理',
                    'manage.cast-analytics.index' => '女性統計',
                    'manage.shop.news.index'    => 'お知らせ',
                    'manage.contact'            => 'お問い合わせ',
                    'manage.password.edit'      => 'パスワード変更',
                ];
            @endphp
            @foreach($links as $route => $label)
                <a href="{{ route($route) }}/"
                   @class([
                       'whitespace-nowrap px-3 py-3 border-b-2 transition',
                       'border-business-600 text-business-700 font-medium' => request()->routeIs($route) || ($route === 'manage.applications.index' && request()->routeIs('manage.applications.*')) || ($route === 'manage.cast-profile.index' && request()->routeIs('manage.cast-profile.*')) || ($route === 'manage.shop.news.index' && request()->routeIs('manage.shop.news.*')) || ($route === 'manage.diaries.index' && request()->routeIs('manage.diaries.*')) || ($route === 'manage.shift-requests.index' && request()->routeIs('manage.shift-requests.*')) || ($route === 'manage.cast-analytics.index' && request()->routeIs('manage.cast-analytics.*')),
                       'border-transparent text-gray-500 hover:text-gray-700' => !(request()->routeIs($route) || ($route === 'manage.applications.index' && request()->routeIs('manage.applications.*')) || ($route === 'manage.cast-profile.index' && request()->routeIs('manage.cast-profile.*')) || ($route === 'manage.shop.news.index' && request()->routeIs('manage.shop.news.*')) || ($route === 'manage.diaries.index' && request()->routeIs('manage.diaries.*')) || ($route === 'manage.shift-requests.index' && request()->routeIs('manage.shift-requests.*')) || ($route === 'manage.cast-analytics.index' && request()->routeIs('manage.cast-analytics.*'))),
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
