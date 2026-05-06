@extends('layouts.admin')
@section('title', 'お知らせ配信')
@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-700">お知らせ配信</h1>
    <a href="{{ route('admin.notices.create') }}/"
       class="bg-yellow-500 hover:bg-yellow-400 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
        + 新規作成
    </a>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
        {{ session('success') }}
    </div>
@endif

@if($notices->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center text-gray-400">
        <p class="text-sm">お知らせはまだありません</p>
    </div>
@else
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">タイトル</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-32">送信対象</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-20">状態</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-24">送信数</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-36">日時</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($notices as $notice)
                <tr class="hover:bg-gray-50 transition cursor-pointer"
                    onclick="location.href='{{ route('admin.notices.show', $notice) }}'">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $notice->title }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $notice->targetLabel() }}</td>
                    <td class="px-4 py-3">
                        @if($notice->isSent())
                            <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full">送信済み</span>
                        @else
                            <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">下書き</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        {{ $notice->isSent() ? number_format($notice->sent_count) . '件' : '—' }}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400">
                        {{ $notice->sent_at?->format('Y/m/d H:i') ?? $notice->created_at->format('Y/m/d H:i') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $notices->links() }}</div>
@endif

@endsection
