@extends('layouts.app')
@section('title', $shop->name . ' への口コミ投稿')
@section('content')
<div class="max-w-xl mx-auto px-4 py-10">
    <div class="bg-surface-500 border border-surface-300 rounded-2xl p-8">
        <h1 class="text-xl font-bold text-[#F0ECE4] mb-1">口コミを投稿</h1>
        <p class="text-sm text-gold-400 mb-6">{{ $shop->name }}</p>

        @if($errors->any())
        <div class="mb-4 bg-deli-500/10 border border-deli-500/30 rounded p-3 text-sm text-deli-400">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
        @endif

        <form action="{{ route('review.store') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="shop_id" value="{{ $shop->id }}">

            <div>
                <label class="block text-sm text-[#B0AEAD] mb-2">総合評価 <span class="text-deli-400">*</span></label>
                <div class="flex gap-3">
                    @for($i = 5; $i >= 1; $i--)
                    <label class="flex flex-col items-center gap-1 cursor-pointer">
                        <input type="radio" name="rating" value="{{ $i }}" @checked(old('rating') == $i) required class="sr-only peer">
                        <span class="text-2xl peer-checked:drop-shadow-lg transition select-none" style="filter:grayscale(1)" onmouseover="this.style.filter='none'" onmouseout="this.style.filter='grayscale(1)'">★</span>
                        <span class="text-xs text-[#6A6A7E]">{{ $i }}</span>
                    </label>
                    @endfor
                </div>
            </div>

            @if($casts->isNotEmpty())
            <div>
                <label class="block text-sm text-[#B0AEAD] mb-1">担当キャスト（任意）</label>
                <select name="cast_id" class="w-full bg-surface-400 border border-surface-300 rounded-lg px-4 py-2.5 text-[#E8E4DC] text-sm focus:outline-none focus:border-deli-500">
                    <option value="">選択しない</option>
                    @foreach($casts as $cast)
                    <option value="{{ $cast->id }}" @selected(old('cast_id') == $cast->id)>{{ $cast->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-sm text-[#B0AEAD] mb-1">タイトル（任意）</label>
                <input type="text" name="title" value="{{ old('title') }}" maxlength="100"
                       placeholder="例：最高のひととき"
                       class="w-full bg-surface-400 border border-surface-300 rounded-lg px-4 py-2.5 text-[#E8E4DC] text-sm focus:outline-none focus:border-deli-500">
            </div>

            <div>
                <label class="block text-sm text-[#B0AEAD] mb-1">口コミ本文 <span class="text-deli-400">*</span> <span class="text-xs text-[#6A6A7E]">（20文字以上）</span></label>
                <textarea name="body" rows="6" required minlength="20" maxlength="2000"
                          placeholder="サービスの内容、スタッフの対応、雰囲気など率直な感想をお聞かせください。"
                          class="w-full bg-surface-400 border border-surface-300 rounded-lg px-4 py-2.5 text-[#E8E4DC] text-sm focus:outline-none focus:border-deli-500 resize-none">{{ old('body') }}</textarea>
            </div>

            <p class="text-xs text-[#6A6A7E]">※投稿内容は店舗による承認後に公開されます。不適切な投稿は非承認になる場合があります。</p>

            <button type="submit"
                    class="w-full bg-deli-500 hover:bg-deli-400 text-white font-bold py-3 rounded-lg transition">
                口コミを投稿する
            </button>
        </form>
    </div>
</div>
@endsection
