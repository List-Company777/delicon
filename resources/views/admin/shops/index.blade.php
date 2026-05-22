@extends('layouts.admin')

@section('title', '店舗審査')

@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-700">店舗審査</h1>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
        {{ session('success') }}
    </div>
@endif

{{-- ステータスタブ --}}
<div class="flex gap-1 mb-5 border-b border-gray-200">
    @foreach(['pending' => '申請中', 'active' => '掲載中', 'inactive' => '非公開', 'all' => 'すべて', 'missing' => '未設定'] as $s => $label)
    @php $tabParams = array_filter(['status' => $s, 'pref_id' => $prefId, 'plan' => $plan, 'keyword' => $keyword]); @endphp
    <a href="{{ route('admin.shops.index', $tabParams) }}"
       class="{{ $status === $s ? 'border-b-2 border-yellow-500 text-yellow-600 font-bold' : 'text-gray-500 hover:text-gray-700' }} px-4 py-2 text-sm transition -mb-px whitespace-nowrap">
        {{ $label }}
        <span class="ml-1 text-xs {{ $status === $s ? 'text-yellow-500' : 'text-gray-400' }}">{{ number_format($counts[$s]) }}</span>
    </a>
    @endforeach
    <a href="{{ route('admin.area-mismatch.index') }}/"
       class="{{ request()->routeIs('admin.area-mismatch.*') ? 'border-b-2 border-amber-500 text-amber-600 font-bold' : 'text-gray-500 hover:text-gray-700' }} px-4 py-2 text-sm transition -mb-px whitespace-nowrap">
        エリア修正</a>
    <a href="{{ route('admin.url-check.index') }}"
       class="{{ request()->routeIs('admin.url-check.*') ? 'border-b-2 border-red-500 text-red-600 font-bold' : 'text-gray-500 hover:text-gray-700' }} px-4 py-2 text-sm transition -mb-px whitespace-nowrap">
        URL確認
    </a>
    <a href="{{ route('admin.banner-check.index') }}"
       class="{{ request()->routeIs('admin.banner-check.*') ? 'border-b-2 border-orange-500 text-orange-600 font-bold' : 'text-gray-500 hover:text-gray-700' }} px-4 py-2 text-sm transition -mb-px whitespace-nowrap">
        バナー確認
    </a>
</div>

