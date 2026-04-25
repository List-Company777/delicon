@php
    $genderLabel = match($article->gender) {
        'female'   => '女性ナイトワーク',
        'male'     => '男性ナイトワーク',
        'business' => '夜遊び',
        'shop'     => '店舗運営',
        default    => 'ナイトワーク',
    };
    $genderColor = match($article->gender) {
        'female'   => 'text-female-600',
        'male'     => 'text-male-600',
        'business' => 'text-business-700',
        'shop'     => 'text-green-700',
        default    => 'text-gray-600',
    };
    $jobBorderColor = match($article->gender) {
        'male'     => 'border-male-200 bg-male-50 hover:bg-male-100',
        'business' => 'border-business-200 bg-business-50 hover:bg-business-100',
        default    => 'border-female-100 bg-female-50 hover:bg-female-100',
    };
    $jobTextColor = match($article->gender) {
        'male'     => 'text-male-600',
        'business' => 'text-business-700',
        default    => 'text-female-600',
    };
@endphp

@extends('layouts.app')

@section('canonical', route('article.show', $article->slug))
@section('title', $article->title . ' | ナイトワークリスト')
@section('description', $article->lead ?? mb_strimwidth(strip_tags($article->body ?? ''), 0, 120, '…'))
@if($article->is_published && $article->published_at?->lte(now()))
@section('robots', 'index, follow')
@else
@section('robots', 'noindex, follow')
@endif
@if($article->hero_image)
@section('ogp_image', asset('storage/' . $article->hero_image))
@section('twitter_card', 'summary_large_image')
@endif

@push('head')
<style>
#article-body h2 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1f2937;
    margin-top: 3rem;
    margin-bottom: 1rem;
    padding: 0.5rem 0 0.5rem 0.85rem;
    border-left: 4px solid #fbbf24;
    background: linear-gradient(to right, #fffbeb, transparent);
    line-height: 1.5;
}
#article-body h3 {
    font-size: 1.05rem;
    font-weight: 700;
    color: #374151;
    margin-top: 2.25rem;
    margin-bottom: 0.75rem;
    padding-bottom: 0.35rem;
    border-bottom: 2px dashed #e5e7eb;
    line-height: 1.5;
}
#article-body p {
    color: #374151;
    line-height: 1.95;
    margin-bottom: 1.25rem;
}
#article-body ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin-top: 0.75rem;
    margin-bottom: 1.25rem;
}
#article-body ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin-top: 0.75rem;
    margin-bottom: 1.25rem;
}
#article-body li {
    color: #374151;
    line-height: 1.85;
    margin-bottom: 0.4rem;
}
#article-body a {
    color: #2563eb;
    text-decoration: underline;
}
#article-body strong {
    color: #111827;
}
</style>
@php
    $ld = [
        '@context'         => 'https://schema.org',
        '@type'            => 'Article',
        'headline'         => $article->title,
        'description'      => $article->lead ?? '',
        'datePublished'    => $article->published_at?->toIso8601String(),
        'dateModified'     => ($article->updated_at_manual ?? $article->updated_at)?->toIso8601String(),
        'author'           => ['@type' => 'Organization', 'name' => 'ナイトワークリスト編集部'],
        'publisher'        => ['@type' => 'Organization', 'name' => 'ナイトワークリスト',
                               'logo' => ['@type' => 'ImageObject', 'url' => asset('android-chrome-192x192.png')]],
    ];
    if ($article->hero_image) {
        $ld['image'] = asset('storage/' . $article->hero_image);
    }

    $breadcrumb = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'ナイトワーク', 'item' => route('top') . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'コラム・ガイド', 'item' => route('article.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $article->title, 'item' => route('article.show', $article->slug)],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')

