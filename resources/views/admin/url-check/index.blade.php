@extends('layouts.admin')
@section('title', 'URL死活チェック')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-800">URL死活チェック</h1>
        <div class="flex items-center gap-4 text-sm text-gray-500">
            <span>対象: <b class="text-gray-800">{{ $total }}</b>件</span>
            <span>チェック済: <b class="text-gray-800">{{ $checked }}</b>件</span>
            <span>エラー: <b class="text-red-600">{{ $errors }}</b>件</span>
        </div>
    </div>

    @if($checked === 0)
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 text-sm px-4 py-3 rounded-lg mb-6">
        まだURLチェックが実行されていません。サーバーで以下を実行してください:<br>
        <code class="font-mono bg-yellow-100 px-2 py-0.5 rounded mt-1 inline-block">php artisan shops:check-urls</code>
    </div>
    @endif

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="flex gap-2 mb-6">
        <a href="{{ request()->fullUrlWithQuery(['status_filter' => 'error']) }}"
           style="{{ $statusFilter === 'error' ? 'background:#dc2626;color:#fff;' : 'background:#f3f4f6;color:#374151;' }} padding:6px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
            エラーのみ ({{ $errors }})
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status_filter' => 'all']) }}"
           style="{{ $statusFilter === 'all' ? 'background:#374151;color:#fff;' : 'background:#f3f4f6;color:#374151;' }} padding:6px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
            全件
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status_filter' => 'ok']) }}"
           style="{{ $statusFilter === 'ok' ? 'background:#16a34a;color:#fff;' : 'background:#f3f4f6;color:#374151;' }} padding:6px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
            正常
        </a>
    </div>

    @forelse($urls as $row)
    @php $shop = $row->shop; $s = $row->url_status; @endphp
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-3">
        <div class="flex items-start gap-3 mb-2">
            @if($s === 0)
                <span style="background:#7c3aed;color:#fff;padding:2px 8px;border-radius:6px;font-size:12px;font-weight:700;">TIMEOUT</span>
            @elseif($s >= 500)
                <span style="background:#dc2626;color:#fff;padding:2px 8px;border-radius:6px;font-size:12px;font-weight:700;">{{ $s }}</span>
            @elseif($s >= 400)
                <span style="background:#ea580c;color:#fff;padding:2px 8px;border-radius:6px;font-size:12px;font-weight:700;">{{ $s }}</span>
            @else
                <span style="background:#16a34a;color:#fff;padding:2px 8px;border-radius:6px;font-size:12px;font-weight:700;">{{ $s }}</span>
            @endif
            <div class="flex-1 min-w-0">
                <div class="text-sm font-bold text-gray-900">
                    {{ $shop->name }}
                    <a href="{{ route('admin.shops.show', $shop->id) }}" target="_blank" class="ml-2 text-xs text-gray-400 hover:text-gray-600">↗</a>
                </div>
                <div class="text-xs text-gray-400 mt-0.5">{{ $shop->prefecture?->prefecture ?? '—' }}{{ $shop->area ? ' › ' . $shop->area->name : '' }}</div>
                <a href="{{ $row->url }}" target="_blank" class="text-xs text-blue-500 hover:underline break-all mt-1 block">{{ $row->url }}</a>
                <div class="text-xs text-gray-300 mt-0.5">確認: {{ $row->url_checked_at?->format('Y/m/d H:i') }}</div>
            </div>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.url-check.dismiss', $row) }}">
                @csrf @method('PATCH')
                <button style="background:#6b7280;color:#fff;padding:5px 14px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;">無視</button>
            </form>
            <form method="POST" action="{{ route('admin.url-check.deactivate', $row) }}" onsubmit="return confirm('この店舗を非公開にしますか？')">
                @csrf @method('PATCH')
                <button style="background:#dc2626;color:#fff;padding:5px 14px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;">非公開にする</button>
            </form>
        </div>
    </div>
    @empty
    <div class="text-center py-8 text-gray-400 text-sm">該当なし</div>
    @endforelse

    <div class="mt-4">{{ $urls->withQueryString()->links() }}</div>
</div>
@endsection
