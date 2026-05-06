@extends('layouts.app')
@section('title', 'ページが見つかりません')
@section('robots', 'noindex, follow')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 py-16">
    <div class="text-center max-w-lg">
        <p class="text-7xl font-bold text-deli-500 mb-2 tracking-tight">404</p>
        <div class="w-16 h-px bg-gold-400 mx-auto mb-6"></div>
        <h1 class="text-xl font-bold text-[#E8E4DC] mb-3">ページが見つかりません</h1>
        <p class="text-[#8A8A9E] text-sm mb-10 leading-7">お探しのページは削除・非公開になったか、URLが変更された可能性があります。</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center mb-8">
            <a href="{{ route('shop.index') }}/"
               class="inline-block border border-surface-300 hover:border-deli-500 text-[#B0AEAD] hover:text-deli-400 px-6 py-2.5 rounded-lg text-sm transition">
                デリヘル店舗一覧
            </a>
            <a href="{{ route('cast.index') }}/"
               class="inline-block border border-surface-300 hover:border-deli-500 text-[#B0AEAD] hover:text-deli-400 px-6 py-2.5 rounded-lg text-sm transition">
                キャスト検索
            </a>
        </div>
        
        <a href="{ route('top') }/"
           class="inline-block bg-deli-500 hover:bg-deli-400 text-white font-bold px-8 py-3 rounded-lg transition">
            トップページへ戻る
        </a>
    </div>
</div>
@endsection