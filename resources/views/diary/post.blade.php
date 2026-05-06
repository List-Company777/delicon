@extends('layouts.app')
@section('title', $cast->name . ' - 写メ日記投稿')
@section('robots', 'noindex')

@section('content')
<div class="min-h-screen flex items-start justify-center px-4 py-8">
    <div class="w-full max-w-lg">

        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-full bg-surface-400 border border-surface-300 overflow-hidden mx-auto mb-3">
                <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                     class="img-onerror-cast w-full h-full object-cover">
            </div>
            <p class="text-[#F0ECE4] font-bold text-lg">{{ $cast->name }}</p>
            <p class="text-xs text-[#6A6A7E] mt-0.5">写メ日記を投稿</p>
            <a href="{{ route('cast.show', $cast) }}/"
               class="inline-block mt-2 text-xs text-deli-400 hover:text-deli-300 underline underline-offset-2 transition">
                自分のページを確認する →
            </a>
        </div>

        @if(session('success'))
        <div class="mb-5 bg-emerald-900/40 border border-emerald-600/40 text-emerald-400 text-sm px-4 py-3 rounded-xl text-center font-medium">
            {{ session('success') }}
        </div>
        @endif

        @if($postedToday)
        <div class="mb-5 bg-amber-900/40 border border-amber-600/40 text-amber-300 text-sm px-4 py-3 rounded-xl text-center font-medium">
            本日はすでに投稿済みです。写メ日記は1日1件までです。
        </div>
        @endif

        @error('limit')
        <div class="mb-5 bg-amber-900/40 border border-amber-600/40 text-amber-300 text-sm px-4 py-3 rounded-xl text-center font-medium">
            {{ $message }}
        </div>
        @enderror

        <form method="POST" action="/diary/post/{{ $dt->token }}/" enctype="multipart/form-data"
              class="bg-surface-500 border border-surface-300 rounded-2xl p-6 space-y-5 {{ $postedToday ? 'opacity-50 pointer-events-none' : '' }}">
            @csrf

            {{-- 写真（最大5枚） --}}
            <div>
                <label class="text-sm font-medium text-[#E8E4DC] block mb-2">写真（最大5枚）</label>

                {{-- プレビューグリッド --}}
                <div id="img-grid" class="grid grid-cols-3 gap-2 mb-2 hidden"></div>

                <label id="img-add-btn"
                       class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-surface-300 rounded-xl cursor-pointer hover:border-deli-500 transition bg-surface-600">
                    <svg class="w-8 h-8 text-[#6A6A7E] mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-sm text-[#6A6A7E]">タップして写真を追加</span>
                    <input type="file" name="images[]" accept="image/*" multiple class="hidden" id="img-input">
                </label>
                <p id="img-count-note" class="text-xs text-[#4A4A5E] mt-1 hidden"></p>
                @error('images.*')<p class="text-xs text-deli-400 mt-1">{{ $message }}</p>@enderror
                @error('images')<p class="text-xs text-deli-400 mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- 本文 --}}
            <div>
                <label class="text-sm font-medium text-[#E8E4DC] block mb-2">本文</label>
                <textarea name="body" rows="6" maxlength="2000"
                       placeholder="今日のことを書いてみよう..."
                       class="w-full bg-surface-600 border border-surface-300 rounded-xl px-4 py-3 text-sm text-[#E8E4DC] placeholder-[#4A4A5E] focus:outline-none focus:border-deli-500 resize-none">{{ old('body') }}</textarea>
                @error('body')<p class="text-xs text-deli-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit" {{ $postedToday ? 'disabled' : '' }}
                    class="w-full bg-deli-500 hover:bg-deli-400 active:scale-95 text-white font-bold py-4 rounded-xl transition text-base disabled:opacity-40 disabled:cursor-not-allowed">
                投稿する
            </button>
        </form>

        <p class="text-center text-xs text-[#4A4A5E] mt-4">
            このURLは {{ $dt->expires_at->format('Y年m月d日') }} まで有効です
        </p>
    </div>
</div>

@push('scripts')
<script @nonce>
(function() {
    var MAX = 5;
    var files = [];
    var input = document.getElementById('img-input');
    var grid  = document.getElementById('img-grid');
    var addBtn = document.getElementById('img-add-btn');
    var note  = document.getElementById('img-count-note');

    input.addEventListener('change', function() {
        var selected = Array.from(this.files);
        var remaining = MAX - files.length;
        selected.slice(0, remaining).forEach(function(file) {
            files.push(file);
        });
        this.value = '';
        render();
    });

    function render() {
        grid.innerHTML = '';
        files.forEach(function(file, i) {
            var r = new FileReader();
            r.onload = function(e) {
                var cell = document.createElement('div');
                cell.className = 'relative aspect-square rounded-lg overflow-hidden bg-surface-600';
                cell.innerHTML =
                    '<img src="' + e.target.result + '" class="w-full h-full object-cover">' +
                    '<button type="button" data-i="' + i + '" class="absolute top-1 right-1 w-5 h-5 bg-black/60 rounded-full text-white text-xs flex items-center justify-center leading-none">×</button>';
                grid.appendChild(cell);
            };
            r.readAsDataURL(file);
        });

        if (files.length > 0) {
            grid.classList.remove('hidden');
            note.textContent = files.length + '枚選択中（最大' + MAX + '枚）';
            note.classList.remove('hidden');
        } else {
            grid.classList.add('hidden');
            note.classList.add('hidden');
        }

        addBtn.style.display = (files.length >= MAX) ? 'none' : '';
        syncHiddenInputs();
    }

    grid.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-i]');
        if (!btn) return;
        files.splice(parseInt(btn.dataset.i), 1);
        render();
    });

    function syncHiddenInputs() {
        // フォーム送信前にFileListを再構成（DataTransferを使用）
        var dt = new DataTransfer();
        files.forEach(function(f) { dt.items.add(f); });
        input.files = dt.files;
    }
})();
</script>
@endpush
@endsection
