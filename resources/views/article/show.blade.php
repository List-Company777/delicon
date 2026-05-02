@php
    $genderLabel = match($article->gender) {
        'female'   => '女性ナイトワーク',
        'male'     => '男性ナイトワーク',
        'yoasobi' => '夜遊び',
        'shop'     => '店舗運営',
        default    => 'ナイトワーク',
    };
    $genderColor = match($article->gender) {
        'female'   => 'text-female-600',
        'male'     => 'text-male-600',
        'yoasobi'  => 'text-business-700',
        'shop'     => 'text-green-700',
        default    => 'text-gray-600',
    };
    $jobBorderColor = match($article->gender) {
        'male'     => 'border-male-200 bg-male-50 hover:bg-male-100',
        'yoasobi'  => 'border-business-200 bg-business-50 hover:bg-business-100',
        default    => 'border-female-100 bg-female-50 hover:bg-female-100',
    };
    $jobTextColor = match($article->gender) {
        'male'     => 'text-male-600',
        'yoasobi'  => 'text-business-700',
        default    => 'text-female-600',
    };
@endphp

@extends('layouts.app')

@section('canonical', route('article.show', $article->slug) . '/')
@section('og_type', 'article')
@section('title', $article->title)
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
    $articleUrl = route('article.show', $article->slug) . '/';
    $ld = [
        '@context'         => 'https://schema.org',
        '@type'            => 'Article',
        '@id'              => $articleUrl . '#article',
        'url'              => $articleUrl,
        'headline'         => $article->title,
        'description'      => $article->lead ?? '',
        'datePublished'    => $article->published_at?->toIso8601String(),
        'dateModified'     => ($article->updated_at_manual ?? $article->updated_at)?->toIso8601String(),
        'inLanguage'       => 'ja',
        'mainEntityOfPage' => ['@id' => $articleUrl . '#webpage'],
        'author'           => ['@type' => 'Organization', '@id' => url('/') . '#org', 'name' => 'ナイトワークリスト編集部'],
        'publisher'        => ['@id' => url('/') . '#org'],
        'isPartOf'         => ['@id' => url('/') . '#website'],
    ];
    if ($article->hero_image) {
        $ld['image'] = [
            '@type'  => 'ImageObject',
            'url'    => asset('storage/' . $article->hero_image),
            'width'  => 1200,
            'height' => 630,
        ];
    }

    $breadcrumb = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        '@id'      => $articleUrl . '#breadcrumb',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'ナイトワーク', 'item' => route('top') . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'コラム・ガイド', 'item' => route('article.index') . '/'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $article->title, 'item' => $articleUrl],
        ],
    ];

    $ldPage = [
        '@context'   => 'https://schema.org',
        '@type'      => 'WebPage',
        '@id'        => $articleUrl . '#webpage',
        'url'        => $articleUrl,
        'name'       => $article->title . ' | ナイトワークリスト',
        'inLanguage' => 'ja',
        'isPartOf'   => ['@id' => url('/') . '#website'],
        'about'      => ['@id' => url('/') . '#org'],
        'publisher'  => ['@id' => url('/') . '#org'],
        'breadcrumb' => ['@id' => $articleUrl . '#breadcrumb'],
    ];
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($ldPage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
<script type="application/ld+json" @nonce>{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
<script type="application/ld+json" @nonce>{!! json_encode($breadcrumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@if(!empty($article->faq))
@php
    $faqLd = [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => collect($article->faq)->map(fn($item) => [
            '@type'          => 'Question',
            'name'           => $item['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
        ])->values()->all(),
    ];
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($faqLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endif
@if($article->video?->isDone())
@php
    $videoLd = [
        '@context'     => 'https://schema.org',
        '@type'        => 'VideoObject',
        'name'         => $article->title,
        'description'  => $article->lead ?? '',
        'uploadDate'   => ($article->video->created_at)->toIso8601String(),
        'contentUrl'   => asset('storage/' . $article->video->video_path),
        'thumbnailUrl' => $article->hero_image ? asset('storage/' . $article->hero_image) : asset('android-chrome-192x192.png'),
        'publisher'    => ['@type' => 'Organization', 'name' => 'ナイトワークリスト'],
    ];
@endphp
<script type="application/ld+json" @nonce>{!! json_encode($videoLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endif
@endpush

@section('content')

<div class="max-w-3xl mx-auto px-4 py-8">

    {{-- パンくず --}}
    <nav class="text-xs text-gray-400 mb-6" aria-label="パンくずリスト">
        <a href="{{ route('top') }}/" class="hover:text-gray-600">ナイトワーク</a>
        <span class="mx-1" aria-hidden="true">›</span>
        <a href="{{ route('article.index') }}/" class="hover:text-gray-600">コラム・ガイド</a>
        <span class="mx-1" aria-hidden="true">›</span>
        <span class="text-gray-600" aria-current="page">{{ $article->title }}</span>
    </nav>

    <article>

        <header>

        {{-- カテゴリ・タグ・日付 --}}
        <div class="flex flex-wrap items-center gap-2 mb-3">
            @foreach($article->categories as $cat)
            <a href="{{ route('article.index') }}/?category={{ $cat->slug }}"
               class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full hover:bg-gray-200 transition">
                {{ $cat->name }}
            </a>
            @endforeach
            <span class="text-xs text-gray-400 ml-auto">
                <time datetime="{{ $article->published_at?->toDateString() }}">{{ $article->published_at?->format('Y年n月j日') }}</time>
                @if($article->updated_at_manual && $article->updated_at_manual->gt($article->published_at))
                <time class="ml-2" datetime="{{ $article->updated_at_manual->toDateString() }}">更新: {{ $article->updated_at_manual->format('Y年n月j日') }}</time>
                @endif
            </span>
        </div>

        {{-- タイトル --}}
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 leading-tight mb-4">
            {{ $article->title }}
        </h1>

        {{-- ヒーロー画像（タイトル直下に配置してLCP候補にする） --}}
        @if($article->hero_image)
        <picture class="block mb-6 rounded-xl overflow-hidden">
            <source srcset="{{ asset('storage/' . \App\Services\ImageService::webpPath($article->hero_image)) }}" type="image/webp">
            <img src="{{ asset('storage/' . $article->hero_image) }}"
                 alt="{{ $article->title }}"
                 class="w-full h-64 md:h-80 object-cover"
                 fetchpriority="high">
        </picture>
        @endif

        {{-- リード文 --}}
        @if($article->lead)
        <p class="text-gray-600 text-base leading-relaxed border-l-4 border-gray-300 pl-4 mb-6">
            {{ $article->lead }}
        </p>
        @endif

        </header>

        {{-- 目次（JSで自動生成） --}}
        <nav id="mokuji" class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-8 hidden" aria-label="目次">
            <p class="text-sm font-bold text-gray-700 mb-3" aria-hidden="true">目次</p>
            <ol id="toc-list" class="space-y-1 text-sm text-gray-600"></ol>
        </nav>

        {{-- 本文 --}}
        <div id="article-body" class="text-base max-w-none mb-10">
            {!! $article->body !!}
        </div>

        {{-- FAQ --}}
        @if(!empty($article->faq))
        <section class="mt-10 pt-8 border-t border-gray-100" id="faq" aria-label="よくある質問">
            <h2 class="text-xl font-bold text-gray-800 mb-5">よくある質問</h2>
            <dl class="space-y-4">
                @foreach($article->faq as $item)
                <div class="bg-gray-50 rounded-xl px-5 py-4">
                    <dt class="font-bold text-gray-800 text-sm flex items-start gap-2 mb-2">
                        <span class="shrink-0 font-black text-gray-400">Q.</span>
                        {{ $item['q'] }}
                    </dt>
                    <dd class="text-sm text-gray-600 leading-relaxed flex items-start gap-2">
                        <span class="shrink-0 font-bold text-gray-400">A.</span>
                        {{ $item['a'] }}
                    </dd>
                </div>
                @endforeach
            </dl>
        </section>
        @endif

        {{-- 動画 --}}
        @if($article->video?->isDone())
        <div class="mb-8 rounded-xl overflow-hidden bg-black">
            <video controls preload="metadata" class="w-full max-h-[480px]">
                <source src="{{ asset('storage/' . $article->video->video_path) }}" type="video/mp4">
            </video>
        </div>
        @endif

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
    <section class="mt-6 border-t border-gray-100 pt-8" aria-label="{{ $genderLabel }}の求人">
        <h2 class="text-sm font-bold text-gray-600 mb-3">{{ $genderLabel }}の求人を見る</h2>
        <div class="space-y-2">
            @foreach($relatedJobs as $rJob)
            <a href="{{ route('job.show', $rJob->id) . '/' }}"
               rel="nofollow"
               class="flex items-center justify-between p-3 rounded-xl border {{ $jobBorderColor }} transition group">
                <span class="min-w-0">
                    <b class="block text-sm font-bold text-gray-800 truncate">{{ $rJob->title }}</b>
                    <span class="block text-xs text-gray-500">{{ $rJob->jobType?->name ?? '求人' }} · {{ $rJob->shop->area?->name ?? '' }} · {{ $rJob->shop->name }}</span>
                </span>
                <span class="{{ $jobTextColor }} opacity-60 ml-3 shrink-0 group-hover:translate-x-0.5 transition-transform" aria-hidden="true">›</span>
            </a>
            @endforeach
        </div>
        <p class="mt-4 text-center">
            <a href="{{ route('search') }}/?gender={{ in_array($article->gender, ['female','male','yoasobi']) ? $article->gender : 'female' }}"
               class="text-sm text-gray-500 hover:text-gray-700 underline">
                {{ $genderLabel }}の求人をもっと見る →
            </a>
        </p>
    </section>
    @endif

    {{-- エリアLPクロスリンク --}}
    @if(!empty($lpLinks))
    <section class="mt-8 border-t border-gray-100 pt-8" aria-label="エリア別求人">
        <h2 class="text-sm font-bold text-gray-600 mb-3">エリア別求人を探す</h2>
        <nav class="flex flex-wrap gap-2" aria-label="エリア別リンク">
            @foreach($lpLinks as $link)
            <a href="{{ $link['url'] }}"
               class="text-xs px-3 py-1.5 rounded-full border border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100 transition">
                {{ $link['label'] }}
            </a>
            @endforeach
        </nav>
    </section>
    @endif

    {{-- 記事一覧へ戻る --}}
    <p class="mt-10 text-center">
        <a href="{{ route('article.index') }}/" class="text-sm text-gray-400 hover:text-gray-600">
            ← コラム・ガイド一覧に戻る
        </a>
    </p>

</div>

@endsection

@push('scripts')
<script @nonce>
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
