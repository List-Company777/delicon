@extends('layouts.admin')
@section('title', 'XML外部連携管理')
@section('content')
<div class="bg-gray-800 text-white py-4">
    <div class="max-w-6xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold">Admin › XML外部連携</h1>
        <a href="{{ route('admin.xml-feeds.create') }}"
           class="bg-white text-gray-800 text-sm font-bold px-4 py-1.5 rounded hover:bg-gray-100">＋ 連携先を追加</a>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-8">
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-blue-50 border border-blue-200 text-blue-800 text-xs px-4 py-3 rounded-lg mb-6 leading-relaxed">
        <strong>連携タイプの違い:</strong><br>
        ・<strong>自社サイト</strong>（is_own_site=ON）: 店舗が登録画面から引き継ぎ可。キャスト求人・営業情報の追加案内あり。入札単価のXML連動も可能。<br>
        ・<strong>他社サイト</strong>（is_own_site=OFF）: 読み取り専用。求人は元サイトへhotlink。クレーム・追加案内なし。
    </div>

    @if($feeds->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
            <p>連携先が登録されていません</p>
        </div>
    @else
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">サイト名 / スラッグ</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">タイプ</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">自社</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">カテゴリ絞込</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">最終同期</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">状態</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($feeds as $feed)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $feed->name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $feed->slug }}</p>
                        @if($feed->url)
                            <p class="text-xs text-gray-400 truncate max-w-xs" title="{{ $feed->url }}">{{ $feed->url }}</p>
                        @else
                            <p class="text-xs text-amber-500">URL未設定（env値を使用）</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $feed->feedTypeLabel() }}</td>
                    <td class="px-4 py-3">
                        @if($feed->is_own_site)
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">自社</span>
                        @else
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">他社</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        @if($feed->allowed_categories)
                            {{ count($feed->allowed_categories) }}カテゴリ
                        @else
                            <span class="text-gray-300">全件</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        {{ $feed->last_imported_at?->format('m/d H:i') ?? '未実行' }}
                    </td>
                    <td class="px-4 py-3">
                        <form action="{{ route('admin.xml-feeds.toggle', $feed) }}" method="POST">
                            @csrf
                            <button type="submit" @class([
                                'text-xs px-2 py-0.5 rounded-full border transition',
                                'bg-green-100 text-green-700 border-green-200 hover:bg-green-200' => $feed->status === 'active',
                                'bg-gray-100 text-gray-400 border-gray-200 hover:bg-gray-200'     => $feed->status !== 'active',
                            ])>
                                {{ $feed->status === 'active' ? 'アクティブ' : '停止中' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.xml-feeds.edit', $feed) }}"
                           class="text-xs text-blue-600 hover:underline">編集</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-xs text-gray-500">
        <p class="font-medium text-gray-700 mb-1">手動実行コマンド</p>
        <code class="block font-mono">php artisan import:xml-feed               # 全フィード</code>
        <code class="block font-mono">php artisan import:xml-feed upstage       # 特定スラッグのみ</code>
        <code class="block font-mono">php artisan import:xml-feed --dry-run     # テスト実行</code>
    </div>
</div>
@endsection
