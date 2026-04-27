@extends('layouts.app')

@section('title', '求人アラート登録 - LINEで完了')
@section('robots', 'noindex')

@php
$genderLabel = match($webToken->gender) {
    'female' => '女性ナイトワーク',
    'male'   => '男性ナイトワーク',
    'both'   => '両方',
    default  => $webToken->gender,
};
$btnClass = match($webToken->gender) {
    'male'  => 'bg-male-600 hover:bg-male-700',
    'both'  => 'bg-gray-700 hover:bg-gray-600',
    default => 'bg-[#06C755] hover:bg-[#05b04c]',
};
@endphp

@section('content')
<div class="max-w-xl mx-auto px-4 py-10">

    {{-- ヘッダー --}}
    <div class="bg-green-500 rounded-2xl px-6 py-6 mb-8 text-white text-center">
        <div class="text-3xl mb-2">✅</div>
        <h1 class="text-xl font-bold mb-1">希望条件を設定しました</h1>
        <p class="text-sm opacity-90">最後にLINEで登録を完了してください</p>
    </div>

    {{-- 設定内容の確認 --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-gray-100 text-sm font-medium text-gray-500">設定内容</div>
        <div class="divide-y divide-gray-100 text-sm">
            <div class="flex px-5 py-3">
                <span class="text-gray-500 w-20 shrink-0">カテゴリ</span>
                <span class="font-medium text-gray-800">{{ $genderLabel }}</span>
            </div>
            <div class="flex px-5 py-3">
                <span class="text-gray-500 w-20 shrink-0">エリア</span>
                <span class="font-medium text-gray-800">{{ $webToken->area?->name ?? '全国' }}</span>
            </div>
            <div class="flex px-5 py-3">
                <span class="text-gray-500 w-20 shrink-0">職種</span>
                <span class="font-medium text-gray-800">{{ $webToken->jobType?->name ?? 'なんでも' }}</span>
            </div>
            @if($webToken->daily_pay_ok || $webToken->inexperienced_ok || $webToken->arubaito)
            <div class="flex px-5 py-3">
                <span class="text-gray-500 w-20 shrink-0">条件</span>
                <span class="font-medium text-gray-800">
                    @php $conditions = array_filter([
                        $webToken->daily_pay_ok    ? '日払いOK' : null,
                        $webToken->inexperienced_ok ? '未経験歓迎' : null,
                        $webToken->arubaito         ? 'アルバイト' : null,
                    ]); @endphp
                    {{ implode('・', $conditions) }}
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- LINE登録ボタン --}}
    <div class="bg-white rounded-xl shadow-sm px-5 py-6 mb-4 text-center">
        <p class="text-sm text-gray-600 mb-4">下のボタンからLINEを開いて、<br>メッセージを送信するとアラート登録が完了します。</p>
        <a href="{{ $lineUrl }}" rel="nofollow"
            class="inline-flex items-center justify-center gap-2 {{ $btnClass }} text-white font-bold px-8 py-6 rounded-xl text-base transition w-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 48 48" fill="white">
                <path d="M24 4C12.95 4 4 11.86 4 21.5c0 5.7 3.1 10.77 7.94 14.1-.35 1.3-1.27 4.72-1.46 5.46-.23.9.33 1.9 1.28 1.4.75-.39 6.05-4 8.5-5.63.56.07 1.14.11 1.74.11 11.05 0 20-7.86 20-17.5S35.05 4 24 4z"/>
            </svg>
            LINEでアラート登録を完了する
        </a>
    </div>

    {{-- 友だち未追加の場合 --}}
    <div class="bg-gray-50 rounded-xl px-5 py-4 text-center text-sm text-gray-500">
        <p class="mb-2">LINEが開かない場合は、先に友だち追加してください。</p>
        <a href="{{ $addFriendUrl }}" target="_blank" rel="noopener"
            class="inline-block text-[#06C755] font-medium hover:underline">
            友だち追加はこちら →
        </a>
    </div>

    {{-- 有効期限の案内 --}}
    <p class="text-xs text-gray-400 text-center mt-4">このリンクの有効期限は30分です</p>

</div>
@endsection
