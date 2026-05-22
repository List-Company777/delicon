@extends('layouts.app')
@section('title', $cast->name . ' - 写メ日記')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-[#F0ECE4]">{{ $cast->name }} の写メ日記</h1>
            <a href="{{ route('manage.cast-profile.edit', $cast->id) }}/" class="text-xs text-[#6A6A7E] hover:text-deli-400">← キャスト編集に戻る</a>
        </div>
        <a href="{{ route('manage.cast-diary.create', $cast->id) }}/" class="bg-deli-500 hover:bg-deli-400 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
            + 新しい日記
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-emerald-900/40 border border-emerald-600/40 text-emerald-400 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- キャスト投稿URL --}}
    @php $token = \App\Models\CastDiaryToken::where('cast_id', $cast->id)->first(); @endphp
    <div class="bg-surface-500 border border-surface-300 rounded-xl p-5 mb-6">
        <p class="text-sm font-bold text-[#E8E4DC] mb-2">キャスト用投稿URL</p>
        @if($token && !$token->isExpired())
        <div class="flex gap-2 items-center">
            <input type="text" readonly id="token-url"
                   value="{{ url('/diary/post/' . $token->token) }}/"
                   class="flex-1 bg-surface-600 border border-surface-300 rounded-lg px-3 py-2 text-xs text-[#C8C4BC] min-w-0">
            <button onclick="navigator.clipboard.writeText(document.getElementById('token-url').value).then(()=>this.textContent='コピー済み')"
                    class="shrink-0 bg-deli-500 hover:bg-deli-400 text-white text-xs font-bold px-3 py-2 rounded-lg transition">
                コピー
            </button>
        </div>
        <p class="text-[10px] text-[#6A6A7E] mt-1.5">有効期限: {{ $token->expires_at->format('Y/m/d') }}</p>
        <p class="text-[10px] text-amber-400/80 mt-1">このURLをキャストに渡すと、キャストが直接写メ日記を投稿できます。URLは有効期限内のみ有効です。</p>
        @else
        <div class="bg-surface-600 border border-surface-300 rounded-lg px-4 py-3 mb-3">
            <p class="text-sm text-[#C8C4BC] font-bold mb-1">URLが発行されていません</p>
            <p class="text-xs text-[#8A8A9E] leading-relaxed">「投稿URLを発行する」をクリックするとURLが生成されます。そのURLをキャストに渡していただくと、キャストが自分のスマートフォンから直接写メ日記を投稿できます。URLの有効期間は<strong class="text-[#C8C4BC]">6ヶ月間</strong>です。</p>
        </div>
        @endif
        <form method="POST" action="{{ route('manage.cast-diary.issue-token', $cast->id) }}/" class="mt-2">
            @csrf
            <button type="submit" class="text-xs text-[#6A6A7E] hover:text-deli-400 underline transition">
                {{ $token ? 'URLを再発行する（旧URLは無効になります）' : '投稿URLを発行する' }}
            </button>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($diaries as $diary)
        <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $diary->status === 'published' ? 'bg-emerald-900/40 text-emerald-400' : 'bg-surface-400 text-[#6A6A7E]' }}">
                            {{ $diary->status === 'published' ? '公開' : '下書き' }}
                        </span>
                        <span class="text-xs text-[#6A6A7E]">{{ $diary->created_at->format('Y/m/d H:i') }}</span>
                    </div>
                    @if($diary->title)
                    <p class="font-bold text-[#E8E4DC] mb-1">{{ $diary->title }}</p>
                    @endif
                    @if($diary->body)
                    <p class="text-sm text-[#C8C4BC] line-clamp-2">{{ $diary->body }}</p>
                    @endif
                    @if($diary->images->count() > 0)
                    <div class="flex gap-1 mt-2">
                        @foreach($diary->images->take(4) as $img)
                        <img src="{{ Storage::url($img->img_path) }}" class="w-14 h-14 object-cover rounded border border-surface-400" loading="lazy">
                        @endforeach
                        @if($diary->images->count() > 4)
                        <div class="w-14 h-14 rounded border border-surface-400 bg-surface-400 flex items-center justify-center text-xs text-[#6A6A7E]">+{{ $diary->images->count() - 4 }}</div>
                        @endif
                    </div>
                    @endif
                </div>
                <form method="POST" action="{{ route('manage.cast-diary.destroy', $diary->id) }}/" onsubmit="return confirm('削除しますか？')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-[#6A6A7E] hover:text-red-400 transition shrink-0">削除</button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-12 text-[#6A6A7E] text-sm">まだ日記がありません</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $diaries->links() }}</div>
</div>
@endsection
