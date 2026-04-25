@extends('layouts.app')

@section('title', 'アクセス権限がありません')
@section('robots', 'noindex, follow')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-16 text-center">

    <p class="text-6xl font-bold text-red-400 mb-4">403</p>
    <h1 class="text-2xl font-bold text-gray-700 mb-3">アクセス権限がありません</h1>
    <p class="text-gray-500 text-sm mb-10">
        このページにアクセスする権限がありません。<br>
        ログインしているアカウントをご確認ください。
    </p>

    <div class="flex flex-col sm:flex-row justify-center gap-3">
        <a href="{{ route('login') }}"
           class="inline-block px-6 py-3 bg-gray-800 hover:bg-gray-700 text-white text-sm font-bold rounded-lg transition">
            ログインページへ
        </a>
        <a href="{{ route('top') }}/"
           class="inline-block px-6 py-3 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 text-sm font-bold rounded-lg transition">
            トップページへ戻る
        </a>
    </div>

</div>
@endsection
