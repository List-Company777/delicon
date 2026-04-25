@extends('layouts.admin')

@section('title', 'ダッシュボード')

@section('content')

<h1 class="text-xl font-bold text-gray-700 mb-6">ダッシュボード</h1>

{{-- 要対応バッジ --}}
@if($pendingShops > 0 || $pendingPlanApplications > 0)
<div class="flex flex-wrap gap-3 mb-6">
    @if($pendingShops > 0)
    <a href="{{ route('admin.shops.index', ['status' => 'pending']) }}"
       class="inline-flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-sm font-medium px-4 py-2.5 rounded-xl hover:bg-red-100 transition">
        <span class="bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $pendingShops }}</span>
        未審査の店舗申請
    </a>
    @endif
    @if($pendingPlanApplications > 0)
    <a href="{{ route('admin.plan-applications.index') }}"
       class="inline-flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-sm font-medium px-4 py-2.5 rounded-xl hover:bg-red-100 transition">
        <span class="bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $pendingPlanApplications }}</span>
        未確認の有料申込み
    </a>
    @endif
</div>
@endif

{{-- キーワード正規化サマリー --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <a href="{{ route('admin.keywords.index', ['status' => 'new']) }}"
       class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition border-l-4 border-yellow-400 block">
        <p class="text-xs text-gray-500 mb-1">未判定キーワード</p>
        <p class="text-3xl font-bold text-yellow-600">{{ number_format($stats['new']) }}</p>
        <p class="text-xs text-gray-400 mt-1">正規化が必要なワード →</p>
    </a>
    <a href="{{ route('admin.keywords.index', ['status' => 'mapped']) }}"
       class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition border-l-4 border-green-400 block">
        <p class="text-xs text-gray-500 mb-1">正規化済み</p>
        <p class="text-3xl font-bold text-green-600">{{ number_format($stats['mapped']) }}</p>
        <p class="text-xs text-gray-400 mt-1">ディレクトリURLに紐付け済み →</p>
    </a>
    <a href="{{ route('admin.keywords.index', ['status' => 'excluded']) }}"
       class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition border-l-4 border-gray-300 block">
        <p class="text-xs text-gray-500 mb-1">除外済み</p>
        <p class="text-3xl font-bold text-gray-500">{{ number_format($stats['excluded']) }}</p>
        <p class="text-xs text-gray-400 mt-1">SEO対象外のワード →</p>
    </a>
</div>

{{-- クイックリンク --}}
<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-sm font-bold text-gray-600 mb-4">クイックアクション</h2>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.keywords.index', ['status' => 'new']) }}"
           class="px-4 py-2 bg-yellow-500 hover:bg-yellow-400 text-white text-sm rounded-lg transition font-medium">
            未判定キーワードを処理する
        </a>
    </div>
</div>

@endsection
