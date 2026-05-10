@extends('layouts.app')
@section('title', 'キャスト検索')
@section('description', '全国のデリヘル・風俗店に在籍するキャストを検索。タイプ・年齢・スタイルなど条件から探せます。')
@section('canonical', route('cast.index') . '/')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    <h1 class="text-2xl font-bold text-[#F0ECE4] mb-6 flex items-center gap-3">
        <span class="w-1 h-7 bg-deli-400 rounded-full inline-block"></span>
        キャスト検索
    </h1>

    <form method="get" action="{{ route('cast.index') }}/"
          class="bg-surface-600 border border-surface-400 rounded-xl p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-[#7A7A8E] mb-1">タイプ</label>
                <select name="type" class="bg-surface-500 border border-surface-300 text-[#D8D4CC] rounded px-3 py-1.5 text-sm focus:border-deli-500 outline-none">
                    <option value="">すべて</option>
                    @foreach($castTypes as $ct)
                        <option value="{{ $ct->id }}" @selected(request('type') == $ct->id) class="bg-surface-500">{{ $ct->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-[#7A7A8E] mb-1">年齢</label>
                <div class="flex items-center gap-1">
                    <input type="number" name="age_from" value="{{ request('age_from') }}" placeholder="下限" min="18" max="60"
                           class="bg-surface-500 border border-surface-300 text-[#D8D4CC] rounded px-2 py-1.5 text-sm focus:border-deli-500 outline-none w-16">
                    <span class="text-[#6A6A7E] text-sm">〜</span>
                    <input type="number" name="age_to" value="{{ request('age_to') }}" placeholder="上限" min="18" max="60"
                           class="bg-surface-500 border border-surface-300 text-[#D8D4CC] rounded px-2 py-1.5 text-sm focus:border-deli-500 outline-none w-16">
                    <span class="text-[#6A6A7E] text-sm">歳</span>
                </div>
            </div>
            <div>
                <label class="block text-xs text-[#7A7A8E] mb-1">カップ</label>
                <select name="cup" class="bg-surface-500 border border-surface-300 text-[#D8D4CC] rounded px-3 py-1.5 text-sm focus:border-deli-500 outline-none">
                    <option value="">指定なし</option>
                    @foreach(['A','B','C','D','E','F','G','H','I'] as $c)
                        <option value="{{ $c }}" @selected(request('cup') === $c) class="bg-surface-500">{{ $c }}カップ</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-deli-500 hover:bg-deli-400 text-white px-5 py-1.5 rounded text-sm transition font-medium">検索</button>
            @if(request()->hasAny(['type','age_from','age_to','cup']))
            <a href="{{ route('cast.index') }}/" class="text-xs text-[#6A6A7E] hover:text-[#B0AEAD] self-center">リセット</a>
            @endif
        </div>
    </form>

    <p class="text-xs text-[#6A6A7E] mb-5">{{ $casts->total() }}名</p>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-8">
        @forelse($casts as $cast)
        <a href="{{ route('cast.show', $cast->id) }}/" class="group">
            <div class="relative overflow-hidden rounded-xl mb-2 border border-surface-300 group-hover:border-deli-500 transition bg-surface-500">
                <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                     class="w-full aspect-[3/4] object-cover opacity-90 group-hover:opacity-100 group-hover:scale-105 transition duration-300"
                     loading="lazy" onerror="this.src='/img/no-cast.svg'">
                @if($cast->is_recommended)
                <span class="absolute top-2 right-2 bg-deli-500 text-white text-xs px-1.5 py-0.5 rounded-full font-medium">おすすめ</span>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-surface-900/70 via-transparent to-transparent"></div>
                <div class="absolute bottom-2 left-2 right-2">
                    <p class="text-xs font-bold text-white drop-shadow truncate">{{ $cast->name }}</p>
                    @if($cast->age)
                    <p class="text-xs text-[#C0C0D4] drop-shadow">{{ $cast->age }}歳</p>
                    @endif
                </div>
            </div>
            @if($cast->castType)
            <p class="text-xs text-[#6A6A7E] text-center">{{ $cast->castType->name }}</p>
            @endif
            @if($cast->shop)
            <p class="text-xs text-[#5A5A7E] text-center truncate">{{ $cast->shop->name }}</p>
            @endif
        </a>
        @empty
        <div class="col-span-5 text-center py-16 text-[#6A6A7E]">
            <p>該当するキャストが見つかりませんでした</p>
        </div>
        @endforelse
    </div>

    <div class="[&_.pagination]:flex [&_.pagination]:gap-1 [&_a]:bg-surface-500 [&_a]:border [&_a]:border-surface-300 [&_a]:text-[#B0AEAD] [&_a:hover]:border-deli-500 [&_a:hover]:text-deli-400 [&_a]:px-3 [&_a]:py-1.5 [&_a]:rounded [&_a]:text-sm [&_span.current]:bg-deli-500 [&_span.current]:text-white [&_span.current]:border-deli-500 [&_span.current]:px-3 [&_span.current]:py-1.5 [&_span.current]:rounded [&_span.current]:text-sm">
        {{ $casts->appends(request()->query())->links() }}
    </div>
</div>
@endsection
