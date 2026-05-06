@extends('layouts.app')
@section('title', '在籍キャスト管理')
@section('content')
<div class="bg-red-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold">店舗管理</h1>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="text-sm opacity-70 hover:opacity-100">ログアウト</button>
        </form>
    </div>
</div>

@include('manage._nav')

<div class="max-w-4xl mx-auto px-4 pb-12">
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold text-gray-800">在籍キャスト一覧</h2>
        <a href="{{ route('manage.cast-profile.create') }}"
           class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
            ＋ キャストを追加
        </a>
    </div>

    @if($casts->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400 text-sm">
            在籍キャストはまだ登録されていません。
        </div>
    @else
        <div class="grid gap-3">
            @foreach($casts as $cast)
            <div class="bg-white rounded-xl shadow-sm p-4 flex items-center gap-4">
                <img src="{{ $cast->img_url }}" alt="{{ $cast->name }}"
                     class="w-14 h-20 object-cover rounded-lg bg-gray-100 shrink-0">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-bold text-gray-800">{{ $cast->name }}</span>
                        @if($cast->is_recommended)
                            <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-medium">おすすめ</span>
                        @endif
                        <span @class([
                            'text-xs px-2 py-0.5 rounded-full font-medium',
                            'bg-green-100 text-green-700' => $cast->status === 'active',
                            'bg-gray-100 text-gray-500' => $cast->status !== 'active',
                        ])>{{ $cast->status === 'active' ? '公開中' : '非公開' }}</span>
                    </div>
                    <p class="text-xs text-gray-500">
                        @if($cast->age) {{ $cast->age }}歳 @endif
                        @if($cast->tall) {{ $cast->tall }}cm @endif
                        @if($cast->bust) B{{ $cast->bust }} @endif
                        @if($cast->cup) {{ $cast->cup }}カップ @endif
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('manage.cast-profile.edit', $cast->id) }}"
                       class="text-xs border border-gray-300 text-gray-600 hover:bg-gray-50 px-3 py-1.5 rounded transition">編集</a>
                    <form action="{{ route('manage.cast-profile.destroy', $cast->id) }}" method="POST"
                          onsubmit="return confirm('このキャストを削除しますか？')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs border border-red-200 text-red-500 hover:bg-red-50 px-3 py-1.5 rounded transition">削除</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
