@php
    $colorMap = [
        'male'   => ['bar' => 'bg-male-800',   'text' => 'text-male-600',   'btn' => 'bg-male-600 hover:bg-male-700',   'ring' => 'focus:ring-male-300',   'label' => '男性ナイトワーク', 'genderLabel' => '男性'],
        'female' => ['bar' => 'bg-female-600', 'text' => 'text-female-500', 'btn' => 'bg-female-600 hover:bg-female-500', 'ring' => 'focus:ring-female-400', 'label' => '女性ナイトワーク', 'genderLabel' => '女性'],
    ];
    $c = $colorMap[$gender] ?? $colorMap['female'];
@endphp

@extends('layouts.app')

@section('title', '応募フォーム | ' . $job->title)
@section('description', $job->shop->name . 'の求人「' . $job->title . '」に応募するフォームです。')
@section('robots', 'noindex, follow')

@push('head')
@php
    $breadcrumb = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'ナイトワーク',    'item' => route('top') . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $c['genderLabel'], 'item' => route('shop.list', ['area_slug' => 'all']) . '/'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $job->shop->name,  'item' => route('job.show', $job->id) . '/'],
            ['@type' => 'ListItem', 'position' => 4, 'name' => '応募フォーム',    'item' => route('apply.create', $job->id) . '/'],
        ],
    ];
@endphp
<script type="application/ld+json" @nonce>
{!! json_encode($breadcrumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG) !!}
</script>
@endpush

@section('content')

{{-- カラーバー --}}
<div class="{{ $c['bar'] }} text-white py-3">
    <div class="max-w-2xl mx-auto px-4 text-sm">
        <a href="{{ route('top') }}/" class="opacity-70 hover:opacity-100">ナイトワーク</a>
        <span class="mx-2 opacity-40">›</span>
        <a href="{{ route('shop.list', ['area_slug' => 'all']) }}/" class="opacity-70 hover:opacity-100">{{ $c['genderLabel'] }}</a>
        <span class="mx-2 opacity-40">›</span>
        <a href="{{ route('job.show', $job->id) }}/" class="opacity-70 hover:opacity-100">{{ $job->shop->name }}</a>
        <span class="mx-2 opacity-40">›</span>
        <span class="opacity-90">応募フォーム</span>
    </div>
</div>

<div class="max-w-2xl mx-auto px-4 py-8">

    {{-- 応募先の求人サマリ --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 mb-6">
        <p class="text-xs text-gray-400 mb-1">応募先</p>
        <h1 class="font-bold text-gray-800 text-lg leading-snug mb-1">{{ $job->title }}</h1>
        <p class="text-sm text-gray-500 mb-3">{{ $job->shop->name }}</p>
        <div class="flex flex-wrap gap-3 text-sm">
            @if($job->hourly_wage_min)
                <span class="{{ $c['text'] }} font-bold">
                    {{ ['hourly'=>'時給','daily'=>'日給','monthly'=>'月給'][$job->wage_type ?? 'hourly'] }}
                    {{ number_format($job->hourly_wage_min) }}円〜{{ $job->hourly_wage_max ? number_format($job->hourly_wage_max) . '円' : '' }}
                </span>
            @endif
            @if($job->jobType)
                <span class="text-gray-500">{{ $job->jobType->name }}</span>
            @endif
            @if($job->area)
                <span class="text-gray-400">📍 {{ $job->area->name }}</span>
            @endif
        </div>
    </div>

    {{-- 応募フォーム --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 md:p-8">
        <h2 class="font-bold text-gray-800 text-lg mb-6">応募情報を入力してください</h2>

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <ul class="text-sm text-red-600 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('apply.store', $job->id) }}/" method="POST" novalidate>
            @csrf

            {{-- お名前 --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    お名前（ニックネーム可）<span class="text-red-500 ml-1">*</span>
                </label>
                <input type="text" name="applicant_name"
                       value="{{ old('applicant_name') }}"
                       placeholder="例：山田 花子"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 {{ $c['ring'] }} focus:border-transparent @error('applicant_name') border-red-400 @enderror">
            </div>

            {{-- 年齢 --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">年齢</label>
                <div class="flex items-center gap-2">
                    <input type="number" name="applicant_age"
                           value="{{ old('applicant_age') }}"
                           min="18" max="99"
                           placeholder="例：22"
                           class="w-28 border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 {{ $c['ring'] }} focus:border-transparent @error('applicant_age') border-red-400 @enderror">
                    <span class="text-sm text-gray-500">歳（18歳以上）</span>
                </div>
            </div>

            {{-- メールアドレス --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    メールアドレス<span class="text-red-500 ml-1">*</span>
                </label>
                <input type="email" name="applicant_email"
                       value="{{ old('applicant_email') }}"
                       placeholder="例：example@mail.com"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 {{ $c['ring'] }} focus:border-transparent @error('applicant_email') border-red-400 @enderror">
                <p class="text-xs text-gray-400 mt-1">確認メールが届きます</p>
            </div>

            {{-- 電話番号 --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">電話番号<span class="text-red-500 ml-1">*</span></label>
                <input type="tel" name="applicant_tel" required
                       value="{{ old('applicant_tel') }}"
                       placeholder="例：090-0000-0000"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 {{ $c['ring'] }} focus:border-transparent @error('applicant_tel') border-red-400 @enderror">
            </div>

            {{-- メッセージ --}}
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-1">メッセージ（任意）</label>
                <textarea name="message"
                          rows="4"
                          placeholder="経験・希望シフトなど自由にご記入ください"
                          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 {{ $c['ring'] }} focus:border-transparent @error('message') border-red-400 @enderror resize-none">{{ old('message') }}</textarea>
            </div>

            {{-- 注意事項 --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-lg text-xs text-gray-500 leading-relaxed">
                ご応募いただいた情報は、求人掲載店舗への連絡にのみ使用します。
                18歳未満の方はご応募いただけません。
            </div>

            {{-- 送信ボタン --}}
            <button type="submit"
                    class="{{ $c['btn'] }} text-white font-bold py-4 px-8 rounded-xl w-full text-base transition">
                この求人に応募する
            </button>
        </form>
    </div>

    {{-- 戻るリンク --}}
    <div class="mt-6 text-center">
        <a href="{{ route('job.show', $job->id) }}/" class="text-sm text-gray-400 hover:text-gray-600">
            ← 求人詳細に戻る
        </a>
    </div>

</div>
@endsection
