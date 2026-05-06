@extends('layouts.app')
@section('title', '通知設定')
@section('robots', 'noindex')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-[#F0ECE4] mb-6 flex items-center gap-3">
        <span class="w-1 h-7 bg-deli-500 rounded-full inline-block"></span>
        通知設定
    </h1>

    <div class="flex gap-4 mb-8 text-sm">
        <a href="{{ route('user.dashboard') }}/" class="text-[#6A6A7E] hover:text-[#C8C4BC] transition">お気に入り / 閲覧履歴</a>
        <a href="{{ route('user.settings') }}/" class="text-deli-400 border-b border-deli-500 pb-1">通知設定</a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-emerald-900/40 border border-emerald-600/40 text-emerald-400 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('user.settings.update') }}/">
        @csrf
        <div class="bg-surface-500 border border-surface-300 rounded-xl p-6 space-y-5">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="notify_working" value="1"
                       @checked($user->notify_working)
                       class="mt-1 rounded border-surface-300 bg-surface-600 text-deli-500 focus:ring-deli-500">
                <div>
                    <p class="text-sm font-medium text-[#E8E4DC]">お気に入りキャストの出勤通知</p>
                    <p class="text-xs text-[#6A6A7E] mt-0.5">お気に入り登録したキャストが出勤予定になったときにメールでお知らせします</p>
                </div>
            </label>
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="notify_new_cast" value="1"
                       @checked($user->notify_new_cast)
                       class="mt-1 rounded border-surface-300 bg-surface-600 text-deli-500 focus:ring-deli-500">
                <div>
                    <p class="text-sm font-medium text-[#E8E4DC]">新人キャスト通知</p>
                    <p class="text-xs text-[#6A6A7E] mt-0.5">新しいキャストが登録されたときにメールでお知らせします</p>
                </div>
            </label>
        </div>
        <div class="mt-4 text-right">
            <button type="submit" class="bg-deli-500 hover:bg-deli-400 text-white text-sm font-bold px-6 py-2 rounded-lg transition">
                保存する
            </button>
        </div>
    </form>
</div>
@endsection
