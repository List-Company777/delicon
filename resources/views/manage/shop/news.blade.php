@extends('layouts.app')
@section('title', 'お知らせ管理')

@section('content')
<div class="bg-business-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold text-lg">店舗管理</h1>
        <div class="flex items-center gap-4 text-sm">
            <span class="opacity-70">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}/" method="POST">
                @csrf
                <button type="submit" class="opacity-70 hover:opacity-100 transition">ログアウト</button>
            </form>
        </div>
    </div>
</div>

@include('manage._nav')


<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->has('is_pinned') || $errors->has('pin'))
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">{{ $errors->first('is_pinned') ?: $errors->first('pin') }}</div>
    @endif

    {{-- 投稿フォーム --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-bold text-gray-800 mb-3">お知らせを追加</h2>
        <p class="text-xs text-gray-500 mb-4">店舗の詳細ページでユーザーに伝えたいニュースを発信できます。古いデータは自動削除されます。</p>
        <form method="POST" action="{{ route('manage.shop.news.store') }}">
            @csrf
            <div class="mb-3">
                <textarea name="body" rows="4" maxlength="1000"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-business-400 @error('body') border-red-400 @enderror"
                    placeholder="お知らせ内容を入力してください（最大1000文字）">{{ old('body') }}</textarea>
                @error('body')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                    <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned')) class="rounded border-gray-300 text-business-600">
                    <span class="font-medium">📌 必ず表示（最大3件）</span>
                </label>
                <button type="submit" class="bg-business-600 text-white text-sm px-5 py-2 rounded-lg hover:bg-business-700 transition">追加</button>
            </div>
        </form>
    </div>

    {{-- 一覧 --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-gray-800 text-sm">投稿一覧（最大10件保持）</h2>
            <span class="text-xs text-gray-400">{{ $news->count() }} 件</span>
        </div>
        @forelse($news as $item)
        <div class="px-5 py-4 border-b border-gray-100 last:border-0">
            <div class="flex items-start gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs text-gray-400">{{ $item->created_at->format('Y/m/d') }}</span>
                        @if($item->is_pinned)
                        <span class="text-xs bg-amber-100 text-amber-700 font-medium px-1.5 py-0.5 rounded">📌 必ず表示</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap break-words">{{ $item->body }}</p>
                </div>
                <div class="flex items-center gap-2 shrink-0 mt-1">
                    {{-- ピン切替 --}}
                    <form method="POST" action="{{ route('manage.shop.news.pin', $item) }}">
                        @csrf @method('PATCH')
                        <button type="submit" title="{{ $item->is_pinned ? 'ピン固定解除' : '必ず表示にする' }}"
                            class="text-sm px-2 py-1 rounded border transition {{ $item->is_pinned ? 'border-amber-300 text-amber-600 hover:bg-amber-50' : 'border-gray-200 text-gray-400 hover:border-amber-300 hover:text-amber-600' }}">
                            📌
                        </button>
                    </form>
                    {{-- 削除 --}}
                    <form method="POST" action="{{ route('manage.shop.news.destroy', $item) }}"
                        data-confirm="このお知らせを削除しますか？">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm px-2 py-1 rounded border border-gray-200 text-red-400 hover:border-red-300 hover:bg-red-50 transition">
                            削除
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-sm text-gray-400">お知らせはまだありません</div>
        @endforelse
    </div>

</div>
@endsection

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
document.querySelectorAll('form[data-confirm]').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        if (!confirm(this.dataset.confirm)) e.preventDefault();
    });
});
</script>
@endpush
