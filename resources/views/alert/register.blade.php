@extends('layouts.app')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2/dist/css/tom-select.min.css">
<style>
.ts-wrapper .ts-control { border-radius: 0.5rem; border-color: #d1d5db; padding: 0.625rem 0.75rem; font-size: 0.875rem; color: #374151; }
.ts-wrapper.focus .ts-control { outline: none; box-shadow: 0 0 0 2px #9ca3af; border-color: #9ca3af; }
.ts-dropdown { border-color: #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; }
.ts-dropdown .option.selected, .ts-dropdown .option:hover { background: #f3f4f6; color: #111827; }
.ts-dropdown .active { background: #e5e7eb !important; color: #111827 !important; }
</style>
@endpush

@php
$colorMap = [
    'female' => ['bg' => 'bg-female-600',  'btn' => 'bg-female-600 hover:bg-female-500',  'label' => '女性ナイトワーク', 'text' => 'text-female-600'],
    'male'   => ['bg' => 'bg-male-800',    'btn' => 'bg-male-600 hover:bg-male-700',       'label' => '男性ナイトワーク', 'text' => 'text-male-700'],
    'both'   => ['bg' => 'bg-gray-700',    'btn' => 'bg-gray-700 hover:bg-gray-600',       'label' => '両方',            'text' => 'text-gray-700'],
];
$c = $colorMap[$gender];
@endphp

@section('title', '求人アラート登録')
@section('robots', 'noindex')

@section('content')
<div class="max-w-xl mx-auto px-4 py-10">

    {{-- ヘッダー --}}
    <div class="{{ $c['bg'] }} rounded-2xl px-6 py-6 mb-8 text-white text-center">
        <div class="text-2xl mb-1">🔔</div>
        <h1 class="text-xl font-bold mb-1">求人アラート登録</h1>
        <p class="text-sm opacity-90">{{ $c['label'] }}の新着求人をLINEでお知らせします</p>
    </div>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 mb-6 text-sm">
        {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('alert.store') }}" method="POST">
        @csrf
        <input type="hidden" name="gender" value="{{ $gender }}">

        <div class="bg-white rounded-xl shadow-sm px-5 py-5 space-y-5 mb-4">

            {{-- カテゴリ --}}
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1.5">カテゴリ</label>
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm font-bold {{ $c['text'] }}">{{ $c['label'] }}</div>
            </div>

            {{-- エリア --}}
            <div>
                <label for="area_id" class="block text-sm font-medium text-gray-600 mb-1.5">エリア</label>
                <select name="area_id" id="area_id" required
                    class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-700">
                    <option value="" disabled selected>都道府県を入力してください</option>
                    @foreach($areas as $prefName => $prefAreas)
                    <optgroup label="{{ $prefName }}">
                        @foreach($prefAreas as $area)
                        <option value="{{ $area->id }}" data-pref="{{ $prefName }}">{{ $area->name }}</option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1.5">都道府県・エリア名で絞り込めます。</p>
            </div>

            {{-- 職種 --}}
            <div>
                <label for="job_type_id" class="block text-sm font-medium text-gray-600 mb-1.5">職種</label>
                <select name="job_type_id" id="job_type_id"
                    class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-gray-400">
                    <option value="">なんでも（指定なし）</option>
                    @foreach($jobTypes as $jobType)
                    <option value="{{ $jobType->id }}">{{ $jobType->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 条件 --}}
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">条件</label>
                <div class="space-y-2.5">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="daily_pay_ok" value="1"
                            class="w-4 h-4 rounded border-gray-300 text-gray-700 cursor-pointer">
                        <span class="text-sm text-gray-700">日払いOKの求人のみ</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="inexperienced_ok" value="1"
                            class="w-4 h-4 rounded border-gray-300 text-gray-700 cursor-pointer">
                        <span class="text-sm text-gray-700">未経験歓迎の求人のみ</span>
                    </label>
                    @if($gender !== 'female')
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="arubaito" value="1"
                            class="w-4 h-4 rounded border-gray-300 text-gray-700 cursor-pointer">
                        <span class="text-sm text-gray-700">アルバイトの求人のみ</span>
                    </label>
                    @endif
                </div>
            </div>

        </div>

        <button type="submit"
            class="{{ $c['btn'] }} w-full text-white font-bold py-5 rounded-xl text-base transition">
            次へ（LINEで登録を完了する）
        </button>
        <p class="text-xs text-gray-400 text-center mt-3">次の画面でLINEを開いてアラート登録が完了します</p>

    </form>

</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2/dist/js/tom-select.complete.min.js"></script>
<script>
new TomSelect('#area_id', {
    placeholder: '都道府県を入力してください',
    allowEmptyOption: false,
    maxOptions: 200,
    searchField: ['text', 'pref'],
});
</script>
@endpush

@endsection
