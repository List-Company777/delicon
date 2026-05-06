@extends('layouts.app')
@section('title', 'キャスト編集')
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

<div class="max-w-2xl mx-auto px-4 pb-12">
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('manage.cast-profile.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← キャスト一覧</a>
        <h2 class="text-lg font-bold text-gray-800">{{ $cast->name }} を編集</h2>
        <a href="{{ route('cast-diary.index', $cast->id) }}/" class="text-sm bg-deli-500 hover:bg-deli-400 text-white px-3 py-1.5 rounded-lg transition">写メ日記</a>
    </div>

    <form action="{{ route('manage.cast-profile.update', $cast->id) }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm overflow-hidden">
        @csrf @method('PUT')
        @include('manage.cast_profile._form', ['cast' => $cast])
        <div class="px-4 py-4 bg-gray-50 border-t border-gray-100 text-right">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold px-6 py-2 rounded-lg transition">
                保存する
            </button>
        </div>
    </form>
</div>
@endsection
