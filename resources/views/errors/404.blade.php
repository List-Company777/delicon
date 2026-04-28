@extends('layouts.app')

@section('title', 'ページが見つかりません')
@section('robots', 'noindex, follow')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-16 text-center">

    <p class="text-6xl font-bold text-yellow-400 mb-4">404</p>
    <h1 class="text-2xl font-bold text-gray-700 mb-3">ページが見つかりません</h1>
    <p class="text-gray-500 text-sm mb-10">
        お探しのページは削除・非公開になったか、URLが変更された可能性があります。
    </p>

    {{-- 性別別トップ --}}
    <div class="grid grid-cols-3 gap-3 mb-10">
        <a href="{{ route('search.directory', ['gender' => 'female', 'area_slug' => 'all', 'job_slug' => 'all']) }}/"
           class="block bg-pink-50 hover:bg-pink-100 border border-pink-200 rounded-xl py-5 transition">
            <span class="block text-2xl mb-1">👩</span>
            <span class="block text-sm font-bold text-pink-700">女性ナイトワーク</span>
        </a>
        <a href="{{ route('search.directory', ['gender' => 'male', 'area_slug' => 'all', 'job_slug' => 'all']) }}/"
           class="block bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl py-5 transition">
            <span class="block text-2xl mb-1">👨</span>
            <span class="block text-sm font-bold text-blue-700">男性ナイトワーク</span>
        </a>
        <a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => 'all', 'job_slug' => 'all']) }}/"
           class="block bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 rounded-xl py-5 transition">
            <span class="block text-2xl mb-1">🍸</span>
            <span class="block text-sm font-bold text-yellow-700">夜遊び情報</span>
        </a>
    </div>

    {{-- 人気エリア --}}
    @php
    $areas = [
        ['新宿','shinjuku'],['池袋','ikebukuro'],['渋谷','shibuya'],['六本木','roppongi'],
        ['銀座','ginza'],['赤坂','akasaka'],['中洲','nakasu'],['すすきの','susukino'],
        ['梅田','umeda'],['難波','namba'],
    ];
    @endphp

    <div class="space-y-3 mb-8 text-left">
        <div class="bg-white border border-pink-100 rounded-xl px-5 py-4">
            <p class="text-xs font-bold text-pink-400 mb-3">👩 女性ナイトワーク</p>
            <div class="flex flex-wrap gap-2">
                @foreach($areas as [$name, $slug])
                <a href="{{ route('search.directory', ['gender' => 'female', 'area_slug' => $slug, 'job_slug' => 'all']) }}/"
                   class="px-3 py-1 bg-pink-50 hover:bg-pink-100 text-pink-700 text-xs rounded-full transition">{{ $name }}</a>
                @endforeach
            </div>
        </div>
        <div class="bg-white border border-blue-100 rounded-xl px-5 py-4">
            <p class="text-xs font-bold text-blue-400 mb-3">👨 男性ナイトワーク</p>
            <div class="flex flex-wrap gap-2">
                @foreach($areas as [$name, $slug])
                <a href="{{ route('search.directory', ['gender' => 'male', 'area_slug' => $slug, 'job_slug' => 'all']) }}/"
                   class="px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs rounded-full transition">{{ $name }}</a>
                @endforeach
            </div>
        </div>
        <div class="bg-white border border-yellow-100 rounded-xl px-5 py-4">
            <p class="text-xs font-bold text-yellow-500 mb-3">🍸 夜遊び情報</p>
            <div class="flex flex-wrap gap-2">
                @foreach($areas as [$name, $slug])
                <a href="{{ route('search.directory', ['gender' => 'yoasobi', 'area_slug' => $slug, 'job_slug' => 'all']) }}/"
                   class="px-3 py-1 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 text-xs rounded-full transition">{{ $name }}</a>
                @endforeach
            </div>
        </div>
    </div>

    <a href="{{ route('top') }}/"
       class="inline-block px-6 py-3 bg-gray-800 hover:bg-gray-700 text-white text-sm font-bold rounded-lg transition">
        トップページへ戻る
    </a>

</div>
@endsection
