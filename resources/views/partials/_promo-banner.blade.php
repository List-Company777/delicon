{{-- 店舗掲載プロモーションバナー --}}
<div class="{{ $wrapClass ?? '' }}" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);">
    <div class="px-6 py-7 md:px-10 md:py-8 flex flex-col md:flex-row items-center gap-5 {{ $innerClass ?? 'max-w-6xl mx-auto' }}">
        <div class="flex-1 text-center md:text-left">
            <p class="text-yellow-400 text-xs font-bold tracking-widest uppercase mb-1">For Shop Owners</p>
            <p class="text-white text-xl md:text-2xl font-bold leading-snug mb-2">
                <span class="text-yellow-300">月間利用者急増中！</span>お店の掲載、完全無料で。
            </p>
            <p class="text-gray-300 text-sm leading-relaxed">
                ナイトワークリストへの基本掲載は<strong class="text-white">完全無料</strong>。<br>
                審査通過後、最短即日で求職者・夜遊びユーザーにリーチできます。
            </p>
            <div class="flex flex-wrap gap-3 mt-3 justify-center md:justify-start">
                <span class="text-xs text-yellow-200 bg-yellow-900/40 px-2.5 py-1 rounded-full">✓ 掲載費0円</span>
                <span class="text-xs text-yellow-200 bg-yellow-900/40 px-2.5 py-1 rounded-full">✓ 最短即日公開</span>
                <span class="text-xs text-yellow-200 bg-yellow-900/40 px-2.5 py-1 rounded-full">✓ 応募管理機能付き</span>
            </div>
        </div>
        <div class="shrink-0">
            <a href="{{ route('register') }}/"
               class="inline-block bg-yellow-400 hover:bg-yellow-300 text-gray-900 font-bold text-sm px-7 py-3 rounded-xl shadow-lg transition whitespace-nowrap">
                無料で掲載する →
            </a>
        </div>
    </div>
</div>
