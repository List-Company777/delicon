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
