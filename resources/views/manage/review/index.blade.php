@extends('layouts.admin')
@section('title', '口コミ管理 - 投稿者一覧')
@section('content')
@include('manage._nav')
<div class="max-w-4xl mx-auto px-4 pb-12">
    <h1 class="text-xl font-bold text-gray-800 mb-6">口コミ管理 — 投稿者一覧</h1>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded p-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    @if($reviewers->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400">
        <p class="text-4xl mb-3">📝</p>
        <p>まだ口コミ投稿はありません。</p>
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">ユーザー名</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">メールアドレス</th>
                    <th class="text-center px-4 py-3 text-gray-600 font-medium">投稿数</th>
                    <th class="text-center px-4 py-3 text-gray-600 font-medium">平均評価</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">最終投稿</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($reviewers as $r)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $r->user->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $r->user->email }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="bg-blue-100 text-blue-700 font-bold text-xs px-2 py-0.5 rounded-full">{{ $r->review_count }}件</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-amber-500 font-bold">★ {{ number_format($r->avg_rating, 1) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ \Carbon\Carbon::parse($r->last_reviewed_at)->format('Y/m/d') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('manage.review.user', $r->user->id) }}/"
                           class="inline-block text-xs bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded font-medium transition">
                            口コミを見る / クーポン送付
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
