@extends('layouts.app')

@section('title', $cast->name)
@section('description',
    $cast->name . '(' . ($cast->age ? $cast->age . '歳' : '') . ')' .
    ($cast->castType ? '・' . $cast->castType->name : '') .
    ($cast->shop ? ' - ' . $cast->shop->name : '') .
    ($cast->comment ? '。' . mb_strimwidth(strip_tags($cast->comment), 0, 60, '…') : '')
)
@section('canonical', route('cast.show', $cast->id) . '/')
@if($cast->img_url !== '/img/no-cast.jpg')
@section('ogp_image', url($cast->img_url))
@section('twitter_card', 'summary_large_image')
@endif

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    {{-- パンくず --}}
    <nav class="text-xs text-[#6A6A7E] mb-5 flex flex-wrap items-center gap-1">
        <a href="{{ route('top') }}/" class="hover:text-deli-400 transition">ホーム</a>
        <span class="text-[#3A3A4E]">/</span>
        <a href="{{ route('cast.index') }}/" class="hover:text-deli-400 transition">キャスト検索</a>
        @if($cast->shop)
        <span class="text-[#3A3A4E]">/</span>
        <a href="{{ route('shop.show', $cast->shop->id) }}/" class="hover:text-deli-400 transition">{{ $cast->shop->name }}</a>
        @endif
        <span class="text-[#3A3A4E]">/</span>
        <span class="text-[#C8C4BC]">{{ $cast->name }}</span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- 左カラム：写真 --}}
        <div>
            <div class="relative">
                <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                     class="img-onerror-cast w-full rounded-xl border border-surface-300 mb-3" loading="eager">
                {{-- 待機中バッジ --}}
                @if($cast->working_date && $cast->working_date->isToday())
                <span class="absolute top-2 left-2 text-xs font-bold bg-emerald-500 text-white px-2.5 py-1 rounded-full shadow">待機中</span>
                @endif
                @if($cast->isNew())
                <span class="absolute top-2 right-2 text-xs font-bold bg-gold-500 text-white px-2.5 py-1 rounded-full shadow">NEW</span>
                @endif
            </div>
            @if($cast->images->count() > 0)
            <div class="grid grid-cols-3 gap-1.5">
                @foreach($cast->images as $img)
                <picture>
                    <source srcset="{{ Storage::url(\App\Services\ImageService::webpPath($img->img_path)) }}" type="image/webp">
                    <img src="{{ Storage::url($img->img_path) }}" alt="{{ $cast->name }}"
                         class="w-full aspect-square object-cover rounded-lg border border-surface-400" loading="lazy">
                </picture>
                @endforeach
            </div>
            @endif
        </div>

        {{-- 右カラム：情報 --}}
        <div class="md:col-span-2 space-y-4">

            {{-- プロフィールヘッダー --}}
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <div class="flex flex-wrap gap-1.5 mb-3">
                    @if($cast->castType)
                    <span class="text-xs bg-deli-900/40 text-deli-400 border border-deli-800/60 px-2.5 py-0.5 rounded-full">{{ $cast->castType->name }}</span>
                    @endif
                    @if($cast->is_recommended)
                    <span class="text-xs bg-gold-500/20 text-gold-400 border border-gold-700/40 px-2.5 py-0.5 rounded-full">おすすめ</span>
                    @endif
                </div>
                <div class="flex items-start justify-between gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-[#F0ECE4] tracking-tight">{{ $cast->name }}</h1>
                    <button id="fav-btn" data-cast="{{ $cast->id }}"
                            data-favorited="{{ $isFavorited ? 'true' : 'false' }}"
                            class="shrink-0 w-10 h-10 flex items-center justify-center rounded-full border transition
                                   {{ $isFavorited ? 'bg-deli-500 border-deli-500 text-white' : 'bg-surface-600 border-surface-300 text-[#6A6A7E] hover:border-deli-400 hover:text-deli-400' }}"
                            title="{{ auth()->check() ? 'お気に入り' : 'ログインするとお気に入り登録できます' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                             fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor"
                             stroke-width="2" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </button>
                </div>
                @if($cast->shop)
                <p class="text-sm text-[#6A6A7E]">
                    <a href="{{ route('shop.show', $cast->shop->id) }}/" class="hover:text-deli-400 transition">{{ $cast->shop->name }}</a>
                </p>
                @endif
                @if($cast->comment)
                <div class="mt-3 text-sm text-[#C8C4BC] leading-relaxed bg-surface-600 border border-surface-400 rounded-lg p-3">
                    {!! nl2br(e($cast->comment)) !!}
                </div>
                @endif
            </div>

            {{-- スペック --}}
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <h2 class="font-bold text-[#E8E4DC] mb-3 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-deli-500 rounded-full"></span>プロフィール
                </h2>
                <div class="grid grid-cols-2 gap-x-4 text-sm">
                    @if($cast->age)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">年齢</span><span class="text-[#E8E4DC]">{{ $cast->age }}歳</span></div>
                    @endif
                    @if($cast->tall)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">身長</span><span class="text-[#E8E4DC]">{{ $cast->tall }}cm</span></div>
                    @endif
                    @if($cast->bust && $cast->cup)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">バスト</span><span class="text-[#E8E4DC]">{{ $cast->bust }}cm {{ $cast->cup }}カップ</span></div>
                    @endif
                    @if($cast->west)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">ウエスト</span><span class="text-[#E8E4DC]">{{ $cast->west }}cm</span></div>
                    @endif
                    @if($cast->hip)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">ヒップ</span><span class="text-[#E8E4DC]">{{ $cast->hip }}cm</span></div>
                    @endif
                    @if($cast->bodyType)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">スタイル</span><span class="text-[#E8E4DC]">{{ $cast->bodyType->name }}</span></div>
                    @endif
                    @if($cast->blood)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">血液型</span><span class="text-[#E8E4DC]">{{ $cast->blood }}型</span></div>
                    @endif
                    @if($cast->country)
                    <div class="border-b border-surface-400 py-2 flex justify-between"><span class="text-[#6A6A7E]">出身</span><span class="text-[#E8E4DC]">{{ $cast->country }}</span></div>
                    @endif
                    @if($cast->price_on)
                    <div class="border-b border-surface-400 py-2 flex justify-between col-span-2"><span class="text-[#6A6A7E]">指名料</span><span class="text-deli-400 font-medium">¥{{ number_format($cast->price_on) }}</span></div>
                    @endif
                </div>
            </div>

            {{-- キャストからのメッセージ --}}
            @if($cast->message)
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <h2 class="font-bold text-[#E8E4DC] mb-2 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-deli-500 rounded-full"></span>キャストからのメッセージ
                </h2>
                <p class="text-sm text-[#C8C4BC] leading-relaxed">{!! nl2br(e($cast->message)) !!}</p>
            </div>
            @endif

            {{-- 詳細プロフィール --}}
            @php $details = array_filter([
                '初体験' => $cast->hatsutaiken, '性感帯' => $cast->seikantai,
                '得意技' => $cast->tokuiwaza, '好きなタイプ' => $cast->sukinatype,
                '趣味' => $cast->shumi, '前職' => $cast->zenshoku,
                '好きな食べ物' => $cast->likeeat, '芸能人似' => $cast->yuumeijin,
                '潮吹き' => $cast->shiofuki, '自宅派/ホテル派' => $cast->zitaku,
                '星座' => $cast->seiza,
            ]); @endphp
            @if(count($details) > 0)
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <h2 class="font-bold text-[#E8E4DC] mb-3 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-surface-200 rounded-full"></span>詳細プロフィール
                </h2>
                <div class="grid grid-cols-2 gap-x-4 text-sm">
                    @foreach($details as $label => $value)
                    <div class="border-b border-surface-400 py-2 flex justify-between gap-2">
                        <span class="text-[#6A6A7E] shrink-0">{{ $label }}</span>
                        <span class="text-[#C8C4BC] text-right">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- チャームポイント --}}
            @if($cast->charms->count() > 0)
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <h2 class="font-bold text-[#E8E4DC] mb-3 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-gold-500 rounded-full"></span>チャームポイント
                </h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($cast->charms as $charm)
                    <span class="bg-gold-900/30 text-gold-400 border border-gold-800/40 text-xs px-2.5 py-1 rounded-full">{{ $charm->name }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 可能プレイ --}}
            @if($cast->plays->count() > 0)
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <h2 class="font-bold text-[#E8E4DC] mb-3 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-deli-600 rounded-full"></span>可能プレイ
                </h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($cast->plays as $play)
                    <span class="bg-deli-900/30 text-deli-400 border border-deli-800/40 text-xs px-2.5 py-1 rounded-full">{{ $play->name }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- タグ --}}
            @if($cast->tags->count() > 0)
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <h2 class="font-bold text-[#E8E4DC] mb-3 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-surface-200 rounded-full"></span>タグ
                </h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($cast->tags as $tag)
                    <span class="bg-surface-400 text-[#C8C4BC] text-xs px-2.5 py-1 rounded-full border border-surface-300">{{ $tag->name }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 出勤スケジュール --}}
            @if($cast->schedules->count() > 0)
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <h2 class="font-bold text-[#E8E4DC] mb-3 text-sm flex items-center gap-2">
                    <span class="w-1 h-4 bg-emerald-500 rounded-full"></span>出勤スケジュール
                </h2>
                <div class="space-y-1.5 text-sm">
                    @foreach($cast->schedules->take(7) as $schedule)
                    <div class="flex items-center gap-3 py-1 border-b border-surface-400 last:border-0">
                        <span class="text-[#6A6A7E] w-24 shrink-0">{{ $schedule->work_date->format('m/d(D)') }}</span>
                        <span class="text-[#E8E4DC]">{{ $schedule->start_time }}〜{{ $schedule->end_time }}</span>
                        @if($schedule->note)<span class="text-[#6A6A7E] text-xs">{{ $schedule->note }}</span>@endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif


        </div>
    </div>

    {{-- 写メ日記 --}}
    @if($cast->diaries->count() > 0)
    <section class="mt-10">
        <h2 class="text-lg font-bold text-[#F0ECE4] mb-5 flex items-center gap-3">
            <span class="w-1 h-6 bg-deli-500 rounded-full inline-block"></span>
            写メ日記
        </h2>
        <div class="space-y-5">
            @foreach($cast->diaries->take(5) as $diary)
            <article class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xs text-[#6A6A7E]">{{ $diary->created_at->format('Y年m月d日') }}</span>
                    @if($diary->title)
                    <h3 class="font-bold text-[#E8E4DC] text-sm">{{ $diary->title }}</h3>
                    @endif
                </div>
                @if($diary->images->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 mb-3">
                    @foreach($diary->images as $img)
                    <picture>
                        <source srcset="{{ Storage::url(\App\Services\ImageService::webpPath($img->img_path)) }}" type="image/webp">
                        <img src="{{ Storage::url($img->img_path) }}" alt="{{ $cast->name }}の日記"
                             class="w-full aspect-square object-cover rounded-lg border border-surface-400" loading="lazy">
                    </picture>
                    @endforeach
                </div>
                @endif
                @if($diary->body)
                <p class="text-sm text-[#C8C4BC] leading-relaxed">{!! nl2br(e($diary->body)) !!}</p>
                @endif
            </article>
            @endforeach
        </div>
    </section>
    @endif

    {{-- キャスト口コミ --}}
    @if($cast->reviews->where('is_approved', true)->count() > 0)
    <section class="mt-10">
        <h2 class="text-lg font-bold text-[#F0ECE4] mb-5 flex items-center gap-3">
            <span class="w-1 h-6 bg-gold-500 rounded-full inline-block"></span>
            口コミ <span class="text-sm text-[#6A6A7E] font-normal">({{ $cast->reviews->where('is_approved', true)->count() }}件)</span>
        </h2>
        <div class="space-y-4">
            @foreach($cast->reviews->where('is_approved', true)->take(5) as $review)
            <div class="bg-surface-500 border border-surface-300 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-amber-400 text-sm tracking-widest">{{ str_repeat('★', $review->rating) }}<span class="text-[#3A3A4E]">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                    <span class="text-xs text-[#6A6A7E]">{{ $review->nickname }}</span>
                    <span class="text-xs text-[#4A4A5E]">{{ $review->created_at->format('Y/m/d') }}</span>
                </div>
                <p class="text-sm text-[#C8C4BC] leading-relaxed">{{ $review->body }}</p>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- 同店舗の在籍キャスト --}}
    @if($otherCasts->count() > 0)
    <section class="mt-10">
        <h2 class="text-lg font-bold text-[#F0ECE4] mb-5 flex items-center gap-3">
            <span class="w-1 h-6 bg-surface-200 rounded-full inline-block"></span>
            @if($cast->shop)<a href="{{ route('shop.show', $cast->shop->id) }}/" class="hover:text-deli-400 transition">{{ $cast->shop->name }}</a>の@endif在籍キャスト
        </h2>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
            @foreach($otherCasts as $other)
            <a href="{{ route('cast.show', $other->id) }}/" class="group text-center">
                <div class="aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 border border-surface-300 group-hover:border-deli-500 transition mb-1">
                    <img src="{{ $other->img_url }}" alt="{{ $other->name }}"
                         class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition" loading="lazy">
                </div>
                <p class="text-xs text-[#C8C4BC] group-hover:text-gold-400 transition truncate">{{ $other->name }}</p>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- こちらのキャストも人気（類似） --}}
    @if($similarCasts->isNotEmpty())
    <section class="mt-10">
        <h2 class="text-lg font-bold text-[#F0ECE4] mb-5 flex items-center gap-3">
            <span class="w-1 h-6 bg-deli-600 rounded-full inline-block"></span>
            こちらのキャストも人気
        </h2>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
            @foreach($similarCasts as $similar)
            <a href="{{ route('cast.show', $similar->id) }}/" class="group text-center">
                <div class="aspect-[3/4] overflow-hidden rounded-lg bg-surface-400 border border-surface-300 group-hover:border-deli-500 transition mb-1">
                    <img src="{{ $similar->img_url }}" alt="{{ $similar->name }}"
                         class="img-onerror-cast w-full h-full object-cover group-hover:scale-105 transition" loading="lazy">
                </div>
                <p class="text-xs text-[#C8C4BC] group-hover:text-gold-400 transition truncate">{{ $similar->name }}</p>
                @if($similar->shop)
                <p class="text-[10px] text-[#6A6A7E] truncate">{{ $similar->shop->name }}</p>
                @endif
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- 削除依頼フォーム --}}
    <div class="mt-10 pt-6 border-t border-surface-400">
        @if(session('deletion_sent'))
        <div class="mb-3 bg-emerald-900/40 border border-emerald-600/40 text-emerald-400 text-sm px-4 py-3 rounded-lg">削除依頼を受け付けました。確認後ご連絡いたします。</div>
        @endif
        <details class="group">
            <summary class="cursor-pointer text-xs text-[#6A6A7E] hover:text-[#C8C4BC] transition list-none flex items-center gap-2 select-none">
                <svg class="w-3.5 h-3.5 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                このページの削除を依頼する
            </summary>
            <div class="mt-4 bg-surface-600 border border-surface-400 rounded-xl p-5">
                <h3 class="text-sm font-bold text-[#E8E4DC] mb-1">削除依頼フォーム</h3>
                <p class="text-xs text-[#6A6A7E] mb-4">ご本人様またはご関係者からの削除依頼を受け付けています。内容を確認後、速やかに対応いたします。</p>
                <form method="POST" action="{{ route('cast.deletion-request', $cast->id) }}/">
                    @csrf
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-[#C8C4BC] block mb-1">お名前 <span class="text-deli-400">*</span></label>
                            <input type="text" name="requester_name" required maxlength="50" value="{{ old('requester_name') }}"
                                   class="w-full bg-surface-500 border border-surface-300 rounded-lg px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:border-deli-500">
                            @error('requester_name')<p class="text-xs text-deli-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-xs text-[#C8C4BC] block mb-1">メールアドレス <span class="text-deli-400">*</span></label>
                            <input type="email" name="requester_email" required maxlength="100" value="{{ old('requester_email') }}"
                                   class="w-full bg-surface-500 border border-surface-300 rounded-lg px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:border-deli-500">
                            @error('requester_email')<p class="text-xs text-deli-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-xs text-[#C8C4BC] block mb-1">削除理由（任意）</label>
                            <textarea name="reason" rows="3" maxlength="500"
                                   class="w-full bg-surface-500 border border-surface-300 rounded-lg px-3 py-2 text-sm text-[#E8E4DC] focus:outline-none focus:border-deli-500 resize-none">{{ old('reason') }}</textarea>
                        </div>
                        <button type="submit" class="w-full bg-surface-400 hover:bg-surface-300 text-[#C8C4BC] text-sm font-bold py-2 rounded-lg transition">
                            削除を依頼する
                        </button>
                    </div>
                </form>
            </div>
        </details>
    </div>

</div>

@push('scripts')
<script @nonce>
(function() {
    var btn = document.getElementById('fav-btn');
    if (!btn) return;
    @auth
    btn.addEventListener('click', function() {
        fetch('/favorites/' + btn.dataset.cast + '/', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            }
        }).then(function(r){ return r.json(); }).then(function(data) {
            btn.dataset.favorited = data.favorited ? 'true' : 'false';
            var svg = btn.querySelector('svg');
            if (data.favorited) {
                btn.classList.add('bg-deli-500','border-deli-500','text-white');
                btn.classList.remove('bg-surface-600','border-surface-300','text-[#6A6A7E]');
                svg.setAttribute('fill','currentColor');
            } else {
                btn.classList.remove('bg-deli-500','border-deli-500','text-white');
                btn.classList.add('bg-surface-600','border-surface-300','text-[#6A6A7E]');
                svg.setAttribute('fill','none');
            }
        });
    });
    @else
    btn.addEventListener('click', function() {
        window.location.href = '{{ route("visitor.register") }}/';
    });
    @endauth
})();
</script>
@endpush
@endsection
