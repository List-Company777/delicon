@php
    $colorMap = [
        'male'   => ['bar' => 'bg-male-800',   'text' => 'text-male-600',   'btn' => 'bg-male-600 hover:bg-male-700',   'ring' => 'focus:ring-male-300',   'label' => '男性ナイトワーク', 'genderLabel' => '男性'],
        'female' => ['bar' => 'bg-female-600', 'text' => 'text-female-500', 'btn' => 'bg-female-600 hover:bg-female-500', 'ring' => 'focus:ring-female-400', 'label' => '女性ナイトワーク', 'genderLabel' => '女性'],
    ];
    $c = $colorMap[$gender] ?? $colorMap['female'];
@endphp

@extends('layouts.app')

@section('title', '応募内容の確認 | ' . $job->title)
@section('description', $job->shop->name . 'の求人「' . $job->title . '」への応募内容確認ページです。')
@section('robots', 'noindex, follow')

@push('head')
@php
    $breadcrumb = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'ナイトワーク',    'item' => route('top') . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $c['genderLabel'], 'item' => route('search.directory', ['gender' => $gender, 'area_slug' => 'all', 'job_slug' => 'all']) . '/'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $job->shop->name,  'item' => route('job.show', $job->id) . '/'],
            ['@type' => 'ListItem', 'position' => 4, 'name' => '応募内容の確認',  'item' => route('apply.confirm', $job->id) . '/'],
        ],
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($breadcrumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG) !!}
</script>
@endpush

@section('content')

{{-- カラーバー --}}
<div class="{{ $c['bar'] }} text-white py-3">
    <div class="max-w-2xl mx-auto px-4 text-sm">
        <a href="{{ route('top') }}/" class="opacity-70 hover:opacity-100">ナイトワーク</a>
        <span class="mx-2 opacity-40">›</span>
        <a href="{{ route('search.directory', ['gender' => $gender, 'area_slug' => 'all', 'job_slug' => 'all']) }}/" class="opacity-70 hover:opacity-100">{{ $c['genderLabel'] }}</a>
        <span class="mx-2 opacity-40">›</span>
        <a href="{{ route('job.show', $job->id) }}/" class="opacity-70 hover:opacity-100">{{ $job->shop->name }}</a>
        <span class="mx-2 opacity-40">›</span>
        <span class="opacity-90">応募内容の確認</span>
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
        </div>
    </div>

    {{-- 入力内容の確認 --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 md:p-8 mb-6">
        <h2 class="font-bold text-gray-800 text-lg mb-5">入力内容の確認</h2>

        <div class="border border-gray-100 rounded-lg overflow-hidden mb-6">
            <table class="w-full text-sm">
                <tr class="border-b border-gray-100">
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-32 whitespace-nowrap">お名前</th>
                    <td class="px-4 py-3 text-gray-800">{{ $data['applicant_name'] }}</td>
                </tr>
                @if(!empty($data['applicant_age']))
                <tr class="border-b border-gray-100">
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">年齢</th>
                    <td class="px-4 py-3 text-gray-800">{{ $data['applicant_age'] }}歳</td>
                </tr>
                @endif
                <tr class="border-b border-gray-100">
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">メールアドレス</th>
                    <td class="px-4 py-3 text-gray-800">{{ $data['applicant_email'] }}</td>
                </tr>
                <tr class="border-b border-gray-100">
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">電話番号</th>
                    <td class="px-4 py-3 text-gray-800">{{ $data['applicant_tel'] }}</td>
                </tr>
                @if(!empty($data['message']))
                <tr>
                    <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap align-top">メッセージ</th>
                    <td class="px-4 py-3 text-gray-800 whitespace-pre-line">{{ $data['message'] }}</td>
                </tr>
                @endif
            </table>
        </div>

        {{-- 注意事項 --}}
        <div class="mb-6 p-4 bg-gray-50 rounded-lg text-xs text-gray-500 leading-relaxed">
            上記の内容で応募します。送信後の取り消しはできません。内容をご確認の上、「この内容で応募する」を押してください。
        </div>

        {{-- 送信ボタン --}}
        <form action="{{ route('apply.final-store', $job->id) }}" method="POST">
            @csrf
            <button type="submit"
                    class="{{ $c['btn'] }} text-white font-bold py-4 px-8 rounded-xl w-full text-base transition">
                この内容で応募する
            </button>
        </form>
    </div>

    {{-- 修正リンク --}}
    <div class="text-center">
        <a href="{{ route('apply.create', $job->id) }}" class="text-sm text-gray-400 hover:text-gray-600">
            ← 入力内容を修正する
        </a>
    </div>

</div>
@endsection
