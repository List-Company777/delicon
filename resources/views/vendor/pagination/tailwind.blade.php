@if ($paginator->hasPages())
<nav role="navigation" aria-label="ページナビゲーション" class="flex flex-col sm:flex-row items-center justify-between gap-3 text-sm">

    {{-- 件数表示 --}}
    <p class="text-gray-500 text-xs">
        全 <span class="font-medium text-gray-700">{{ number_format($paginator->total()) }}</span> 件中
        <span class="font-medium text-gray-700">{{ number_format($paginator->firstItem()) }}</span>〜<span class="font-medium text-gray-700">{{ number_format($paginator->lastItem()) }}</span> 件を表示
    </p>

    {{-- ページリンク --}}
    <div class="flex items-center gap-1">

        {{-- 前へ --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-200 text-gray-300 cursor-not-allowed text-xs select-none">
                ← 前へ
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 transition text-xs">
                ← 前へ
            </a>
        @endif

        {{-- ページ番号 --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 py-1.5 text-gray-400 text-xs select-none">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"
                              class="inline-flex items-center justify-center w-8 h-8 rounded-lg {{ $currentPageClass ?? 'bg-business-700' }} text-white font-bold text-xs cursor-default">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition text-xs"
                           aria-label="{{ $page }}ページ目">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- 次へ --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 transition text-xs">
                次へ →
            </a>
        @else
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-200 text-gray-300 cursor-not-allowed text-xs select-none">
                次へ →
            </span>
        @endif

    </div>
</nav>
@endif
