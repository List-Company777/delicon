@extends('layouts.app')
@section('title', '写メ日記一覧')

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

<div class="max-w-4xl mx-auto px-4 pb-12">
    <h1 class="text-xl font-bold text-gray-800 mb-6">写メ日記一覧</h1>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="space-y-4">
        @forelse($diaries as $diary)
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-sm font-medium text-gray-800">{{ $diary->cast?->name ?? '（削除済み）' }}</span>
                    <span class="text-xs text-gray-400">{{ $diary->created_at->format('Y/m/d H:i') }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $diary->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $diary->status === 'published' ? '公開中' : '非公開' }}
                    </span>
                </div>
                <form method="POST" action="{{ route('manage.cast-diary.destroy', $diary) }}/"
                      data-confirm="この日記を削除しますか？">
                    @csrf @method('DELETE')
                    <button class="text-xs bg-red-50 hover:bg-red-100 text-red-500 border border-red-200 px-3 py-1.5 rounded-lg transition">削除</button>
                </form>
            </div>

            @if($diary->title)
            <p class="text-sm font-bold text-gray-700 mb-2">{{ $diary->title }}</p>
            @endif

            @if($diary->body)
            <p class="text-xs text-gray-500 leading-relaxed mb-3 line-clamp-2">{{ $diary->body }}</p>
            @endif

            @if($diary->images->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                @foreach($diary->images as $img)
                <a href="{{ Storage::url($img->img_path) }}" target="_blank">
                    <picture>
                        <source srcset="{{ Storage::url(\App\Services\ImageService::webpPath($img->img_path)) }}" type="image/webp">
                        <img src="{{ Storage::url($img->img_path) }}" alt=""
                             class="w-16 h-16 object-cover rounded-lg border border-gray-200 hover:border-business-400 transition">
                    </picture>
                </a>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div class="py-10 text-center">
            <p class="text-sm text-[#9A9A9E] mb-4">投稿された写メ日記はまだありません。</p>
            <div class="inline-block bg-surface-600 border border-surface-300 rounded-lg px-5 py-4 text-left max-w-md">
                <p class="text-xs text-[#C8C4BC] font-bold mb-2">写メ日記を投稿するには</p>
                <ul class="text-xs text-[#8A8A9E] leading-relaxed space-y-1.5">
                    <li>① 各女性の編集ページ上部の「この女性の写メ日記を作成・管理」ボタンから管理者が直接投稿できます。</li>
                    <li>② 同じページから投稿用URLを発行し、女性自身のスマートフォンから投稿してもらうこともできます。</li>
                </ul>
            </div>
        </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $diaries->links() }}</div>
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