<div class="max-w-3xl mx-auto px-4 py-8">

    {{-- パンくず --}}
    <nav class="text-xs text-gray-400 mb-6">
        <a href="{{ route('top') }}/" class="hover:text-gray-600">ナイトワーク</a>
        <span class="mx-1">›</span>
        <a href="{{ route('article.index') }}" class="hover:text-gray-600">コラム・ガイド</a>
        <span class="mx-1">›</span>
        <span class="text-gray-600">{{ $article->title }}</span>
    </nav>

    <article>

        {{-- カテゴリ・タグ・日付 --}}
        <div class="flex flex-wrap items-center gap-2 mb-3">
            @foreach($article->categories as $cat)
            <a href="{{ route('article.index') }}?category={{ $cat->slug }}"
               class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full hover:bg-gray-200 transition">
                {{ $cat->name }}
            </a>
            @endforeach
            <span class="text-xs text-gray-400 ml-auto">
                {{ $article->published_at?->format('Y年n月j日') }}
                @if($article->updated_at_manual && $article->updated_at_manual->gt($article->published_at))
                <span class="ml-2">更新: {{ $article->updated_at_manual->format('Y年n月j日') }}</span>
                @endif
            </span>
        </div>

        {{-- タイトル --}}
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 leading-tight mb-4">
            {{ $article->title }}
        </h1>

        {{-- リード文 --}}
        @if($article->lead)
        <p class="text-gray-600 text-base leading-relaxed border-l-4 border-gray-300 pl-4 mb-6">
            {{ $article->lead }}
        </p>
        @endif

        {{-- ヒーロー画像 --}}
        @if($article->hero_image)
        <div class="mb-8 rounded-xl overflow-hidden">
            <picture>
                <source srcset="{{ asset('storage/' . \App\Services\ImageService::webpPath($article->hero_image)) }}" type="image/webp">
                <img src="{{ asset('storage/' . $article->hero_image) }}"
                     alt="{{ $article->title }}"
                     class="w-full h-64 md:h-80 object-cover"
                     fetchpriority="high">
            </picture>
        </div>
        @endif

        {{-- 目次（JSで自動生成） --}}
        <div id="mokuji" class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-8 hidden">
            <p class="text-sm font-bold text-gray-700 mb-3">目次</p>
            <ol id="toc-list" class="space-y-1 text-sm text-gray-600"></ol>
        </div>

        {{-- 本文 --}}
        <div id="article-body" class="text-base max-w-none mb-10">
            {!! $article->body !!}
        </div>

        {{-- タグ --}}
        @if($article->tags->isNotEmpty())
        <div class="flex flex-wrap gap-2 mb-10 pt-6 border-t border-gray-100">
            @foreach($article->tags as $tag)
            <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">
                #{{ $tag->name }}
            </span>
            @endforeach
        </div>
        @endif

    </article>

    @include('partials._promo-banner', ['wrapClass' => 'my-10 rounded-2xl overflow-hidden shadow-md', 'innerClass' => ''])

    {{-- 関連求人 --}}
    @if($relatedJobs->isNotEmpty())
    <div class="mt-6 border-t border-gray-100 pt-8">
        <h2 class="text-sm font-bold text-gray-600 mb-3">
            {{ $genderLabel }}の求人を見る
        </h2>
        <div class="space-y-2">
            @foreach($relatedJobs as $rJob)
            <a href="{{ url('/track/job/' . $rJob->id) . '/' }}"
               rel="nofollow"
               class="flex items-center justify-between p-3 rounded-xl border {{ $jobBorderColor }} transition group">
                <div class="min-w-0">
                    <p class="text-xs {{ $jobTextColor }} font-medium">
                        {{ $rJob->jobType?->name ?? '求人' }} &nbsp;·&nbsp; {{ $rJob->shop->area?->name ?? '' }}
                    </p>
                    <p class="text-sm font-bold text-gray-800 truncate">{{ $rJob->title }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $rJob->shop->name }}</p>
                </div>
                <span class="{{ $jobTextColor }} opacity-60 ml-3 shrink-0 group-hover:translate-x-0.5 transition-transform">›</span>
            </a>
            @endforeach
        </div>
        <div class="mt-4 text-center">
            <a href="{{ route('search', ['gender' => in_array($article->gender, ['female','male','business']) ? $article->gender : 'female']) }}"
               class="text-sm text-gray-500 hover:text-gray-700 underline">
                {{ $genderLabel }}の求人をもっと見る →
            </a>
        </div>
    </div>
    @endif

    {{-- 記事一覧へ戻る --}}
    <div class="mt-10 text-center">
        <a href="{{ route('article.index') }}" class="text-sm text-gray-400 hover:text-gray-600">
            ← コラム・ガイド一覧に戻る
        </a>
    </div>

</div>

@endsection

@push('scripts')
<script>
// h2/h3から目次を自動生成
(function () {
    const body    = document.getElementById('article-body');
    const mokuji  = document.getElementById('mokuji');
    const tocList = document.getElementById('toc-list');
    if (!body || !mokuji || !tocList) return;

    const headings = body.querySelectorAll('h2, h3');
    if (headings.length < 2) return;

    headings.forEach((h, i) => {
        const id = 'heading-' + i;
        h.id = id;
        const li = document.createElement('li');
        li.className = h.tagName === 'H3' ? 'pl-4 text-gray-500' : '';
        li.innerHTML = `<a href="#${id}" class="hover:underline">${h.textContent}</a>`;
        tocList.appendChild(li);
    });

    mokuji.classList.remove('hidden');
})();
</script>
@endpush
