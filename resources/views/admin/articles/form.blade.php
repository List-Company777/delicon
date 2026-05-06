@extends('layouts.admin')
@section('title', $article->exists ? '記事編集' : '記事作成')
@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-700">
        {{ $article->exists ? '記事編集' : '記事作成' }}
    </h1>
    <div class="flex gap-3">
        @if($article->exists)
        <a href="{{ route('admin.articles.preview', $article) }}/" target="_blank"
           class="text-sm text-gray-400 hover:text-gray-600 border border-gray-200 px-3 py-1.5 rounded-lg">
            プレビュー
        </a>
        @endif
        <a href="{{ route('admin.articles.index') }}/" class="text-sm text-gray-400 hover:text-gray-600">← 一覧に戻る</a>
    </div>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
    <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<form action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}"
      method="POST" class="space-y-4">
    @csrf
    @if($article->exists) @method('PUT') @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">

            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-36 whitespace-nowrap">
                    タイトル <span class="text-red-400">*</span>
                </th>
                <td class="px-4 py-3">
                    <input type="text" name="title" value="{{ old('title', $article->title) }}" required
                           class="w-full border border-gray-300 rounded px-3 py-1.5 focus:outline-none focus:border-yellow-400 @error('title') border-red-400 @enderror">
                    @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </td>
            </tr>

            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">
                    スラッグ <span class="text-red-400">*</span>
                </th>
                <td class="px-4 py-3">
                    <input type="text" name="slug" value="{{ old('slug', $article->slug) }}" required
                           placeholder="例: nightwork-how-to-choose"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 font-mono text-xs focus:outline-none focus:border-yellow-400 @error('slug') border-red-400 @enderror">
                    <p class="text-xs text-gray-400 mt-1">半角英数字・ハイフン・アンダースコアのみ。/article/{スラッグ}/ となります</p>
                    @error('slug')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </td>
            </tr>

            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">対象</th>
                <td class="px-4 py-3">
                    <div class="flex gap-6">
                        @foreach(['shop' => '店舗運営者', 'female' => '女性ナイトワーク', 'male' => '男性ナイトワーク', 'yoasobi' => '夜遊び'] as $val => $label)
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="gender" value="{{ $val }}"
                                   {{ old('gender', $article->gender ?? 'shop') === $val ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </td>
            </tr>

            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">カテゴリ</th>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-3">
                        @foreach($categories as $cat)
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}"
                                   {{ in_array($cat->id, old('category_ids', $article->categories->pluck('id')->toArray())) ? 'checked' : '' }}>
                            <span>{{ $cat->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </td>
            </tr>

            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">タグ</th>
                <td class="px-4 py-3">
                    <input type="text" name="tag_names"
                           value="{{ old('tag_names', $article->tags->pluck('name')->implode(', ')) }}"
                           placeholder="例: 新宿, キャバクラ, 時給相場"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 focus:outline-none focus:border-yellow-400">
                    <p class="text-xs text-gray-400 mt-1">カンマ区切りで入力。新しいタグは自動作成されます</p>
                </td>
            </tr>

            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">リード文</th>
                <td class="px-4 py-3">
                    <textarea name="lead" rows="3"
                              placeholder="記事の要約・導入文（500文字以内）"
                              class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-yellow-400">{{ old('lead', $article->lead) }}</textarea>
                    @error('lead')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </td>
            </tr>

            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">
                    本文 <span class="text-red-400">*</span>
                </th>
                <td class="px-4 py-3">
                    <textarea name="body" rows="30"
                              placeholder="HTML形式で入力。h2/h3タグから目次が自動生成されます。"
                              class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono focus:outline-none focus:border-yellow-400">{{ old('body', $article->body) }}</textarea>
                    <p class="text-xs text-gray-400 mt-1">HTMLタグ使用可。&lt;h2&gt;&lt;h3&gt;&lt;p&gt;&lt;ul&gt;&lt;ol&gt;等</p>
                    @error('body')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </td>
            </tr>

            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">ヒーロー画像</th>
                <td class="px-4 py-3">
                    <input type="text" name="hero_image" value="{{ old('hero_image', $article->hero_image) }}"
                           placeholder="storage/ 以降のパス"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 font-mono text-xs focus:outline-none focus:border-yellow-400">
                    @if($article->hero_image)
                    <img src="{{ asset('storage/' . $article->hero_image) }}" alt="" class="mt-2 h-24 rounded object-cover">
                    @endif
                </td>
            </tr>

            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">公開日時</th>
                <td class="px-4 py-3">
                    <input type="datetime-local" name="published_at"
                           value="{{ old('published_at', $article->published_at?->format('Y-m-d\TH:i')) }}"
                           class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-yellow-400">
                    <p class="text-xs text-gray-400 mt-1">空欄の場合、公開時に現在時刻が設定されます</p>
                </td>
            </tr>

            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">更新日（手動）</th>
                <td class="px-4 py-3">
                    <input type="date" name="updated_at_manual"
                           value="{{ old('updated_at_manual', $article->updated_at_manual?->format('Y-m-d')) }}"
                           class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-yellow-400">
                    <p class="text-xs text-gray-400 mt-1">記事詳細ページに表示する更新日。空欄なら表示しません</p>
                </td>
            </tr>

            <tr>
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">公開状態</th>
                <td class="px-4 py-3">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_published" value="0">
                        <input type="checkbox" name="is_published" value="1"
                               {{ old('is_published', $article->is_published) ? 'checked' : '' }}
                               class="w-4 h-4">
                        <span>公開する</span>
                    </label>
                    <p class="text-xs text-gray-400 mt-1">チェックを外すと下書きになります（noindex）</p>
                </td>
            </tr>

        </table>

        <div class="px-4 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button type="submit"
                    class="bg-gray-800 hover:bg-gray-700 text-white text-sm font-bold px-6 py-2 rounded-lg transition">
                {{ $article->exists ? '更新する' : '作成する' }}
            </button>
        </div>
    </div>

</form>

@endsection
