@extends('layouts.app')
@section('title', 'キャスト編集')
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

<div class="max-w-5xl mx-auto px-4 pb-12">
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('manage.cast-profile.index') }}/" class="text-sm text-[#9A9A9E] hover:text-[#C8C4BC]">← キャスト一覧</a>
        <h2 class="text-lg font-bold text-[#E8E4DC]">{{ $cast->name }} を編集</h2>
        <a href="{{ route('manage.cast-diary.index', $cast->id) }}/" class="text-sm bg-deli-500 hover:bg-deli-400 text-white px-3 py-1.5 rounded-lg transition">この女性の写メ日記を作成・管理</a>
    </div>

    <form action="{{ route('manage.cast-profile.update', $cast->id) }}/" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm overflow-hidden">
        @csrf @method('PUT')
        @include('manage.cast_profile._form', ['cast' => $cast])
        <div class="px-4 py-4 bg-gray-50 border-t border-gray-100 text-right">
            <button type="submit" class="bg-red-600 hover:bg-business-700 text-white text-sm font-bold px-6 py-2 rounded-lg transition">
                保存する
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script @nonce>
(function() {
    var ta = document.getElementById('cast-comment');
    var lenEl = document.getElementById('comment-len');
    var warn = document.getElementById('noindex-warning');
    if (!ta) return;

    ta.addEventListener('input', function() {
        var len = [...this.value].length;
        if (lenEl) lenEl.textContent = len;
        if (warn) warn.style.display = len >= 100 ? 'none' : '';
    });
})();
</script>
@endpush
@endsection
