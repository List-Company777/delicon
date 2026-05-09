@extends('layouts.admin')
@section('title', '写メ日記管理')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-white">写メ日記管理
            @if($diaries->total() > 0)
            <span class="ml-2 text-sm bg-amber-500/20 text-amber-400 border border-amber-500/40 px-2 py-0.5 rounded-full">未確認 {{ $diaries->total() }}件</span>
            @endif
        </h1>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-emerald-900/40 border border-emerald-600/40 text-emerald-400 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="space-y-4">
        @forelse($diaries as $diary)
        <div class="bg-surface-600 border border-surface-400 rounded-xl p-4">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-sm font-medium text-[#E8E4DC]">
                        <a href="{{ route('cast.show', $diary->cast_id) }}/" target="_blank"
                           class="hover:text-deli-400 transition">{{ $diary->cast?->name ?? '（削除済み）' }}</a>
                    </span>
                    <span class="text-xs text-[#6A6A7E]">{{ $diary->cast?->shop?->name ?? '—' }}</span>
                    <span class="text-xs text-[#6A6A7E]">{{ $diary->created_at->format('Y/m/d H:i') }}</span>
                </div>
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('admin.cast-diaries.approve', $diary) }}/">
                        @csrf @method('PATCH')
                        <button class="text-xs bg-emerald-700 hover:bg-emerald-600 text-white px-4 py-1.5 rounded-lg transition font-medium">OK</button>
                    </form>
                    <form method="POST" action="{{ route('admin.cast-diaries.destroy', $diary) }}/"
                          onsubmit="return confirm('この日記を削除しますか？')">
                        @csrf @method('DELETE')
                        <button class="text-xs bg-red-900/60 hover:bg-red-800 text-red-400 px-4 py-1.5 rounded-lg transition font-medium">NG（削除）</button>
                    </form>
                </div>
            </div>

            @if($diary->title)
            <p class="text-sm font-bold text-[#E8E4DC] mb-2">{{ $diary->title }}</p>
            @endif

            @if($diary->body)
            <p class="text-xs text-[#9A96A0] leading-relaxed mb-3 line-clamp-3">{{ $diary->body }}</p>
            @endif

            @if($diary->images->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                @foreach($diary->images as $img)
                <a href="{{ Storage::url($img->img_path) }}" target="_blank">
                    <picture>
                        <source srcset="{{ Storage::url(\App\Services\ImageService::webpPath($img->img_path)) }}" type="image/webp">
                        <img src="{{ Storage::url($img->img_path) }}" alt=""
                             class="w-20 h-20 object-cover rounded-lg border border-surface-400 hover:border-deli-400 transition">
                    </picture>
                </a>
                @endforeach
            </div>
            @else
            <p class="text-xs text-[#4A4A5E]">画像なし</p>
            @endif
        </div>
        @empty
        <div class="text-center py-16 text-[#6A6A7E]">
            <p class="text-lg mb-1">未確認の日記はありません</p>
            <p class="text-sm">新しい投稿があるとここに表示されます。</p>
        </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $diaries->links() }}</div>
</div>
@endsection
