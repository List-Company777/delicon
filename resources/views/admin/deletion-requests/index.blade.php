@extends('layouts.admin')
@section('title', '削除依頼一覧')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">削除依頼一覧</h1>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">受付日時</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">キャスト</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">依頼者</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">メール</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">理由</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">状態</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($requests as $req)
                <tr class="{{ $req->status === 'pending' ? 'bg-red-50 dark:bg-red-900/10' : 'opacity-60' }}">
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $req->created_at->format('m/d H:i') }}</td>
                    <td class="px-4 py-3">
                        @if($req->cast)
                        <a href="{{ route('cast.show', $req->cast_id) }}/" target="_blank" class="text-blue-600 hover:underline">{{ $req->cast->name }}</a>
                        <span class="text-xs text-gray-400 ml-1">#{{ $req->cast_id }}</span>
                        @else
                        <span class="text-gray-400">（削除済み #{{ $req->cast_id }}）</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $req->requester_name }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">{{ $req->requester_email }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs max-w-xs truncate" title="{{ $req->reason }}">{{ $req->reason ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($req->status === 'pending')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">未処理</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">処理済</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($req->status === 'pending')
                        <form method="POST" action="{{ route('admin.deletion-requests.process', $req->id) }}/" onsubmit="return confirm('キャストを非公開にして依頼者に通知メールを送信します。よろしいですか？')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-3 py-1.5 rounded transition">
                                削除して通知
                            </button>
                        </form>
                        @else
                        <span class="text-xs text-gray-400">{{ $req->processed_at?->format('m/d H:i') }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">削除依頼はありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $requests->links() }}
    </div>
</div>
@endsection
