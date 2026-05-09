@extends('layouts.admin')
@section('title', '口コミ管理')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-white mb-6">口コミ管理</h1>

    @if(session('success'))
    <div class="mb-4 bg-emerald-900/40 border border-emerald-600/40 text-emerald-400 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="space-y-4">
        @forelse($reviews as $review)
        @php
            $matchedUsers = $ipMatches[$review->ip_address] ?? collect();
            $shopUsers    = $matchedUsers->filter(fn($u) => in_array($u->role ?? '', ['company','agency','partner']));
            $isSuspicious = $shopUsers->isNotEmpty();
        @endphp
        <div class="bg-surface-600 border {{ $isSuspicious ? 'border-amber-500/60' : 'border-surface-400' }} rounded-xl p-5">
            {{-- ヘッダー --}}
            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $review->is_approved ? 'bg-emerald-900/50 text-emerald-400' : 'bg-amber-900/50 text-amber-400' }}">
                        {{ $review->is_approved ? '承認済' : '未承認' }}
                    </span>
                    @if($isSuspicious)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-red-900/50 text-red-400 font-medium">
                        ⚠ 店舗ログインIPと一致
                    </span>
                    @endif
                    <span class="text-xs text-[#9A96A0]">{{ $review->created_at->format('Y/m/d H:i') }}</span>
                </div>
                <div class="flex gap-2">
                    @if(!$review->is_approved)
                    <form method="POST" action="{{ route('admin.cast-reviews.approve', $review) }}/">
                        @csrf @method('PATCH')
                        <button class="text-xs bg-emerald-700 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg transition">承認</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('admin.cast-reviews.reject', $review) }}/"
                          onsubmit="return confirm('この口コミを削除しますか？')">
                        @csrf @method('DELETE')
                        <button class="text-xs bg-red-900/60 hover:bg-red-800 text-red-400 px-3 py-1.5 rounded-lg transition">削除</button>
                    </form>
                </div>
            </div>

            {{-- 口コミ内容 --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 text-xs text-[#9A96A0] mb-3">
                <div>
                    <span class="text-[#6A6A7E]">キャスト:</span>
                    <a href="{{ route('cast.show', $review->cast_id) }}/" target="_blank"
                       class="text-deli-400 hover:underline ml-1">{{ $review->cast?->name ?? '（削除済み）' }}</a>
                </div>
                <div>
                    <span class="text-[#6A6A7E]">店舗:</span>
                    <span class="ml-1 text-[#C8C4BC]">{{ $review->cast?->shop?->name ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-[#6A6A7E]">投稿者:</span>
                    <span class="ml-1 text-[#C8C4BC]">{{ $review->nickname }}</span>
                    @if($review->user)
                    <span class="text-[#6A6A7E] ml-1">({{ $review->user->email }} / {{ $review->user->role }})</span>
                    @endif
                </div>
                <div>
                    <span class="text-[#6A6A7E]">評価:</span>
                    <span class="ml-1 text-[#C8C4BC]">{{ $review->rating }} / 5</span>
                </div>
            </div>
            <p class="text-sm text-[#E8E4DC] leading-relaxed mb-3">{{ $review->body }}</p>

            {{-- IP情報 --}}
            <div class="border-t border-surface-400 pt-3">
                <p class="text-xs text-[#6A6A7E]">
                    投稿IP: <code class="text-[#C8C4BC] font-mono">{{ $review->ip_address ?? '不明' }}</code>
                </p>
                @if($matchedUsers->isNotEmpty())
                <div class="mt-2 space-y-1">
                    <p class="text-xs font-medium {{ $isSuspicious ? 'text-amber-400' : 'text-[#9A96A0]' }}">同IPからのログイン履歴:</p>
                    @foreach($matchedUsers as $mu)
                    <div class="text-xs text-[#9A96A0] flex items-center gap-2 pl-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-medium
                            {{ in_array($mu->role ?? '', ['company','agency','partner']) ? 'bg-red-900/40 text-red-400' : 'bg-surface-500 text-[#6A6A7E]' }}">
                            {{ $mu->role ?? '不明' }}
                        </span>
                        <span>{{ $mu->name }}</span>
                        <span class="text-[#6A6A7E]">{{ $mu->email }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-xs text-[#6A6A7E] mt-1">同IPのログイン記録なし</p>
                @endif
            </div>
        </div>
        @empty
        <p class="text-sm text-[#6A6A7E]">口コミはありません。</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $reviews->links() }}</div>
</div>
@endsection
