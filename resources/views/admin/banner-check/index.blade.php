@extends('layouts.admin')
@section('title', 'バナーリンク確認')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">バナーリンク確認</h1>
            <p class="text-xs text-gray-400 mt-0.5">チェックドメイン: <code class="bg-gray-100 px-1 rounded">{{ $domain }}</code></p>
        </div>
        <div class="text-xs text-gray-400">バナープラン {{ $totalBanner }}件 / 未確認 {{ $unchecked }}件</div>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="flex flex-wrap gap-1 mb-6 border-b border-gray-200">
        @php
        $tabs = [
            'ng'        => ['label' => '設置NG',             'count' => $ngCount,     'color' => '#dc2626'],
            'broken'    => ['label' => '画像切れ',           'count' => $brokenCount, 'color' => '#d97706'],
            'manual'    => ['label' => '手動確認済み',       'count' => $manualCount, 'color' => '#7c3aed'],
            'unapplied' => ['label' => '未適用（リンクあり）','count' => $unapplied,   'color' => '#ea580c'],
            'ok'        => ['label' => '設置済',             'count' => $okCount,     'color' => '#16a34a'],
        ];
        @endphp
        @foreach($tabs as $key => $t)
        <a href="?tab={{ $key }}"
           style="{{ $tab === $key ? 'border-bottom:2px solid '.$t['color'].';color:'.$t['color'].';font-weight:700;' : 'color:#6b7280;' }} padding:8px 16px;font-size:13px;text-decoration:none;white-space:nowrap;margin-bottom:-1px;display:inline-block;">
            {{ $t['label'] }} <span style="font-size:11px;">({{ $t['count'] }})</span>
        </a>
        @endforeach
    </div>

    @if($tab === 'ng')
    <div class="mb-4 bg-gray-50 border border-gray-200 text-gray-500 text-xs px-4 py-3 rounded-lg">
        自動検出できない場合（FC2の年齢認証・Cloudflare等）は「手動OK」で確認済みにできます。次回チェック時もスキップされます。
    </div>
    @endif
    @if($tab === 'broken')
    <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-700 text-xs px-4 py-3 rounded-lg">
        deliconへのリンクはありますが、旧バナー画像URL（<code>delicon.jp/img/dcbn_...</code>）を使用しています。現在は画像を復旧済みのため表示は回復しています。
    </div>
    @endif
    @if($tab === 'manual')
    <div class="mb-4 bg-purple-50 border border-purple-200 text-purple-700 text-xs px-4 py-3 rounded-lg">
        手動で確認済みにした店舗です。自動チェックではスキップされます。
    </div>
    @endif
    @if($tab === 'unapplied')
    <div class="mb-4 bg-orange-50 border border-orange-200 text-orange-700 text-xs px-4 py-3 rounded-lg">
        <strong>両方あり（橙）</strong>：deliconとup-stage両バナー確認済み → プラン3（バナープラン）に適用できます。<br>
        <strong>deliconのみ（青）</strong>：プラン5でdeliconのみ確認済み → プラン4（無料上位）に適用できます。
    </div>
    @endif

    <table class="w-full text-sm bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">店舗名</th>
                <th class="px-4 py-3 text-left">公式URL</th>
                <th class="px-4 py-3 text-left">確認日時</th>
                <th class="px-4 py-3 text-left">操作</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
        @forelse($shops as $shop)
        @php $urlRow = $shop->externalUrls->first(); @endphp
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">
                <a href="{{ route('admin.shops.show', $shop->id) }}" class="font-medium text-gray-900 hover:text-blue-600 hover:underline">{{ $shop->name }}</a>
                <div class="text-xs text-gray-400">{{ $shop->prefecture?->prefecture ?? '—' }}{{ $shop->area ? ' › ' . $shop->area->name : '' }}</div>
                @if($tab === 'unapplied')
                    @if(in_array($shop->banner_ok, [1, 2, 3]))
                    <span style="background:#ea580c;color:#fff;padding:1px 6px;border-radius:4px;font-size:10px;">両方あり → プラン3</span>
                    @elseif($shop->banner_ok === 4)
                    <span style="background:#2563eb;color:#fff;padding:1px 6px;border-radius:4px;font-size:10px;">deliconのみ → プラン4</span>
                    @endif
                @else
                    <span style="background:#e5e7eb;color:#374151;padding:1px 6px;border-radius:4px;font-size:10px;">プラン{{ $shop->plan }}</span>
                @endif
            </td>
            <td class="px-4 py-3">
                @if($urlRow)
                    <a href="{{ $urlRow->url }}" target="_blank" class="text-blue-500 hover:underline text-xs break-all">{{ $urlRow->url }}</a>
                @else
                    <span class="text-gray-300 text-xs">—</span>
                @endif
            </td>
            <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">
                {{ $shop->banner_checked_at?->format('m/d H:i') ?? '—' }}
            </td>
            <td class="px-4 py-3">
                <div class="flex gap-2 flex-wrap">
                    @if($tab === 'ng')
                    <form method="POST" action="{{ route('admin.banner-check.manual-ok', $shop) }}">
                        @csrf @method('PATCH')
                        <button style="background:#7c3aed;color:#fff;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;">手動OK</button>
                    </form>
                    @endif
                    @if($tab === 'manual')
                    <form method="POST" action="{{ route('admin.banner-check.manual-ok', $shop) }}" onsubmit="return confirm('手動確認を解除して再チェック対象に戻しますか？')">
                        @csrf @method('PATCH')
                        <button style="background:#6b7280;color:#fff;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;">解除</button>
                    </form>
                    @endif
                    @if($tab === 'unapplied')
                    <form method="POST" action="{{ route('admin.banner-check.apply', $shop) }}">
                        @csrf @method('PATCH')
                        @if(in_array($shop->banner_ok, [1, 2, 3]))
                        <button style="background:#ea580c;color:#fff;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;">プラン3適用</button>
                        @elseif($shop->banner_ok === 4)
                        <button style="background:#2563eb;color:#fff;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;">プラン4適用</button>
                        @endif
                    </form>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center py-8 text-gray-400">該当なし</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $shops->withQueryString()->links() }}</div>
</div>
@endsection
