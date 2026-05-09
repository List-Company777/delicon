@extends('layouts.manage')
@section('title', '写メ日記一覧')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-[#F0ECE4] mb-6">写メ日記一覧</h1>

    @if(session('success'))
    <div class="mb-4 bg-emerald-100 border border-emerald-300 text-emerald-800 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="space-y-4">
        @forelse($diaries as $diary)
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-sm font-medium text-gray-800">{{ $diary->cast?->name ?? '（削除済み）' }}</span>
                    <span class="text-xs text-gray-400">{{ $diary->created_at->format('Y/m/d H:i') }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $diary->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $diary->status === 'published' ? '公開中' : '非公開' }}
                    </span>
                </div>
                <form method="POST" action="{{ route('cast-diary.destroy', $diary) }}/"
                      onsubmit="return confirm('この日記を削除しますか？')">
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
        <p class="text-sm text-gray-400 py-10 text-center">投稿された写メ日記はまだありません。</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $diaries->links() }}</div>
</div>
@endsection
