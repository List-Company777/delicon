@extends('layouts.app')
@section('title', 'サーバーエラーが発生しました')
@section('robots', 'noindex, follow')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 py-16">
    <div class="text-center max-w-lg">
        <p class="text-7xl font-bold text-deli-500 mb-2 tracking-tight">500</p>
        <div class="w-16 h-px bg-gold-400 mx-auto mb-6"></div>
        <h1 class="text-xl font-bold text-[#E8E4DC] mb-3">サーバーエラーが発生しました</h1>
        <p class="text-[#8A8A9E] text-sm mb-10 leading-7">申し訳ございません。サーバー側で問題が発生しました。<br>しばらく経ってから再度お試しください。</p>
        
        <a href="{ route('top') }/"
           class="inline-block bg-deli-500 hover:bg-deli-400 text-white font-bold px-8 py-3 rounded-lg transition">
            トップページへ戻る
        </a>
    </div>
</div>
@endsection