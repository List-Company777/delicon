@extends('layouts.manage')
@section('title', '日記を投稿')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-[#F0ECE4]">{{ $cast->name }} - 日記を投稿</h1>
        <a href="{{ route('cast-diary.index', $cast->id) }}/" class="text-xs text-[#6A6A7E] hover:text-deli-400">← 一覧に戻る</a>
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
    <div class="bg-surface-600 border border-gold-700/40 rounded-xl p-4 mb-6">
        <p class="text-xs font-bold text-gold-400 mb-1">💡 ファンが遊びやすい時間帯</p>
        <p class="text-xs text-[#B0AEAD] mb-3">登録ユーザー {{ number_format($scheduleStats['total']) }}名の回答データです。この時間帯の投稿は反応を得やすいかもしれません。</p>
        <div class="flex gap-4">
            <div>
                <p class="text-[10px] text-[#6A6A7E] mb-1.5">曜日</p>
                <div class="flex gap-1 items-end">
                    @foreach($scheduleStats['days'] as $day => $cnt)
                    @php $pct = round($cnt / $maxDay * 100); @endphp
                    <div class="flex flex-col items-center gap-0.5">
                        <div class="w-5 rounded-t" style="height:{{ max(3, $pct * 0.28) }}px; background:{{ $pct >= 80 ? '#E05A5A' : ($pct >= 50 ? '#E09050' : '#3A5A7A') }}"></div>
                        <span class="text-[9px] {{ $pct >= 80 ? 'text-gold-400 font-bold' : 'text-[#6A6A7E]' }}">{{ $dayLabels[$day] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="flex-1">
                <p class="text-[10px] text-[#6A6A7E] mb-1.5">時間帯</p>
                <div class="space-y-1">
                    @foreach($scheduleStats['times'] as $time => $cnt)
                    @php $pct = round($cnt / $maxTime * 100); @endphp
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] text-[#6A6A7E] w-10 shrink-0">{{ $timeLabels[$time] }}</span>
                        <div class="flex-1 bg-surface-700 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full" style="width:{{ $pct }}%; background:{{ $pct >= 80 ? '#E05A5A' : ($pct >= 50 ? '#E09050' : '#3A5A7A') }}"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <p class="text-[10px] text-[#6A6A7E] mt-2">人気: {{ $topDays }} ／ {{ $topTimes }}</p>
    </div>
    @endif

    {{-- ファン個人リスト --}}
    @if(isset($fanList) && $fanList->isNotEmpty())
    @php
        $dlabels = ['mon'=>'月','tue'=>'火','wed'=>'水','thu'=>'木','fri'=>'金','sat'=>'土','sun'=>'日'];
        $tlabels = ['morning'=>'午前','afternoon'=>'昼間','evening'=>'夕方','night'=>'夜','midnight'=>'深夜'];
    @endphp
    <div class="bg-surface-600 border border-surface-300 rounded-xl p-4 mb-6" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-left">
            <p class="text-xs font-bold text-[#E8E4DC]">お気に入り登録者 {{ $fanList->count() }}名</p>
            <svg x-show="!open" class="w-4 h-4 text-[#6A6A7E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            <svg x-show="open" class="w-4 h-4 text-deli-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
        </button>
        <div x-show="open" x-transition class="mt-3 overflow-x-auto">
            <table class="w-full text-xs text-[#C8C4BC]">
                <thead>
                    <tr class="border-b border-surface-400 text-[#6A6A7E] text-left">
                        <th class="pb-2 pr-3 font-medium">お名前</th>
                        <th class="pb-2 pr-3 font-medium">遊びやすい曜日</th>
                        <th class="pb-2 font-medium">時間帯</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-500">
                    @foreach($fanList as $fan)
                    <tr>
                        <td class="py-1.5 pr-3">{{ $fan->user_name }}</td>
                        <td class="py-1.5 pr-3">
                            @if(!empty($fan->preferred_days))
                                {{ collect($fan->preferred_days)->map(fn($d) => $dlabels[$d] ?? $d)->join('・') }}
                            @else<span class="text-surface-400">未設定</span>@endif
                        </td>
                        <td class="py-1.5">
                            @if(!empty($fan->preferred_times))
                                {{ collect($fan->preferred_times)->map(fn($t) => $tlabels[$t] ?? $t)->join('・') }}
                            @else<span class="text-surface-400">未設定</span>@endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="bg-amber-900/30 border border-amber-700/50 rounded-xl px-4 py-3 mb-5 text-xs text-amber-300 leading-relaxed">
        <span class="font-bold">⚠ 写メ日記についてのご案内</span>：写メ日記は女性の人柄を知ってもらうための機能です。アダルトな内容については控えていただくよう、在籍女性にもご指導ください。
    </div>
    <form method="POST" action="{{ route('cast-diary.store', $cast->id) }}/" enctype="multipart/form-data">
        @csrf
        <div class="bg-surface-500 border border-surface-300 rounded-xl p-6 space-y-5">

            <div>
                <label class="text-xs text-[#C8C4BC] block mb-1">タイトル（任意）</label>
                <input type="text" name="title" maxlength="100" value="{{ old('title') }}"
                       class="w-full bg-surface-600 border border-surface-300 rounded-lg px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:border-deli-500">
            </div>

            <div>
                <label class="text-xs text-[#C8C4BC] block mb-1">本文</label>
                <textarea name="body" rows="6" maxlength="2000"
                       class="w-full bg-surface-600 border border-surface-300 rounded-lg px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:border-deli-500 resize-none">{{ old('body') }}</textarea>
                @error('body')<p class="text-xs text-deli-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-xs text-[#C8C4BC] block mb-1">画像（最大8枚）</label>
                <input type="file" name="images[]" multiple accept="image/*"
                       class="w-full text-sm text-[#C8C4BC] file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-deli-500 file:text-white hover:file:bg-deli-400">
                @error('images.*')<p class="text-xs text-deli-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-xs text-[#C8C4BC] block mb-1">公開設定</label>
                <select name="status" class="bg-surface-600 border border-surface-300 rounded-lg px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:border-deli-500">
                    <option value="published" @selected(old('status','published')==='published')>公開</option>
                    <option value="draft" @selected(old('status')==='draft')>下書き</option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex justify-end gap-3">
            <a href="{{ route('cast-diary.index', $cast->id) }}/" class="text-sm text-[#6A6A7E] hover:text-[#C8C4BC] px-4 py-2">キャンセル</a>
            <button type="submit" class="bg-deli-500 hover:bg-deli-400 text-white text-sm font-bold px-6 py-2 rounded-lg transition">投稿する</button>
        </div>
    </form>
</div>
@endsection
