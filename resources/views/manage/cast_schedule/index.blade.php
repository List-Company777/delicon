@extends('layouts.app')
@section('title', $cast->name . ' シフト管理')
@section('content')
<div class="bg-business-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold text-lg">店舗管理</h1>
        <div class="flex items-center gap-4 text-sm">
            <span class="opacity-70">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}/" method="POST">
                @csrf
                <button type="submit" class="opacity-70 hover:opacity-100 transition">ログアウト</button>
            </form>
        </div>
    </div>
</div>

@include('manage._nav')

<div class="max-w-4xl mx-auto px-4 pb-12">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('manage.cast-profile.index') }}/" class="text-sm text-gray-400 hover:text-gray-600">← 在籍キャスト一覧</a>
        <span class="text-gray-300">/</span>
        <h2 class="text-lg font-bold text-gray-800">{{ $cast->name }} のシフト管理</h2>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- 登録フォーム --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-sm font-bold text-gray-700 mb-4">シフトを追加</h3>
        <form action="{{ route('manage.cast-schedule.store', $cast->id) }}/" method="POST">
            @csrf
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs text-gray-500 mb-1">日付 <span class="text-red-500">*</span></label>
                    <input type="date" name="work_date" value="{{ old('work_date') }}" required
                           min="{{ today()->toDateString() }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-red-400 @error('work_date') border-red-400 @enderror">
                    @error('work_date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">出勤時間</label>
                    <input type="time" name="start_time" value="{{ old('start_time') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-red-400">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">退勤時間</label>
                    <input type="time" name="end_time" value="{{ old('end_time') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-red-400">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs text-gray-500 mb-1">備考</label>
                    <input type="text" name="note" value="{{ old('note') }}" maxlength="100" placeholder="例：遅出し予定"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-red-400">
                </div>
            </div>
            <button type="submit"
                    class="bg-business-700 hover:bg-business-600 text-white text-sm font-bold px-5 py-2 rounded-lg transition">
                追加する
            </button>
        </form>
    </div>

    {{-- シフト一覧 --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-700">登録済みシフト（本日以降）</h3>
            <span class="text-xs text-gray-400">{{ $schedules->count() }}件</span>
        </div>
        @if($schedules->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">シフトはまだ登録されていません。</p>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($schedules as $schedule)
                <div class="flex items-center gap-4 px-5 py-3">
                    <div class="flex-1">
                        <span class="text-sm font-medium text-gray-800">
                            {{ $schedule->work_date->format('Y/m/d') }}（{{ ['日','月','火','水','木','金','土'][$schedule->work_date->dayOfWeek] }}）
                        </span>
                        @if($schedule->start_time || $schedule->end_time)
                            <span class="text-xs text-gray-500 ml-2">
                                {{ $schedule->start_time ? substr($schedule->start_time, 0, 5) : '?' }}〜{{ $schedule->end_time ? substr($schedule->end_time, 0, 5) : '?' }}
                            </span>
                        @endif
                        @if($schedule->note)
                            <span class="text-xs text-gray-400 ml-2">{{ $schedule->note }}</span>
                        @endif
                    </div>
                    <form action="{{ route('manage.cast-schedule.destroy', [$cast->id, $schedule->id]) }}/" method="POST"
                          onsubmit="return confirm('このシフトを削除しますか？')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600 border border-red-200 hover:border-red-400 px-3 py-1 rounded transition">削除</button>
                    </form>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
