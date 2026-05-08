@extends('layouts.app')
@section('title', '通知設定・好み設定')
@section('robots', 'noindex')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-[#F0ECE4] mb-6 flex items-center gap-3">
        <span class="w-1 h-7 bg-deli-500 rounded-full inline-block"></span>
        マイページ設定
    </h1>

    <div class="flex gap-4 mb-8 text-sm">
        <a href="{{ route('user.dashboard') }}/" class="text-[#6A6A7E] hover:text-[#C8C4BC] transition">お気に入り / 閲覧履歴</a>
        <a href="{{ route('user.settings') }}/" class="text-deli-400 border-b border-deli-500 pb-1">通知設定・好み</a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-emerald-900/40 border border-emerald-600/40 text-emerald-400 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('user.settings.update') }}/">
        @csrf

        {{-- 通知設定 --}}
        <div class="bg-surface-500 border border-surface-300 rounded-xl p-6 mb-5">
            <h2 class="text-sm font-bold text-[#E8E4DC] mb-4 flex items-center gap-2">
                <span class="w-1 h-4 bg-deli-500 rounded-full"></span>
                メール通知
            </h2>
            <div class="space-y-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="notify_working" value="1"
                           @checked($user->notify_working)
                           class="mt-1 rounded border-surface-300 bg-surface-600 text-deli-500 focus:ring-deli-500">
                    <div>
                        <p class="text-sm font-medium text-[#E8E4DC]">お気に入りキャストの出勤通知</p>
                        <p class="text-xs text-[#6A6A7E] mt-0.5">お気に入り登録したキャストが出勤予定になったときにお知らせします</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="notify_new_cast" value="1"
                           @checked($user->notify_new_cast)
                           class="mt-1 rounded border-surface-300 bg-surface-600 text-deli-500 focus:ring-deli-500">
                    <div>
                        <p class="text-sm font-medium text-[#E8E4DC]">新人キャスト通知</p>
                        <p class="text-xs text-[#6A6A7E] mt-0.5">新しいキャストが登録されたときにお知らせします（下記の好み設定と連動）</p>
                    </div>
                </label>
            </div>
        </div>


        {{-- 遊びやすい曜日・時間帯 --}}
        <div class="bg-surface-500 border border-surface-300 rounded-xl p-6 mb-5">
            <h2 class="text-sm font-bold text-[#E8E4DC] mb-1 flex items-center gap-2">
                <span class="w-1 h-4 bg-gold-500 rounded-full"></span>
                遊びやすい曜日・時間帯
            </h2>
            <p class="text-xs text-[#6A6A7E] mb-4">登録すると、店舗やキャストがあなたのような方が多い時間に出勤を組みやすくなります。</p>

            <p class="text-xs font-bold text-[#9A96A0] mb-2">曜日</p>
            <div class="flex flex-wrap gap-2 mb-5">
                @php $days = ['mon'=>'月','tue'=>'火','wed'=>'水','thu'=>'木','fri'=>'金','sat'=>'土','sun'=>'日']; @endphp
                @foreach($days as $val => $label)
                <label class="cursor-pointer">
                    <input type="checkbox" name="preferred_days[]" value="{{ $val }}"
                           @checked(in_array($val, $user->preferred_days ?? []))
                           class="sr-only peer">
                    <span class="flex items-center justify-center w-10 h-10 rounded-full border text-sm font-medium transition
                                 border-surface-300 text-[#6A6A7E]
                                 peer-checked:bg-deli-500 peer-checked:border-deli-500 peer-checked:text-white
                                 hover:border-deli-400 hover:text-deli-400">{{ $label }}</span>
                </label>
                @endforeach
            </div>

            <p class="text-xs font-bold text-[#9A96A0] mb-2">時間帯</p>
            <div class="flex flex-wrap gap-2">
                @php $times = ['morning'=>'午前（〜13時）','afternoon'=>'昼間（13〜17時）','evening'=>'夕方（17〜20時）','night'=>'夜（20〜24時）','midnight'=>'深夜（0〜5時）']; @endphp
                @foreach($times as $val => $label)
                <label class="cursor-pointer">
                    <input type="checkbox" name="preferred_times[]" value="{{ $val }}"
                           @checked(in_array($val, $user->preferred_times ?? []))
                           class="sr-only peer">
                    <span class="px-3 py-1.5 rounded-full border text-xs font-medium transition
                                 border-surface-300 text-[#6A6A7E]
                                 peer-checked:bg-deli-500 peer-checked:border-deli-500 peer-checked:text-white
                                 hover:border-deli-400 hover:text-deli-400">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- タイプ好み --}}
        <div class="bg-surface-500 border border-surface-300 rounded-xl p-6 mb-5">
            <h2 class="text-sm font-bold text-[#E8E4DC] mb-1 flex items-center gap-2">
                <span class="w-1 h-4 bg-gold-500 rounded-full"></span>
                好みのタイプ
            </h2>
            <p class="text-xs text-[#6A6A7E] mb-4">選択したタイプのキャストのみ新人通知・おすすめに表示されます。未選択の場合はすべて対象。</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($castTypes as $type)
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" name="pref_cast_type_ids[]" value="{{ $type->id }}"
                           @checked(in_array($type->id, $user->pref_cast_type_ids ?? []))
                           class="rounded border-surface-300 bg-surface-600 text-deli-500 focus:ring-deli-500">
                    <span class="text-sm text-[#C8C4BC] group-hover:text-[#E8E4DC] transition">{{ $type->name }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- エリア好み --}}
        <div class="bg-surface-500 border border-surface-300 rounded-xl p-6 mb-5">
            <h2 class="text-sm font-bold text-[#E8E4DC] mb-1 flex items-center gap-2">
                <span class="w-1 h-4 bg-gold-500 rounded-full"></span>
                好みのエリア
            </h2>
            <p class="text-xs text-[#6A6A7E] mb-4">選択したエリアの店舗のキャストのみ対象になります。未選択の場合はすべて対象。</p>
            <div class="space-y-4">
                @foreach($areas as $prefName => $areaGroup)
                <div>
                    <p class="text-xs font-bold text-[#9A96A0] mb-2">{{ $prefName }}</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                        @foreach($areaGroup as $area)
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" name="pref_area_ids[]" value="{{ $area->id }}"
                                   @checked(in_array($area->id, $user->pref_area_ids ?? []))
                                   class="rounded border-surface-300 bg-surface-600 text-deli-500 focus:ring-deli-500">
                            <span class="text-sm text-[#C8C4BC] group-hover:text-[#E8E4DC] transition">{{ $area->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="text-right">
            <button type="submit" class="bg-deli-500 hover:bg-deli-400 text-white text-sm font-bold px-8 py-2.5 rounded-lg transition">
                保存する
            </button>
        </div>
    </form>
</div>
@endsection
