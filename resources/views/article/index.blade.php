@extends('layouts.app')

@section('title', ($currentCategory ? $currentCategory->name . ' | ' : '') . 'コラム・ガイド | ナイトワークリスト')
@section('description', 'ナイトワーク・夜遊びに役立つコラム・ガイド記事を掲載。求人の選び方、給与相場、業種解説など。')
@section('canonical', route('article.index') . ($currentCategory ? '?category=' . $currentCategory->slug : ''))

@section('content')

<div class="max-w-5xl mx-auto px-4 py-10">

    {{-- ページヘッダー --}}
    <div class="mb-8">
        <nav class="text-xs text-gray-400 mb-3">
            <a href="{{ route('top') }}/" class="hover:text-gray-600">ナイトワーク</a>
            <span class="mx-1">›</span>
            <span>コラム・ガイド</span>
            @if($currentCategory)
            <span class="mx-1">›</span>
            <span>{{ $currentCategory->name }}</span>
            @endif
        </nav>
        <h1 class="text-2xl font-bold text-gray-800">
            {{ $currentCategory ? $currentCategory->name : 'コラム・ガイド' }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">ナイトワーク・夜遊びに役立つ情報をお届けします</p>
    </div>

    {{-- カテゴリフィルター --}}
    @if($categories->isNotEmpty())
    <div class="flex flex-wrap gap-2 mb-8">
        <a href="{{ route('article.index') }}"
           class="px-3 py-1 rounded-full text-xs border transition
                  {{ !$currentCategory ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' }}">
            すべて
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('article.index') }}?category={{ $cat->slug }}"
           class="px-3 py-1 rounded-full text-xs border transition
                  {{ $currentCategory?->id === $cat->id ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' }}">
            {{ $cat->name }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- 記事一覧 --}}
    @if($articles->isEmpty())
    <div class="text-center py-20 text-gray-400">
        <p>記事がまだありません</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        @foreach($articles as $article)
        <a href="{{ route('article.show', $article->slug) }}"
           class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition group">
            @if($article->hero_image)
            <div class="h-44 overflow-hidden">
                <img src="{{ asset('storage/' . $article->hero_image) }}"
                     alt="{{ $article->title }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            </div>
            @else
            <div class="h-44 bg-gradient-to-br
                @if($article->gender === 'female') from-female-100 to-female-50
                @elseif($article->gender === 'male') from-male-100 to-male-50
                @elseif($article->gender === 'business') from-business-100 to-business-50
                @elseif($article->gender === 'shop') from-green-100 to-green-50
                @else from-gray-100 to-gray-50 @endif
                flex items-center justify-center">
                <span class="text-4xl opacity-30">📝</span>
            </div>
            @endif
            <div class="p-4">
                <div class="flex flex-wrap gap-1 mb-2">
                    @foreach($article->categories->take(2) as $cat)
                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">{{ $cat->name }}</span>
                    @endforeach
                </div>
                <h2 class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug mb-2 group-hover:text-gray-600 transition">
                    {{ $article->title }}
                </h2>
                @if($article->lead)
                <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">{{ $article->lead }}</p>
                @endif
                <p class="text-xs text-gray-400 mt-3">
                    {{ $article->published_at?->format('Y年n月j日') }}
                </p>
            </div>
        </a>
        @endforeach
    </div>

    {{ $articles->appends(request()->query())->links() }}
    @endif

</div>

@endsection
