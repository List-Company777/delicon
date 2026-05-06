@extends('layouts.app')

@section('title', 'キャスト検索')
@section('description', '全国のデリヘル・風俗店に在籍するキャストを検索。タイプ・年齢・スタイルなど条件から探せます。')
@section('canonical', route('cast.index') . '/')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    <h1 class="text-2xl font-bold text-gray-800 mb-6">キャスト検索</h1>

    {{-- 絞り込みフォーム --}}
    <form method="get" action="{{ route('cast.index') }}/" class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">タイプ</label>
                <select name="type" class="border rounded px-2 py-1.5 text-sm">
                    <option value="">すべて</option>
                    @foreach($castTypes as $ct)
                        <option value="{{ $ct->id }}" @selected(request('type') == $ct->id)>{{ $ct->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">年齢</label>
                <div class="flex items-center gap-1">
                    <input type="number" name="age_from" value="{{ request('age_from') }}" placeholder="下限" min="18" max="60"
                           class="border rounded px-2 py-1.5 text-sm w-16">
                    <span class="text-gray-400 text-sm">〜</span>
                    <input type="number" name="age_to" value="{{ request('age_to') }}" placeholder="上限" min="18" max="60"
                           class="border rounded px-2 py-1.5 text-sm w-16">
                    <span class="text-gray-400 text-sm">歳</span>
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">カップ</label>
                <select name="cup" class="border rounded px-2 py-1.5 text-sm">
                    <option value="">指定なし</option>
                    @foreach(['A','B','C','D','E','F','G','H','I'] as $c)
                        <option value="{{ $c }}" @selected(request('cup') === $c)>{{ $c }}カップ</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-red-600 text-white px-4 py-1.5 rounded text-sm hover:bg-red-700 transition">検索</button>
            @if(request()->hasAny(['type','age_from','age_to','cup']))
            <a href="{{ route('cast.index') }}/" class="text-xs text-gray-400 hover:text-gray-600">リセット</a>
            @endif
        </div>
    </form>

    <p class="text-sm text-gray-500 mb-4">{{ $casts->total() }}名</p>

    {{-- キャストグリッド --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-8">
        @forelse($casts as $cast)
        <a href="{{ route('cast.show', $cast->id) }}/" class="group text-center">
            <div class="relative overflow-hidden rounded-lg mb-2">
                <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                     class="w-full aspect-[3/4] object-cover group-hover:scale-105 transition-transform duration-200"
                     loading="lazy" onerror="this.src='/img/no-cast.jpg'">
                @if($cast->is_recommended)
                <span class="absolute top-1 right-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded">おすすめ</span>
                @endif
            </div>
            <p class="text-xs font-medium group-hover:text-red-600 transition">{{ $cast->name }}</p>
            @if($cast->age)
            <p class="text-xs text-gray-500">{{ $cast->age }}歳</p>
            @endif
            @if($cast->castType)
            <p class="text-xs text-gray-400">{{ $cast->castType->name }}</p>
            @endif
            @if($cast->shop)
            <p class="text-xs text-gray-400 truncate">{{ $cast->shop->name }}</p>
            @endif
        </a>
        @empty
        <div class="col-span-5 text-center py-16 text-gray-400">
            <p>該当するキャストが見つかりませんでした</p>
        </div>
        @endforelse
    </div>

    {{ $casts->appends(request()->query())->links() }}
</div>
@endsection
