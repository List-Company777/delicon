@extends('layouts.admin')

@section('title', 'ダッシュボード')

@section('content')

<h1 class="text-xl font-bold text-gray-700 mb-6">ダッシュボード</h1>

{{-- 要対応バッジ --}}
@if($pendingShops > 0 || $pendingPlanApplications > 0)
<div class="flex flex-wrap gap-3 mb-6">
    @if($pendingShops > 0)
    <a href="{{ route('admin.shops.index', ['status' => 'pending']) }}/"
       class="inline-flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-sm font-medium px-4 py-2.5 rounded-xl hover:bg-red-100 transition">
        <span class="bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $pendingShops }}</span>
        未審査の店舗申請
    </a>
    @endif
    @if($pendingPlanApplications > 0)
    <a href="{{ route('admin.plan-applications.index') }}/"
       class="inline-flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-sm font-medium px-4 py-2.5 rounded-xl hover:bg-red-100 transition">
        <span class="bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $pendingPlanApplications }}</span>
        未確認の有料申込み
    </a>
    @endif
</div>
@endif

{{-- KPIカード --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <a href="{{ route('admin.shops.index') }}/"
       class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition border-l-4 border-indigo-400 block">
        <p class="text-xs text-gray-500 mb-1">登録店舗数</p>
        <p class="text-3xl font-bold text-indigo-600">{{ number_format($kpi['shops']) }}</p>
        <p class="text-xs text-gray-400 mt-1">全ステータス合計</p>
    </a>
    <a href="{{ route('admin.plan-applications.index') }}/"
       class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition border-l-4 border-emerald-400 block">
        <p class="text-xs text-gray-500 mb-1">有料契約（承認済み）</p>
        <p class="text-3xl font-bold text-emerald-600">{{ number_format($kpi['paid']) }}</p>
        <p class="text-xs text-gray-400 mt-1">代理店 {{ $partnerCount }} 社</p>
    </a>
    <a href="{{ route('admin.articles.index') }}/"
       class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition border-l-4 border-sky-400 block">
        <p class="text-xs text-gray-500 mb-1">公開記事</p>
        <p class="text-3xl font-bold text-sky-600">{{ number_format($kpi['articles']) }}</p>
        <p class="text-xs text-gray-400 mt-1">下書き {{ number_format($articleStats['draft']) }} 件</p>
    </a>
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-gray-300">
        <p class="text-xs text-gray-500 mb-1">今月の検索PV</p>
        <p class="text-3xl font-bold text-gray-400">{{ number_format($kpi['pv_this_month']) }}</p>
        <p class="text-xs text-gray-400 mt-1">内部計測（ボット含む）</p>
    </div>
</div>

{{-- 記事・コンテンツ進捗 --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-sm font-bold text-gray-600 mb-4">記事コンテンツ</h2>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">公開済み</span>
                <span class="font-bold text-sky-600">{{ $articleStats['published'] }} 件</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">下書き</span>
                <span class="font-bold text-gray-500">{{ $articleStats['draft'] }} 件</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">承認済みトピック（生成待ち）</span>
                <span class="font-bold text-amber-500">{{ $articleStats['topics'] }} 件</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">動画生成済み</span>
                <span class="font-bold text-emerald-600">{{ $articleStats['video'] }} 件</span>
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <a href="{{ route('admin.articles.index') }}/"
               class="text-xs text-indigo-600 hover:underline">記事一覧 →</a>
            <a href="{{ route('admin.articles.index') }}#topics"
               class="text-xs text-indigo-600 hover:underline">トピック管理 →</a>
        </div>
    </div>

    {{-- 最近の店舗登録 --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-sm font-bold text-gray-600 mb-4">最近の店舗登録</h2>
        @if($recentShops->isEmpty())
            <p class="text-sm text-gray-400">登録なし</p>
        @else
        <div class="space-y-2">
            @foreach($recentShops as $shop)
            <div class="flex items-center justify-between text-sm">
                <a href="{{ route('admin.shops.show', $shop) }}/" class="text-gray-700 hover:text-indigo-600 truncate max-w-[180px]">
                    {{ $shop->name }}
                </a>
                <div class="flex items-center gap-2 shrink-0">
                    @if($shop->status === 'pending')
                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">審査中</span>
                    @elseif($shop->status === 'active')
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">公開</span>
                    @else
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">{{ $shop->status }}</span>
                    @endif
                    <span class="text-xs text-gray-400">{{ $shop->created_at->format('m/d') }}</span>
                </div>
            </div>
            @endforeach
        </div>
        <a href="{{ route('admin.shops.index') }}/" class="block mt-4 text-xs text-indigo-600 hover:underline">全店舗一覧 →</a>
        @endif
    </div>

</div>

{{-- 最近の有料申込み --}}
@if($recentApplications->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <h2 class="text-sm font-bold text-gray-600 mb-4">最近の有料申込み</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-500 border-b">
                    <th class="text-left pb-2 font-medium">店舗名</th>
                    <th class="text-left pb-2 font-medium">プラン</th>
                    <th class="text-left pb-2 font-medium">ステータス</th>
                    <th class="text-left pb-2 font-medium">申込日</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($recentApplications as $app)
                <tr>
                    <td class="py-2 text-gray-700">{{ $app->shop?->name ?? '（削除済み）' }}</td>
                    <td class="py-2 text-gray-600">{{ $app->plan_type ?? '-' }}</td>
                    <td class="py-2">
                        @if($app->status === 'pending')
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">未確認</span>
                        @elseif($app->status === 'approved')
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">承認済み</span>
                        @else
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">{{ $app->status }}</span>
                        @endif
                    </td>
                    <td class="py-2 text-gray-400">{{ $app->created_at->format('m/d') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <a href="{{ route('admin.plan-applications.index') }}/" class="block mt-3 text-xs text-indigo-600 hover:underline">全申込み一覧 →</a>
</div>
@endif

{{-- キーワード正規化サマリー --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <a href="{{ route('admin.keywords.index', ['status' => 'new']) }}/"
       class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition border-l-4 border-yellow-400 block">
        <p class="text-xs text-gray-500 mb-1">未判定キーワード</p>
        <p class="text-3xl font-bold text-yellow-600">{{ number_format($stats['new']) }}</p>
        <p class="text-xs text-gray-400 mt-1">正規化が必要なワード →</p>
    </a>
    <a href="{{ route('admin.keywords.index', ['status' => 'mapped']) }}/"
       class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition border-l-4 border-green-400 block">
        <p class="text-xs text-gray-500 mb-1">正規化済み</p>
        <p class="text-3xl font-bold text-green-600">{{ number_format($stats['mapped']) }}</p>
        <p class="text-xs text-gray-400 mt-1">ディレクトリURLに紐付け済み →</p>
    </a>
    <a href="{{ route('admin.keywords.index', ['status' => 'excluded']) }}/"
       class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition border-l-4 border-gray-300 block">
        <p class="text-xs text-gray-500 mb-1">除外済み</p>
        <p class="text-3xl font-bold text-gray-500">{{ number_format($stats['excluded']) }}</p>
        <p class="text-xs text-gray-400 mt-1">SEO対象外のワード →</p>
    </a>
</div>

{{-- XML未解決求人 --}}
@if($unresolvedXmlJobs->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-sm font-bold text-gray-600 mb-1">XML未解決求人 <span class="text-orange-500">{{ $unresolvedXmlJobs->sum('count') }}件</span></h2>
    <p class="text-xs text-gray-400 mb-4">職種マッピング不可のためボーイに仮割り当て済み。マッピング追加 or 職種新規作成で解消できます。</p>
    <table class="w-full text-sm">
        <thead>
            <tr class="text-xs text-gray-400 border-b border-gray-100">
                <th class="text-left pb-2 font-medium">タイトル（職種部分）</th>
                <th class="text-right pb-2 font-medium w-16">件数</th>
                <th class="text-left pb-2 font-medium pl-4">代表店舗</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($unresolvedXmlJobs as $rawTitle => $info)
            <tr>
                <td class="py-2 font-medium text-gray-800">{{ $rawTitle }}</td>
                <td class="py-2 text-right text-orange-600 font-bold">{{ $info['count'] }}</td>
                <td class="py-2 pl-4 text-gray-500 text-xs">{{ $info['shop_names'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- クイックリンク --}}
<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-sm font-bold text-gray-600 mb-4">クイックアクション</h2>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.keywords.index', ['status' => 'new']) }}/"
           class="px-4 py-2 bg-yellow-500 hover:bg-yellow-400 text-white text-sm rounded-lg transition font-medium">
            未判定キーワードを処理する
        </a>
        <a href="{{ route('admin.articles.index') }}/"
           class="px-4 py-2 bg-sky-500 hover:bg-sky-400 text-white text-sm rounded-lg transition font-medium">
            記事を管理する
        </a>
        <a href="{{ route('admin.articles.index') }}#topics"
           class="px-4 py-2 bg-indigo-500 hover:bg-indigo-400 text-white text-sm rounded-lg transition font-medium">
            記事トピックを管理する
        </a>
    </div>
</div>

@endsection
