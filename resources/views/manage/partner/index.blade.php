@extends('layouts.app')
@section('title', '紹介店舗一覧')
@section('content')
<div class="bg-business-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <div>
            <p class="text-xs opacity-70">代理店ポータル</p>
            <h1 class="font-bold">{{ $partner->company_name }}</h1>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="text-sm opacity-70 hover:opacity-100">ログアウト</button>
        </form>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-8">
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-800">
                {{ $partner->isManagement() ? '管理店舗一覧' : '紹介店舗一覧' }}
            </h2>
            <p class="text-sm text-gray-500 mt-0.5">
                管理件数 <span class="font-bold text-gray-700">{{ $totalCount }}</span> 件
                <span class="mx-1 text-gray-300">：</span>
                非公開 <span class="font-bold text-gray-500">{{ $nonPublicCount }}</span> 件
            </p>
        </div>
        <div class="text-sm text-gray-500 text-right">
            @if($partner->isManagement())
                <p>マージン率：<span class="font-bold text-gray-800">{{ $partner->commissionRatePercent() }}%</span></p>
                <p class="text-xs text-gray-400 mt-0.5">掲載中 {{ $activeCount }} 件をもとに算出</p>
            @else
                <p>手数料率：<span class="font-bold text-gray-800">{{ $partner->commissionRatePercent() }}%</span></p>
            @endif
        </div>
    </div>

    @if($partner->isManagement() && $nonPublicCount > 0)
    <div class="mb-5 bg-yellow-50 border border-yellow-200 text-yellow-800 text-sm px-4 py-3 rounded-lg">
        ※ 重複・閉店店舗は件数に含まれません。速やかに整理をお願いします。
    </div>
    @endif

    {{-- 店舗名検索 --}}
    <form method="GET" action="{{ route('manage.partner.index') }}" class="mb-4 flex gap-2">
        <input type="text" name="keyword" value="{{ $keyword }}" placeholder="店舗名で絞り込み"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500 w-64">
        <button type="submit" class="px-4 py-2 bg-business-700 text-white text-sm rounded-lg hover:bg-business-600 transition">検索</button>
        @if($keyword)
            <a href="{{ route('manage.partner.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">クリア</a>
        @endif
    </form>

    @if($shops->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-12 text-center text-gray-400">
            <p class="mb-2">紹介店舗がまだありません</p>
            <p class="text-xs">紹介URL：<span class="font-mono text-gray-600">{{ url('/register?ref=' . $partner->referral_code) }}/</span></p>
        </div>
    @else
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">店舗名</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">業種</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">エリア</th>
                    <th class="text-center px-4 py-3 text-gray-500 font-medium">掲載状況</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium w-24">入札単価</th>
                    <th class="px-4 py-3 {{ $partner->isManagement() ? 'w-48' : 'w-32' }}"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($shops as $shop)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $shop->name }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $shop->genre?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $shop->area?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($shop->status === 'active')
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">掲載中</span>
                        @elseif($shop->status === 'pending')
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">審査中</span>
                        @else
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">非公開</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-sm text-gray-600">
                        {{ number_format($shop->bid_price) }}円
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <form action="{{ route('manage.partner.actAs', $shop->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="text-xs bg-business-700 hover:bg-business-600 text-white px-3 py-1.5 rounded transition">
                                    管理画面を開く
                                </button>
                            </form>
                            @if($partner->isManagement())
                            <form action="{{ route('manage.partner.shops.destroy', $shop->id) }}" method="POST"
                                  onsubmit="return confirm('「{{ $shop->name }}」を完全に削除します。\nオーナーアカウントも削除されます。この操作は取り消せません。')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-xs text-red-500 hover:text-red-700 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded transition">
                                    削除
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($partner->isManagement() && $rankings->isNotEmpty())
    {{-- 推定掲載順位 --}}
    <div class="mt-8">
        <div class="flex items-baseline justify-between mb-3">
            <h3 class="text-base font-bold text-gray-700">推定掲載順位</h3>
            <p class="text-xs text-gray-400">入札単価・予算残高をもとにリアルタイム算出（参考値）</p>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm bg-white rounded-xl shadow-sm overflow-hidden">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium whitespace-nowrap">店舗名</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium whitespace-nowrap">業種 / エリア</th>
                    <th class="text-center px-4 py-3 text-gray-500 font-medium whitespace-nowrap">都道府県<br><span class="font-normal text-xs">全体</span></th>
                    <th class="text-center px-4 py-3 text-gray-500 font-medium whitespace-nowrap">小エリア<br><span class="font-normal text-xs">全体</span></th>
                    <th class="text-center px-4 py-3 text-gray-500 font-medium whitespace-nowrap">小エリア<br><span class="font-normal text-xs">業種別</span></th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium whitespace-nowrap">職種別順位<br><span class="font-normal text-xs">エリア / 都道府県</span></th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium whitespace-nowrap">参考：同業種エリア上位<br><span class="font-normal text-xs">（有効入札単価）</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($shops->where('status', 'active') as $shop)
                @php $r = $rankings[$shop->id] ?? null; @endphp
                @if($r)
                @php
                    $rankClass = function(int $rank): string {
                        if ($rank <= 3)  return 'bg-green-100 text-green-700';
                        if ($rank <= 10) return 'text-yellow-700 bg-yellow-50';
                        return 'text-gray-500 bg-gray-100';
                    };
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $shop->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">有効スコア：{{ number_format($r['score']) }}</p>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        <p>{{ $shop->genre?->name ?? '—' }}</p>
                        <p class="text-gray-400">{{ $shop->area?->name ?? '—' }}（{{ $shop->area?->prefecture?->name ?? '—' }}）</p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold {{ $rankClass($r['pref_rank']) }}">
                            {{ $r['pref_rank'] }}
                        </span>
                        <p class="text-xs text-gray-400 mt-0.5">/ {{ $r['pref_total'] }}店舗</p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold {{ $rankClass($r['area_rank']) }}">
                            {{ $r['area_rank'] }}
                        </span>
                        <p class="text-xs text-gray-400 mt-0.5">/ {{ $r['area_total'] }}店舗</p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold {{ $rankClass($r['genre_area_rank']) }}">
                            {{ $r['genre_area_rank'] }}
                        </span>
                        <p class="text-xs text-gray-400 mt-0.5">/ {{ $r['genre_area_total'] }}店舗</p>
                    </td>
                    <td class="px-4 py-3">
                        @forelse($r['job_type_ranks'] as $jtRank)
                        <div class="flex items-center gap-1.5 mb-1 last:mb-0">
                            <span class="text-xs text-gray-500 w-20 truncate">{{ $jtRank['name'] }}</span>
                            <span class="inline-block px-1.5 py-0 rounded text-xs font-bold {{ $rankClass($jtRank['area_rank']) }}">
                                {{ $jtRank['area_rank'] }}
                            </span>
                            <span class="text-xs text-gray-300">/</span>
                            <span class="inline-block px-1.5 py-0 rounded text-xs font-bold {{ $rankClass($jtRank['pref_rank']) }}">
                                {{ $jtRank['pref_rank'] }}
                            </span>
                            <span class="text-xs text-gray-400">/ {{ $jtRank['area_total'] }}・{{ $jtRank['pref_total'] }}</span>
                        </div>
                        @empty
                        <span class="text-xs text-gray-400">求人なし</span>
                        @endforelse
                    </td>
                    <td class="px-4 py-3">
                        @forelse($r['top_scores'] as $i => $topScore)
                        <span class="inline-block text-xs mr-2">
                            <span class="text-gray-400">{{ $i + 1 }}位</span>
                            <span class="font-bold {{ $i === 0 ? 'text-gray-700' : 'text-gray-500' }}">
                                ¥{{ number_format($topScore) }}
                            </span>
                        </span>
                        @empty
                        <span class="text-xs text-gray-400">データなし</span>
                        @endforelse
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
        </div>
        <p class="text-xs text-gray-400 mt-2">※ 掲載順位は店舗スコアをもとに算出した参考値です。予算が不足すると順位が大幅に下落します。職種検索での実際の表示位置は競合店の求人掲載数によって前後する場合があります。</p>
    </div>
    @endif

    <div class="mt-4 p-4 bg-gray-50 rounded-lg text-xs text-gray-500">
        <p class="font-medium text-gray-600 mb-1">紹介URL</p>
        <p class="font-mono">{{ url('/register?ref=' . $partner->referral_code) }}/</p>
        <p class="mt-1">このURLから登録した店舗は自動的にあなたの紹介店舗として記録されます</p>
    </div>
    @endif
</div>
@endsection
