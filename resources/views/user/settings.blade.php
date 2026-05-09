@extends('layouts.app')
@section('title', '設定')
@section('robots', 'noindex')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-[#F0ECE4] mb-6 flex items-center gap-3">
        <span class="w-1 h-7 bg-deli-500 rounded-full inline-block"></span>
        マイページ
    </h1>

    {{-- タブナビ --}}
    <div class="flex gap-4 mb-8 text-sm border-b border-surface-300">
        <a href="{{ route('user.dashboard') }}/" class="text-[#6A6A7E] hover:text-[#C8C4BC] pb-2 transition">お気に入り / 閲覧履歴</a>
        <a href="{{ route('user.settings') }}/?tab=notify"
           class="pb-2 transition {{ request('tab', 'notify') === 'notify' ? 'text-deli-400 border-b-2 border-deli-500' : 'text-[#6A6A7E] hover:text-[#C8C4BC]' }}">新人通知</a>
        <a href="{{ route('user.settings') }}/?tab=prefs"
           class="pb-2 transition {{ request('tab') === 'prefs' ? 'text-deli-400 border-b-2 border-deli-500' : 'text-[#6A6A7E] hover:text-[#C8C4BC]' }}">好み</a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-emerald-900/40 border border-emerald-600/40 text-emerald-400 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- ============ 新人通知タブ ============ --}}
    @if(request('tab', 'notify') === 'notify')
    <form method="POST" action="{{ route('user.settings.update') }}/?tab=notify">
        @csrf

        <div class="bg-surface-500 border border-surface-300 rounded-xl p-6 mb-5">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="notify_new_cast" value="1"
                       @checked($user->notify_new_cast)
                       class="mt-1 rounded border-surface-300 bg-surface-600 text-deli-500 focus:ring-deli-500">
                <div>
                    <p class="text-sm font-medium text-[#E8E4DC]">新人キャスト通知を受け取る</p>
                    <p class="text-xs text-[#6A6A7E] mt-0.5">
                        新しいキャストが登録されたときにメールでお知らせします。<br>
                        「好み」タブで設定した条件に合致するキャスト、および通知登録した店舗の新人キャストが対象です。
                    </p>
                </div>
            </label>
        </div>

        {{-- 通知登録済み店舗一覧 --}}
        <div class="bg-surface-500 border border-surface-300 rounded-xl p-6 mb-5">
            <h2 class="text-sm font-bold text-[#E8E4DC] mb-1 flex items-center gap-2">
                <span class="w-1 h-4 bg-deli-500 rounded-full"></span>
                新人通知登録済みの店舗
            </h2>
            <p class="text-xs text-[#6A6A7E] mb-4">店舗詳細ページから通知登録した店舗です。解除するには店舗詳細ページの通知ボタンを押してください。</p>
            @if($notifyShops->isEmpty())
            <p class="text-sm text-[#6A6A7E]">通知登録している店舗はありません。</p>
            @else
            <ul class="space-y-2">
                @foreach($notifyShops as $ns)
                @if($ns->shop)
                <li class="flex items-center justify-between py-2 border-b border-surface-300 last:border-0">
                    <a href="{{ route('shop.show', $ns->shop->id) }}/" class="text-sm text-deli-400 hover:underline">{{ $ns->shop->name }}</a>
                </li>
                @endif
                @endforeach
            </ul>
            @endif
        </div>

        <div class="text-right">
            <button type="submit" class="bg-deli-500 hover:bg-deli-400 text-white text-sm font-bold px-8 py-2.5 rounded-lg transition">
                保存する
            </button>
        </div>
    </form>

    {{-- ============ 好みタブ ============ --}}
    {{-- ============ 好みタブ ============ --}}
    @else
    <form method="POST" action="{{ route('user.settings.update') }}/?tab=prefs">
        @csrf

        {{-- 年齢 --}}
        <div class="bg-surface-500 border border-surface-300 rounded-xl p-6 mb-5">
            <h2 class="text-sm font-bold text-[#E8E4DC] mb-1 flex items-center gap-2">
                <span class="w-1 h-4 bg-gold-500 rounded-full"></span>
                好みの年齢
            </h2>
            <p class="text-xs text-[#6A6A7E] mb-4">未入力の場合はすべての年齢が対象になります。</p>
            <div class="flex items-center gap-3">
                <div class="flex-1">
                    <label class="text-xs text-[#9A96A0] block mb-1">下限（歳）</label>
                    <input type="number" name="pref_age_min" min="18" max="80"
                           value="{{ $user->pref_age_min }}"
                           placeholder="例: 18"
                           class="w-full bg-surface-600 border border-surface-300 rounded-lg px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:ring-1 focus:ring-deli-500">
                </div>
                <span class="text-[#6A6A7E] mt-5">〜</span>
                <div class="flex-1">
                    <label class="text-xs text-[#9A96A0] block mb-1">上限（歳）</label>
                    <input type="number" name="pref_age_max" min="18" max="80"
                           value="{{ $user->pref_age_max }}"
                           placeholder="例: 35"
                           class="w-full bg-surface-600 border border-surface-300 rounded-lg px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:ring-1 focus:ring-deli-500">
                </div>
            </div>
        </div>

        {{-- タイプ好み --}}
        <div class="bg-surface-500 border border-surface-300 rounded-xl p-6 mb-5">
            <h2 class="text-sm font-bold text-[#E8E4DC] mb-1 flex items-center gap-2">
                <span class="w-1 h-4 bg-gold-500 rounded-full"></span>
                好みのタイプ
            </h2>
            <p class="text-xs text-[#6A6A7E] mb-4">未選択の場合はすべてのタイプが対象です。</p>
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

        {{-- 体型好み --}}
        <div class="bg-surface-500 border border-surface-300 rounded-xl p-6 mb-5">
            <h2 class="text-sm font-bold text-[#E8E4DC] mb-1 flex items-center gap-2">
                <span class="w-1 h-4 bg-gold-500 rounded-full"></span>
                好みの体型
            </h2>
            <p class="text-xs text-[#6A6A7E] mb-4">未選択の場合はすべての体型が対象です。</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($bodyTypes as $bt)
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" name="pref_body_type_ids[]" value="{{ $bt->id }}"
                           @checked(in_array($bt->id, $user->pref_body_type_ids ?? []))
                           class="rounded border-surface-300 bg-surface-600 text-deli-500 focus:ring-deli-500">
                    <span class="text-sm text-[#C8C4BC] group-hover:text-[#E8E4DC] transition">{{ $bt->name }}</span>
                </label>
                @endforeach
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

        {{-- エリア好み（都道府県アコーディオン＋検索） --}}
        <div class="bg-surface-500 border border-surface-300 rounded-xl p-6 mb-5"
             x-data="{
                 search: '',
                 open: {},
                 toggle(pref) { this.open[pref] = !this.open[pref]; },
                 isOpen(pref) { return this.search !== '' || !!this.open[pref]; }
             }">
            <h2 class="text-sm font-bold text-[#E8E4DC] mb-1 flex items-center gap-2">
                <span class="w-1 h-4 bg-gold-500 rounded-full"></span>
                好みのエリア
            </h2>
            <p class="text-xs text-[#6A6A7E] mb-3">未選択の場合はすべてのエリアが対象です。都道府県をタップして展開してください。</p>
            <input type="text" x-model="search" placeholder="都道府県・エリア名で検索…"
                   class="w-full bg-surface-600 border border-surface-300 rounded-lg px-3 py-2 text-sm text-[#E8E4DC] placeholder-[#6A6A7E] focus:outline-none focus:ring-1 focus:ring-deli-500 mb-3">
            <div class="space-y-1">
                @foreach($areas as $prefName => $areaGroup)
                @php
                    $hasChecked = collect($areaGroup)->contains(fn($a) => in_array($a->id, $user->pref_area_ids ?? []));
                    $areaNames  = $areaGroup->pluck('name')->join(' ');
                @endphp
                <div x-show="search === '' || '{{ addslashes($prefName) }}'.includes(search) || '{{ addslashes($areaNames) }}'.includes(search)">
                    {{-- 都道府県ヘッダー --}}
                    <button type="button"
                            @click="toggle('{{ addslashes($prefName) }}')"
                            class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-surface-400 transition text-left">
                        <span class="text-sm font-medium text-[#C8C4BC] flex items-center gap-2">
                            {{ $prefName }}
                            @if($hasChecked)
                            <span class="text-[9px] bg-deli-500 text-white px-1.5 py-0.5 rounded-full">設定済</span>
                            @endif
                        </span>
                        <svg x-show="!isOpen('{{ addslashes($prefName) }}')" class="w-4 h-4 text-[#6A6A7E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        <svg x-show="isOpen('{{ addslashes($prefName) }}')" class="w-4 h-4 text-deli-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                    </button>
                    {{-- エリア一覧 --}}
                    <div x-show="isOpen('{{ addslashes($prefName) }}')" x-transition
                         class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 px-3 pb-3 pt-1">
                        @foreach($areaGroup as $area)
                        <label class="flex items-center gap-2 cursor-pointer group"
                               x-show="search === '' || '{{ addslashes($area->name) }}'.includes(search) || '{{ addslashes($prefName) }}'.includes(search)">
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
    @endif
</div>
@endsection
