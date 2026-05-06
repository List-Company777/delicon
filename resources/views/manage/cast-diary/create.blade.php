@extends('layouts.manage')
@section('title', '日記を投稿')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-[#F0ECE4]">{{ $cast->name }} - 日記を投稿</h1>
        <a href="{{ route('cast-diary.index', $cast->id) }}/" class="text-xs text-[#6A6A7E] hover:text-deli-400">← 一覧に戻る</a>
    </div>

    <form method="POST" action="{{ route('cast-diary.store', $cast->id) }}/" enctype="multipart/form-data">
        @csrf
        <div class="bg-surface-500 border border-surface-300 rounded-xl p-6 space-y-5">

            <div>
                <label class="text-xs text-[#C8C4BC] block mb-1">タイトル（任意）</label>
                <input type="text" name="title" maxlength="100" value="{{ old('title') }}"
                       class="w-full bg-surface-600 border border-surface-300 rounded-lg px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:border-deli-500">
            </div>

            <div>
                <label class="text-xs text-[#C8C4BC] block mb-1">本文</label>
                <textarea name="body" rows="6" maxlength="2000"
                       class="w-full bg-surface-600 border border-surface-300 rounded-lg px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:border-deli-500 resize-none">{{ old('body') }}</textarea>
                @error('body')<p class="text-xs text-deli-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-xs text-[#C8C4BC] block mb-1">画像（最大8枚）</label>
                <input type="file" name="images[]" multiple accept="image/*"
                       class="w-full text-sm text-[#C8C4BC] file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-deli-500 file:text-white hover:file:bg-deli-400">
                @error('images.*')<p class="text-xs text-deli-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-xs text-[#C8C4BC] block mb-1">公開設定</label>
                <select name="status" class="bg-surface-600 border border-surface-300 rounded-lg px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:border-deli-500">
                    <option value="published" @selected(old('status','published')==='published')>公開</option>
                    <option value="draft" @selected(old('status')==='draft')>下書き</option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex justify-end gap-3">
            <a href="{{ route('cast-diary.index', $cast->id) }}/" class="text-sm text-[#6A6A7E] hover:text-[#C8C4BC] px-4 py-2">キャンセル</a>
            <button type="submit" class="bg-deli-500 hover:bg-deli-400 text-white text-sm font-bold px-6 py-2 rounded-lg transition">投稿する</button>
        </div>
    </form>
</div>
@endsection
