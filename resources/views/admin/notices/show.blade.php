@extends('layouts.admin')
@section('title', $notice->title)
@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-700">お知らせ詳細</h1>
    <a href="{{ route('admin.notices.index') }}/" class="text-sm text-gray-400 hover:text-gray-600">← 一覧に戻る</a>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">

    {{-- ステータスバー --}}
    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
            @if($notice->isSent())
                <span class="text-xs px-3 py-1 bg-green-100 text-green-700 rounded-full font-medium">送信済み</span>
                <span class="text-xs text-gray-400">{{ $notice->sent_at->format('Y/m/d H:i') }} に {{ number_format($notice->sent_count) }}件送信</span>
            @else
                <span class="text-xs px-3 py-1 bg-gray-100 text-gray-500 rounded-full font-medium">下書き</span>
                <span class="text-xs text-gray-400">送信対象：{{ $notice->targetLabel() }}（{{ number_format($targetCount) }}人）</span>
            @endif
        </div>
        @if(! $notice->isSent())
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.notices.edit', $notice) }}/"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold px-4 py-2 rounded-lg transition">
                    編集する
                </a>
                <form action="{{ route('admin.notices.send', $notice) }}/" method="POST"
                      onsubmit="return confirm('{{ $notice->targetLabel() }}（{{ number_format($targetCount) }}人）にメールを送信します。よろしいですか？')">
                    @csrf
                    <button type="submit"
                            class="bg-yellow-500 hover:bg-yellow-400 text-white text-sm font-bold px-5 py-2 rounded-lg transition">
                        {{ number_format($targetCount) }}人に送信する
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- 内容プレビュー --}}
    <div class="space-y-4 text-sm">
        <div class="flex gap-4">
            <span class="text-gray-400 w-24 shrink-0">件名</span>
            <span class="font-medium text-gray-800">【デリヘルリスト】{{ $notice->title }}</span>
        </div>
        <div class="flex gap-4">
            <span class="text-gray-400 w-24 shrink-0">送信対象</span>
            <span class="text-gray-700">{{ $notice->targetLabel() }}</span>
        </div>
        <div class="flex gap-4">
            <span class="text-gray-400 w-24 shrink-0 pt-0.5">本文</span>
            <div class="bg-gray-50 rounded-lg p-4 flex-1 text-gray-700 whitespace-pre-line leading-relaxed text-xs font-mono">{{ $notice->body }}</div>
        </div>
    </div>
</div>

@endsection
