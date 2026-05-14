@extends('layouts.app')
@section('title', '日記を投稿')

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
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-800">{{ $cast->name }} - 日記を投稿</h1>
        <a href="{{ route('manage.cast-diary.index', $cast->id) }}/" class="text-xs text-gray-500 hover:text-business-600">← 一覧に戻る</a>
    </div>


    {{-- ユーザーの遊びやすい曜日・時間帯ヒント --}}
    @if(!empty($scheduleStats) && $scheduleStats['total'] > 0)
    @php
        $dayLabels  = ['mon'=>'月','tue'=>'火','wed'=>'水','thu'=>'木','fri'=>'金','sat'=>'土','sun'=>'日'];
        $timeLabels = ['morning'=>'午前','afternoon'=>'昼間','evening'=>'夕方','night'=>'夜','midnight'=>'深夜'];
        $maxDay  = max($scheduleStats['days'])  ?: 1;
        $maxTime = max($scheduleStats['times']) ?: 1;
        $topDays  = collect($scheduleStats['days'])->sortDesc()->take(2)->keys()->map(fn($k) => $dayLabels[$k])->join('・');
        $topTimes = collect($scheduleStats['times'])->sortDesc()->take(2)->keys()->map(fn($k) => $timeLabels[$k])->join('・');
    @endphp
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
        <p class="text-xs font-bold text-blue-700 mb-1">💡 ファンが遊びやすい時間帯</p>
        <p class="text-xs text-blue-600 mb-3">登録ユーザー {{ number_format($scheduleStats['total']) }}名の回答データです。この時間帯の投稿は反応を得やすいかもしれません。</p>
        <div class="flex gap-4">
            <div>
                <p class="text-[10px] text-gray-500 mb-1.5">曜日</p>
                <div class="flex gap-1 items-end">
                    @foreach($scheduleStats['days'] as $day => $cnt)
                    @php $pct = round($cnt / $maxDay * 100); @endphp
                    <div class="flex flex-col items-center gap-0.5">
                        <div class="w-5 rounded-t" style="height:{{ max(3, $pct * 0.28) }}px; background:{{ $pct >= 80 ? '#E05A5A' : ($pct >= 50 ? '#E09050' : '#3A5A7A') }}"></div>
                        <span class="text-[9px] {{ $pct >= 80 ? 'text-blue-700 font-bold' : 'text-gray-500' }}">{{ $dayLabels[$day] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="flex-1">
                <p class="text-[10px] text-gray-500 mb-1.5">時間帯</p>
                <div class="space-y-1">
                    @foreach($scheduleStats['times'] as $time => $cnt)
                    @php $pct = round($cnt / $maxTime * 100); @endphp
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] text-[#6A6A7E] w-10 shrink-0">{{ $timeLabels[$time] }}</span>
                        <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full" style="width:{{ $pct }}%; background:{{ $pct >= 80 ? '#E05A5A' : ($pct >= 50 ? '#E09050' : '#3A5A7A') }}"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <p class="text-[10px] text-gray-500 mt-2">人気: {{ $topDays }} ／ {{ $topTimes }}</p>
    </div>
    @endif

    {{-- ファン個人リスト --}}
    @if(isset($fanList) && $fanList->isNotEmpty())
    @php
        $dlabels = ['mon'=>'月','tue'=>'火','wed'=>'水','thu'=>'木','fri'=>'金','sat'=>'土','sun'=>'日'];
        $tlabels = ['morning'=>'午前','afternoon'=>'昼間','evening'=>'夕方','night'=>'夜','midnight'=>'深夜'];
    @endphp
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-left">
            <p class="text-xs font-bold text-gray-800">お気に入り登録者 {{ $fanList->count() }}名</p>
            <svg x-show="!open" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            <svg x-show="open" class="w-4 h-4 text-business-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
        </button>
        <div x-show="open" x-transition class="mt-3 overflow-x-auto">
            <table class="w-full text-xs text-gray-700">
                <thead>
                    <tr class="border-b border-gray-200 text-gray-500 text-left">
                        <th class="pb-2 pr-3 font-medium">お名前</th>
                        <th class="pb-2 pr-3 font-medium">遊びやすい曜日</th>
                        <th class="pb-2 font-medium">時間帯</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($fanList as $fan)
                    <tr>
                        <td class="py-1.5 pr-3">{{ $fan->user_name }}</td>
                        <td class="py-1.5 pr-3">
                            @if(!empty($fan->preferred_days))
                                {{ collect($fan->preferred_days)->map(fn($d) => $dlabels[$d] ?? $d)->join('・') }}
                            @else<span class="text-gray-300">未設定</span>@endif
                        </td>
                        <td class="py-1.5">
                            @if(!empty($fan->preferred_times))
                                {{ collect($fan->preferred_times)->map(fn($t) => $tlabels[$t] ?? $t)->join('・') }}
                            @else<span class="text-gray-300">未設定</span>@endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-5 text-xs text-amber-700 leading-relaxed">
        <span class="font-bold">⚠ 写メ日記についてのご案内</span>：写メ日記は女性の人柄を知ってもらうための機能です。アダルトな内容については控えていただくよう、在籍女性にもご指導ください。
    </div>
    <form method="POST" action="{{ route('manage.cast-diary.store', $cast->id) }}/" enctype="multipart/form-data">
        @csrf
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-5">

            <div>
                <label class="text-xs text-gray-600 block mb-1">タイトル（任意）</label>
                <input type="text" name="title" maxlength="100" value="{{ old('title') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-800 focus:outline-none focus:border-business-400">
            </div>

            <div>
                <label class="text-xs text-gray-600 block mb-1">本文</label>
                <textarea name="body" rows="6" maxlength="2000"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-800 focus:outline-none focus:border-business-400 resize-y">{{ old('body') }}</textarea>
                @error('body')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-xs text-gray-600 block mb-1">画像（最大8枚）</label>
                <input type="file" name="images[]" multiple accept="image/*"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-business-700 file:text-white hover:file:bg-business-600">
                @error('images.*')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-xs text-gray-600 block mb-1">公開設定</label>
                <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-800 focus:outline-none focus:border-business-400 bg-white">
                    <option value="published" @selected(old('status','published')==='published')>公開</option>
                    <option value="draft" @selected(old('status')==='draft')>下書き</option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex justify-end gap-3">
            <a href="{{ route('manage.cast-diary.index', $cast->id) }}/" class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2">キャンセル</a>
            <button type="submit" class="bg-business-700 hover:bg-business-600 text-white text-sm font-bold px-6 py-2 rounded-lg transition">投稿する</button>
        </div>
    </form>
</div>
@endsection
