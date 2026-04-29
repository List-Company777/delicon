@extends('layouts.admin')
@section('title', 'コラム・記事管理')

@php
    // フラッシュメッセージ・URLパラメータに応じて初期タブを切り替え
    $initTab = $articleTab === 'draft' ? 'articles_draft' : 'articles_published';
    if (session('topic_success') || session('topic_error')) $initTab = 'topics';
    if (session('prompt_success')) $initTab = 'prompts';

    $genderBadge = fn($g) => match($g) {
        'female'   => 'bg-pink-100 text-pink-700',
        'male'     => 'bg-blue-100 text-blue-700',
        'yoasobi' => 'bg-purple-100 text-purple-700',
        'shop'     => 'bg-green-100 text-green-700',
        default    => 'bg-gray-100 text-gray-600',
    };
@endphp

@section('content')

<div x-data="{ tab: '{{ $initTab }}' }">

    {{-- ヘッダー --}}
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold text-gray-700">コラム・記事管理</h1>
        <a x-show="tab === 'articles_published' || tab === 'articles_draft'"
           href="{{ route('admin.articles.create') }}"
           class="bg-gray-800 hover:bg-gray-700 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
            ＋ 新規作成
        </a>
    </div>

    {{-- タブバー --}}
    <div class="flex border-b border-gray-200 mb-6">
        <button @click="tab='articles_published'"
                :class="tab==='articles_published' ? 'border-b-2 border-gray-800 text-gray-800' : 'text-gray-400 hover:text-gray-600'"
                class="px-5 py-2.5 text-sm font-medium -mb-px transition">
            公開・予約
            <span class="ml-1 text-xs text-gray-400">{{ $publishedArticles->total() }}</span>
        </button>
        <button @click="tab='articles_draft'"
                :class="tab==='articles_draft' ? 'border-b-2 border-gray-800 text-gray-800' : 'text-gray-400 hover:text-gray-600'"
                class="px-5 py-2.5 text-sm font-medium -mb-px transition">
            下書き
            @if($draftArticles->total() > 0)
            <span class="ml-1 text-xs text-gray-400">{{ $draftArticles->total() }}</span>
            @endif
        </button>
        <button @click="tab='topics'"
                :class="tab==='topics' ? 'border-b-2 border-gray-800 text-gray-800' : 'text-gray-400 hover:text-gray-600'"
                class="px-5 py-2.5 text-sm font-medium -mb-px transition">
            テーマ管理
            @if($pendingTopics->count() > 0)
            <span class="ml-1 inline-flex items-center justify-center w-4 h-4 text-xs bg-amber-500 text-white rounded-full">
                {{ $pendingTopics->count() }}
            </span>
            @endif
        </button>
        <button @click="tab='prompts'"
                :class="tab==='prompts' ? 'border-b-2 border-gray-800 text-gray-800' : 'text-gray-400 hover:text-gray-600'"
                class="px-5 py-2.5 text-sm font-medium -mb-px transition">
            プロンプト設定
        </button>
    </div>

    {{-- ========== 公開・予約タブ ========== --}}
    <div x-show="tab === 'articles_published'" x-transition>

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-500 font-normal w-56">タイトル</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-normal w-16">対象</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-normal w-20">状態</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-normal w-32">公開日</th>
                        <th class="text-center px-4 py-3 text-gray-500 font-normal w-32">動画</th>
                        <th class="px-4 py-3 w-32"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($publishedArticles as $article)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 max-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $article->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">/article/{{ $article->slug }}/</p>
                            @if($article->categories->isNotEmpty())
                            <div class="flex gap-1 mt-1">
                                @foreach($article->categories as $cat)
                                <span class="text-xs px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded">{{ $cat->name }}</span>
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ match($article->gender) { 'female' => '女性', 'male' => '男性', 'yoasobi' => '夜遊び', 'shop' => '店舗運営者', default => '全般' } }}
                        </td>
                        <td class="px-4 py-3">
                            @if($article->isVisible())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">公開中</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-700">公開予約</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ $article->published_at?->format('Y/m/d H:i') ?? '―' }}
                        </td>
                        <td class="px-4 py-3 text-center"
                            x-data="articleVideo({{ $article->id }}, '{{ $article->video?->status ?? 'none' }}', '{{ route('admin.articles.video.download', $article) }}')"
                            data-caption='@json($article->video?->sns_caption ?? "")'
                            x-init="init()">
                            <template x-if="status === 'done'">
                                <div class="flex flex-col gap-1 items-center">
                                    <a :href="downloadUrl"
                                       class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-200 px-2 py-1 rounded whitespace-nowrap">
                                        ↓ DL
                                    </a>
                                    <button @click="showCaption = true" :disabled="!caption"
                                            class="text-xs text-emerald-600 hover:text-emerald-800 border border-emerald-200 px-2 py-1 rounded disabled:opacity-30 whitespace-nowrap">
                                        投稿文
                                    </button>
                                    <button @click="generate()" :disabled="loading"
                                            class="text-xs text-gray-400 hover:text-gray-600 border border-gray-200 px-2 py-1 rounded disabled:opacity-50 whitespace-nowrap">
                                        再生成
                                    </button>
                                    <button @click="destroy()" :disabled="loading"
                                            class="text-xs text-red-400 hover:text-red-600 border border-red-200 px-2 py-1 rounded disabled:opacity-50 whitespace-nowrap">
                                        削除
                                    </button>
                                    {{-- 投稿文モーダル --}}
                                    <div x-show="showCaption" x-cloak
                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                                         @click.self="showCaption = false">
                                        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6">
                                            <div class="flex items-center justify-between mb-3">
                                                <h3 class="text-sm font-semibold text-gray-700">SNS投稿文</h3>
                                                <button @click="showCaption = false" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                                            </div>
                                            <textarea class="w-full text-sm text-gray-800 border border-gray-200 rounded-lg p-3 resize-none focus:outline-none focus:ring-2 focus:ring-emerald-300"
                                                      rows="6" x-model="caption" readonly></textarea>
                                            <div class="mt-3 flex justify-end gap-2">
                                                <button @click="copyCaption()"
                                                        class="text-sm bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg flex items-center gap-1">
                                                    <span x-text="copied ? 'コピーしました！' : 'コピー'"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template x-if="status === 'pending' || status === 'processing'">
                                <span class="text-xs text-amber-600 animate-pulse">生成中…</span>
                            </template>
                            <template x-if="status === 'failed'">
                                <button @click="generate()"
                                        class="text-xs text-red-400 hover:text-red-600 border border-red-200 px-2 py-1 rounded">
                                    失敗
                                </button>
                            </template>
                            <template x-if="status === 'none'">
                                <button @click="generate()" :disabled="loading"
                                        class="text-xs text-gray-400 hover:text-gray-600 border border-gray-200 px-2 py-1 rounded disabled:opacity-50">
                                    <span x-show="!loading">生成</span>
                                    <span x-show="loading" x-cloak>…</span>
                                </button>
                            </template>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2 justify-end">
                                <a href="{{ route('admin.articles.preview', $article) }}"
                                   target="_blank"
                                   class="text-xs text-gray-400 hover:text-gray-600 border border-gray-200 px-2 py-1 rounded">
                                    プレビュー
                                </a>
                                <a href="{{ route('admin.articles.edit', $article) }}"
                                   class="text-xs text-blue-600 hover:text-blue-800 border border-blue-200 px-2 py-1 rounded">
                                    編集
                                </a>
                                <form action="{{ route('admin.articles.destroy', $article) }}" method="POST"
                                      onsubmit="return confirm('削除しますか？')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-400 hover:text-red-600 border border-red-200 px-2 py-1 rounded">
                                        削除
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">公開・予約中の記事はありません</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $publishedArticles->links() }}</div>

    </div>{{-- /公開・予約 --}}

    {{-- ========== 下書きタブ ========== --}}
    <div x-show="tab === 'articles_draft'" x-transition>

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-500 font-normal w-56">タイトル</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-normal w-16">対象</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-normal w-32">最終更新</th>
                        <th class="px-4 py-3 w-32"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($draftArticles as $article)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 max-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $article->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">/article/{{ $article->slug }}/</p>
                            @if($article->categories->isNotEmpty())
                            <div class="flex gap-1 mt-1">
                                @foreach($article->categories as $cat)
                                <span class="text-xs px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded">{{ $cat->name }}</span>
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ match($article->gender) { 'female' => '女性', 'male' => '男性', 'yoasobi' => '夜遊び', 'shop' => '店舗運営者', default => '全般' } }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ $article->updated_at->format('Y/m/d H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2 justify-end">
                                <a href="{{ route('admin.articles.preview', $article) }}"
                                   target="_blank"
                                   class="text-xs text-gray-400 hover:text-gray-600 border border-gray-200 px-2 py-1 rounded">
                                    プレビュー
                                </a>
                                <a href="{{ route('admin.articles.edit', $article) }}"
                                   class="text-xs text-blue-600 hover:text-blue-800 border border-blue-200 px-2 py-1 rounded">
                                    編集
                                </a>
                                <form action="{{ route('admin.articles.destroy', $article) }}" method="POST"
                                      onsubmit="return confirm('削除しますか？')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-400 hover:text-red-600 border border-red-200 px-2 py-1 rounded">
                                        削除
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-gray-400">下書きの記事はありません</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $draftArticles->links() }}</div>

    </div>{{-- /下書き --}}

    {{-- ========== テーマ管理タブ ========== --}}
    <div x-show="tab === 'topics'" x-transition>

        @if(session('topic_success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-2 rounded-lg mb-4">
            {{ session('topic_success') }}
        </div>
        @endif
        @if(session('topic_error'))
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2 rounded-lg mb-4">
            {{ session('topic_error') }}
        </div>
        @endif

        {{-- 審査待ち --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="flex items-center justify-between px-4 py-3 bg-amber-50 border-b border-amber-100">
                <p class="text-sm font-bold text-amber-800">
                    審査待ち
                    <span class="ml-1 text-xs font-normal text-amber-600">（{{ $pendingTopics->count() }}件）</span>
                </p>
                <form action="{{ route('admin.article-topics.suggest') }}" method="POST"
                      x-data="{ loading: false }" @submit="loading = true">
                    @csrf
                    <button type="submit" :disabled="loading"
                            class="flex items-center gap-1.5 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-600 disabled:cursor-not-allowed text-gray-900 text-xs font-bold px-3 py-1.5 rounded-lg transition">
                        <svg x-show="!loading" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <svg x-show="loading" x-cloak class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <span x-show="!loading">AIに20件提案させる</span>
                        <span x-show="loading" x-cloak>提案中（20〜30秒）...</span>
                    </button>
                </form>
            </div>

            @forelse($pendingTopics as $topic)
            <div class="flex items-start gap-3 px-4 py-3 border-b border-gray-100 hover:bg-gray-50">
                <div class="flex items-center gap-1.5 shrink-0 mt-0.5">
                    <span class="text-xs px-1.5 py-0.5 rounded {{ $genderBadge($topic->gender) }}">{{ $topic->gender_label }}</span>
                    @if($topic->source === 'ai')
                    <span class="text-xs px-1.5 py-0.5 rounded bg-amber-100 text-amber-700">AI</span>
                    @else
                    <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">手動</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800">{{ $topic->title }}</p>
                    @if($topic->ai_reason)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $topic->ai_reason }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <form action="{{ route('admin.article-topics.approve', $topic) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="text-xs text-green-600 hover:text-green-800 border border-green-200 hover:border-green-400 px-2 py-1 rounded transition">
                            承認
                        </button>
                    </form>
                    <form action="{{ route('admin.article-topics.destroy', $topic) }}" method="POST"
                          onsubmit="return confirm('却下して削除しますか？')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-gray-400 hover:text-red-500 border border-gray-200 hover:border-red-200 px-2 py-1 rounded transition">
                            却下
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <p class="px-4 py-6 text-center text-sm text-gray-400">審査待ちのテーマはありません。</p>
            @endforelse

            <div class="px-4 py-4 bg-gray-50 border-t border-gray-100">
                <p class="text-xs font-medium text-gray-500 mb-2">手動でテーマを追加（審査待ちへ）</p>
                <form action="{{ route('admin.article-topics.store') }}" method="POST" class="flex gap-2">
                    @csrf
                    <input type="text" name="title" required placeholder="記事テーマタイトル"
                           class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-yellow-400">
                    <select name="gender" class="border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:border-yellow-400">
                        <option value="female">女性</option>
                        <option value="male">男性</option>
                        <option value="business">夜遊び</option>
                        <option value="shop">店舗</option>
                    </select>
                    <button type="submit"
                            class="bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                        追加
                    </button>
                </form>
            </div>
        </div>

        {{-- 作成予定 --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-blue-50 border-b border-blue-100">
                <p class="text-sm font-bold text-blue-800">
                    作成予定
                    <span class="ml-1 text-xs font-normal text-blue-600">（{{ $approvedTopics->count() }}件 ／ 次回Cronで最大5件生成）</span>
                </p>
            </div>
            @forelse($approvedTopics as $topic)
            <div class="flex items-center gap-3 px-4 py-2.5 border-b border-gray-100 hover:bg-gray-50">
                <span class="text-xs px-1.5 py-0.5 rounded {{ $genderBadge($topic->gender) }} shrink-0">{{ $topic->gender_label }}</span>
                <p class="flex-1 text-sm text-gray-800 truncate">{{ $topic->title }}</p>
                <form action="{{ route('admin.article-topics.destroy', $topic) }}" method="POST"
                      onsubmit="return confirm('作成予定から削除しますか？')" class="shrink-0">
                    @csrf @method('DELETE')
                    <button class="text-xs text-gray-300 hover:text-red-500 transition">✕</button>
                </form>
            </div>
            @empty
            <p class="px-4 py-6 text-center text-sm text-gray-400">
                作成予定のテーマはありません。審査待ちのテーマを承認してください。
            </p>
            @endforelse
        </div>

    </div>{{-- /テーマ管理 --}}

    {{-- ========== プロンプト設定タブ ========== --}}
    <div x-show="tab === 'prompts'" x-transition>

        @if(session('prompt_success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-2 rounded-lg mb-4">
            {{ session('prompt_success') }}
        </div>
        @endif

        <p class="text-xs text-gray-500 mb-4">
            各対象の「対象読者」指示文を設定します。記事自動生成・AIテーマ提案のプロンプトに差し込まれます。
        </p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($prompts as $prompt)
            @php
                $badgeColor = match($prompt->gender) {
                    'female'   => 'bg-pink-100 text-pink-700',
                    'male'     => 'bg-blue-100 text-blue-700',
                    'yoasobi' => 'bg-purple-100 text-purple-700',
                    'shop'     => 'bg-green-100 text-green-700',
                    default    => 'bg-gray-100 text-gray-600',
                };
            @endphp
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $badgeColor }}">{{ $prompt->gender_label }}</span>
                </div>
                <form action="{{ route('admin.article-generation-prompts.update', $prompt->gender) }}" method="POST">
                    @csrf @method('PUT')
                    <textarea name="instruction" rows="4" maxlength="1000"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-yellow-400 resize-none"
                              placeholder="対象読者の説明を入力...">{{ old('instruction', $prompt->instruction) }}</textarea>
                    <div class="flex justify-end mt-2">
                        <button type="submit"
                                class="bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold px-4 py-1.5 rounded-lg transition">
                            保存
                        </button>
                    </div>
                </form>
            </div>
            @endforeach
        </div>

    </div>{{-- /プロンプト設定 --}}

</div>{{-- /x-data --}}

@push('scripts')
<script @nonce>
function articleVideo(articleId, initialStatus, downloadUrl) {
    return {
        articleId,
        status: initialStatus,
        downloadUrl,
        caption: '',
        showCaption: false,
        copied: false,
        loading: false,
        pollTimer: null,

        init() {
            this.caption = JSON.parse(this.$el.dataset.caption || '""');
            if (this.status === 'pending' || this.status === 'processing') {
                this.poll();
            }
        },

        async generate() {
            this.loading = true;
            try {
                const res = await fetch(`/admin/articles/${this.articleId}/video/`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                this.status = data.status;
                if (this.status === 'pending' || this.status === 'processing') {
                    this.poll();
                }
            } finally {
                this.loading = false;
            }
        },

        poll() {
            this.pollTimer = setInterval(async () => {
                const res = await fetch(`/admin/articles/${this.articleId}/video/status/`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.status = data.status;
                if (data.sns_caption) this.caption = data.sns_caption;
                if (this.status !== 'pending' && this.status !== 'processing') {
                    clearInterval(this.pollTimer);
                }
            }, 10000);
        },

        async destroy() {
            if (!confirm('動画ファイルを削除しますか？')) return;
            this.loading = true;
            try {
                await fetch(`/admin/articles/${this.articleId}/video/`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });
                this.status = 'none';
            } finally {
                this.loading = false;
            }
        },

        async copyCaption() {
            await navigator.clipboard.writeText(this.caption);
            this.copied = true;
            setTimeout(() => { this.copied = false; }, 2000);
        },
    };
}
</script>
@endpush

@endsection