{{-- 絞り込みフォーム --}}
<form method="GET" action="{{ route('admin.shops.index') }}" class="bg-white rounded-xl shadow-sm px-4 py-3 mb-4 flex flex-wrap items-end gap-3">
    <input type="hidden" name="status" value="{{ $status }}">

    <div>
        <label class="block text-xs text-gray-400 mb-1">都道府県</label>
        <select name="pref_id" class="border border-gray-300 rounded px-2 py-1.5 text-xs focus:outline-none focus:border-yellow-400 w-36">
            <option value="">全都道府県</option>
            @foreach($prefectures as $pref)
                <option value="{{ $pref->id }}" {{ $prefId == $pref->id ? 'selected' : '' }}>{{ $pref->prefecture }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs text-gray-400 mb-1">掲載プラン</label>
        <select name="plan" class="border border-gray-300 rounded px-2 py-1.5 text-xs focus:outline-none focus:border-yellow-400 w-32">
            <option value="">全プラン</option>
            <option value="paid"  {{ $plan === 'paid' ? 'selected' : '' }}>有料（1〜3）</option>
            <option value="free"  {{ $plan === 'free' ? 'selected' : '' }}>無料（5）</option>
            <option value="1" {{ $plan == '1' ? 'selected' : '' }}>VIP</option>
            <option value="2" {{ $plan == '2' ? 'selected' : '' }}>ミドル</option>
            <option value="3" {{ $plan == '3' ? 'selected' : '' }}>ベーシック</option>
            <option value="4" {{ $plan == '4' ? 'selected' : '' }}>無料上位</option>
            <option value="5" {{ $plan == '5' ? 'selected' : '' }}>無料</option>
        </select>
    </div>

    <div>
        <label class="block text-xs text-gray-400 mb-1">店舗名</label>
        <input type="text" name="keyword" value="{{ $keyword }}" placeholder="キーワード"
               class="border border-gray-300 rounded px-2 py-1.5 text-xs focus:outline-none focus:border-yellow-400 w-44">
    </div>

    <button type="submit" class="px-4 py-1.5 bg-gray-700 text-white text-xs rounded hover:bg-gray-600 transition">絞り込む</button>
    @if($prefId || $plan || $keyword || $noArea)
        <a href="{{ route('admin.shops.index', ['status' => $status]) }}" class="px-3 py-1.5 text-xs text-gray-400 hover:text-gray-600">クリア</a>
    @endif
</form>

{{-- 小エリア未設定警告 --}}
@if($noAreaCount > 0)
<div class="bg-orange-50 border border-orange-200 rounded-lg px-4 py-3 mb-4 flex items-center justify-between text-sm">
    <span class="text-orange-700">⚠ 小エリア未設定の店舗が <strong>{{ $noAreaCount }}件</strong> あります</span>
    @if($noArea)
        <a href="{{ route('admin.shops.index', array_filter(['status' => $status, 'pref_id' => $prefId, 'plan' => $plan, 'keyword' => $keyword])) }}" class="text-orange-600 underline hover:text-orange-800 text-xs">絞り込み解除</a>
    @else
        <a href="{{ route('admin.shops.index', array_merge(array_filter(['status' => $status, 'pref_id' => $prefId, 'plan' => $plan, 'keyword' => $keyword]), ['no_area' => 1])) }}" class="text-orange-600 underline hover:text-orange-800 text-xs">この店舗を表示</a>
    @endif
</div>
@endif

@if($shops->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center text-gray-400">
        <p>該当する店舗はありません</p>
    </div>
@else
<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-10">ID</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">店舗名</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-24">プラン</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">エリア</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">担当者</th>
                @if($status === 'pending' || $status === 'all')
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-32">届出書</th>
                @endif
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-24">更新日</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-20">状態</th>
                <th class="px-4 py-3 w-28"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($shops as $shop)
            @php
                $planLabels = [
                    1 => ['label' => 'VIP',       'color' => 'bg-yellow-100 text-yellow-700'],
                    2 => ['label' => 'ミドル',    'color' => 'bg-purple-100 text-purple-700'],
                    3 => ['label' => 'ベーシック', 'color' => 'bg-blue-100 text-blue-700'],
                    4 => ['label' => '無料上位',  'color' => 'bg-green-100 text-green-700'],
                    5 => ['label' => '無料',      'color' => 'bg-gray-100 text-gray-500'],
                ];
                $planInfo = $planLabels[$shop->plan] ?? $planLabels[5];
            @endphp
            <tr class="{{ $shop->area_id ? 'hover:bg-gray-50' : 'bg-orange-50 hover:bg-orange-100' }} transition">
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $shop->id }}</td>
                <td class="px-4 py-3 font-medium">
                    <a href="{{ route('admin.shops.show', $shop->id) }}" class="text-gray-800 hover:underline">{{ $shop->name }}</a>
                    <select data-url="/admin/shops/{{ $shop->id }}/genre/"
                            data-field="genre_id"
                            class="text-xs border-0 bg-transparent {{ $shop->genre_id ? 'text-gray-400' : 'text-red-400' }} hover:text-gray-600 focus:outline-none cursor-pointer py-0 ml-1">
                        <option value="">— ジャンル未設定 —</option>
                        @foreach($genres as $g)
                            <option value="{{ $g->id }}" {{ $shop->genre_id == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                    <select data-url="/admin/shops/{{ $shop->id }}/shop-type/"
                            data-field="shop_type_id"
                            class="text-xs border-0 bg-transparent {{ $shop->shop_type_id ? 'text-gray-400' : 'text-red-400' }} hover:text-gray-600 focus:outline-none cursor-pointer py-0 ml-1">
                        <option value="">— 業種未設定 —</option>
                        @foreach($shopTypes as $t)
                            <option value="{{ $t->id }}" {{ $shop->shop_type_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $planInfo['color'] }}">{{ $planInfo['label'] }}</span>
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $shop->area?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-xs">
                    @php $owner = $shop->users->first(); @endphp
                    @if($owner)
                        <p class="text-gray-700">{{ $owner->name }}</p>
                        <p class="text-gray-400">{{ $owner->email }}</p>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>
                @if($status === 'pending' || $status === 'all')
                <td class="px-4 py-3 text-xs">
                    @if($shop->permit_type === 'uploaded')
                        @if($shop->permit_document_path)
                            <a href="{{ route('admin.shops.permit-download', $shop->id) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-600 border border-blue-200 rounded hover:bg-blue-100 transition text-xs font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                届出書を確認
                            </a>
                        @else
                            <span class="text-amber-500">ファイルなし</span>
                        @endif
                    @elseif($shop->permit_type === 'not_required')
                        <span class="inline-flex items-center gap-1 text-green-600 text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            届出不要（誓約済）
                        </span>
                    @else
                        <span class="text-gray-300 text-xs">未提出</span>
                    @endif
                </td>
                @endif
                <td class="px-4 py-3 text-xs text-gray-400">{{ $shop->updated_at->format('m/d H:i') }}</td>
                <td class="px-4 py-3">
                    @if($shop->status === 'pending')
                        <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full">申請中</span>
                    @elseif($shop->status === 'active')
                        <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full">掲載中</span>
                    @else
                        <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">非公開</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex gap-1.5">
                        @if($shop->status !== 'active')
                            <form action="{{ route('admin.shops.approve', $shop->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 bg-green-500 hover:bg-green-400 text-white text-xs rounded transition">承認</button>
                            </form>
                        @endif
                        @if($shop->status !== 'inactive')
                            <form action="{{ route('admin.shops.reject', $shop->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="px-2.5 py-1 bg-gray-200 hover:bg-gray-300 text-gray-600 text-xs rounded transition"
                                        onclick="return confirm('「{{ $shop->name }}」を非公開にしますか？')">非公開</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4 flex items-center justify-between">
    <p class="text-xs text-gray-400">{{ $shops->total() }} 件</p>
    {{ $shops->appends(request()->query())->links() }}
</div>
@endif


@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
document.querySelectorAll('select[data-url]').forEach(function(select) {
    select.addEventListener('change', function() {
        const url   = this.dataset.url;
        const field = this.dataset.field;
        const token = document.querySelector('meta[name=csrf-token]').content;
        const body  = new FormData();
        body.append('_method', 'PATCH');
        body.append('_token', token);
        body.append(field, this.value);
        const sel = this;
        fetch(url, { method: 'POST', body: body })
            .then(function(r) {
                if (r.ok || r.redirected) {
                    sel.className = sel.className.replace('text-red-400', 'text-gray-400');
                }
            });
    });
});
</script>
@endpush
@endsection
