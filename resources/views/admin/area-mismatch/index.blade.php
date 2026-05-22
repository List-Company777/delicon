@extends('layouts.admin')
@section('title', 'エリア名不一致レビュー')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-800">エリア名不一致レビュー</h1>
        <span class="text-sm text-gray-500">
            店名: <span class="font-bold text-gray-800">{{ $areaMismatches->count() }}</span> 件 /
            住所: <span class="font-bold text-gray-800">{{ $prefMismatches->count() }}</span> 件
        </span>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- ① 店名ベース --}}
    <h2 class="text-sm font-bold text-gray-600 uppercase tracking-wide mb-3">
        店名にエリア名が含まれているが設定が異なる
    </h2>
    <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-700 text-xs px-4 py-3 rounded-lg">
        「適用」でエリアを提案値に変更、「無視」でこの店舗を対象外にします。
    </div>

    @forelse($areaMismatches as $item)
    @php $shop = $item['shop']; $suggested = $item['suggested_area']; @endphp
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-3">
        <div class="text-sm font-bold text-gray-900 mb-1">
            {!! preg_replace('/(' . preg_quote(e($suggested->name), '/') . ')/', '<span class="bg-amber-200 text-amber-800 px-0.5 rounded">$1</span>', e($shop->name)) !!}
            <a href="{{ route('admin.shops.show', $shop->id) }}" target="_blank" class="ml-2 text-xs text-gray-400 hover:text-gray-600">↗</a>
        </div>
        <div class="flex flex-wrap items-center gap-2 mt-1 mb-3">
            <span class="text-gray-400 text-xs">現在:</span>
            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs">{{ $shop->prefecture?->prefecture ?? '—' }}{{ $shop->area ? ' › ' . $shop->area->name : '' }}</span>
            <span class="text-gray-400 text-xs">→</span>
            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-medium">{{ $suggested->prefecture?->prefecture ?? '—' }} › {{ $suggested->name }}</span>
        </div>
        @if($shop->address)<div class="mb-3 text-xs text-gray-400">住所: {{ $shop->address }}</div>@endif
        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.area-mismatch.apply', $shop) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="area_id" value="{{ $suggested->id }}">
                <button style="background:#d97706;color:#fff;padding:6px 16px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;">適用</button>
            </form>
            <form method="POST" action="{{ route('admin.area-mismatch.dismiss', $shop) }}" onsubmit="return confirm('無視リストに追加しますか？')">
                @csrf @method('PATCH')
                <button style="background:#6b7280;color:#fff;padding:6px 16px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;">無視</button>
            </form>
        </div>
    </div>
    @empty
    <div class="text-center py-8 text-gray-400 text-sm">不一致なし</div>
    @endforelse

    {{-- ② 住所ベース --}}
    <h2 class="text-sm font-bold text-gray-600 uppercase tracking-wide mt-8 mb-3">
        住所の都道府県と設定が異なる
    </h2>
    <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-700 text-xs px-4 py-3 rounded-lg">
        「適用」で都道府県を住所に合わせて変更（エリアはリセット）、「無視」で対象外にします。都県境の店舗は意図的な場合があるため目視確認してください。
    </div>

    @forelse($prefMismatches as $item)
    @php $shop = $item['shop']; $suggested = $item['suggested_pref']; @endphp
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-3">
        <div class="text-sm font-bold text-gray-900 mb-1">
            {{ $shop->name }}
            <a href="{{ route('admin.shops.show', $shop->id) }}" target="_blank" class="ml-2 text-xs text-gray-400 hover:text-gray-600">↗</a>
        </div>
        <div class="flex flex-wrap items-center gap-2 mt-1 mb-3">
            <span class="text-gray-400 text-xs">現在:</span>
            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs">{{ $shop->prefecture?->prefecture ?? '—' }}{{ $shop->area ? ' › ' . $shop->area->name : '' }}</span>
            <span class="text-gray-400 text-xs">→ 住所から:</span>
            <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-xs font-medium">{{ $suggested->prefecture }}</span>
        </div>
        @if($shop->address)<div class="mb-3 text-xs text-gray-400">住所: <span class="text-gray-600">{{ $shop->address }}</span></div>@endif
        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.area-mismatch.apply-pref', $shop) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="prefecture_id" value="{{ $suggested->id }}">
                <button style="background:#2563eb;color:#fff;padding:6px 16px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;">適用</button>
            </form>
            <form method="POST" action="{{ route('admin.area-mismatch.dismiss', $shop) }}" onsubmit="return confirm('無視リストに追加しますか？')">
                @csrf @method('PATCH')
                <button style="background:#6b7280;color:#fff;padding:6px 16px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;">無視</button>
            </form>
        </div>
    </div>
    @empty
    <div class="text-center py-8 text-gray-400 text-sm">不一致なし</div>
    @endforelse
</div>
@endsection
