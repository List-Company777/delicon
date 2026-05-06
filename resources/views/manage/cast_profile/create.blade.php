@extends('layouts.app')
@section('title', 'キャスト追加')
@section('content')
<div class="bg-red-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold">店舗管理</h1>
        <form action="{{ route('logout') }}/" method="POST">
            @csrf
            <button class="text-sm opacity-70 hover:opacity-100">ログアウト</button>
        </form>
    </div>
</div>

@include('manage._nav')

<div class="max-w-2xl mx-auto px-4 pb-12">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('manage.cast-profile.index') }}/" class="text-sm text-gray-500 hover:text-gray-700">← キャスト一覧</a>
        <h2 class="text-lg font-bold text-gray-800">キャストを追加</h2>
    </div>

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($limit !== null)
    <div class="mb-4 bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-xs text-gray-600">
        在籍登録数：<span class="font-bold {{ $currentCount >= $limit ? 'text-red-600' : 'text-gray-800' }}">{{ $currentCount }} / {{ $limit }}人</span>
        @if($currentCount >= $limit)
        　<span class="text-red-600 font-semibold">上限に達しています</span>
        @endif
    </div>
    @endif

    <form action="{{ route('manage.cast-profile.store') }}/" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm overflow-hidden">
        @csrf
        @include('manage.cast_profile._form', ['cast' => null])
        <div class="px-4 py-4 bg-gray-50 border-t border-gray-100 text-right">
            <button type="submit"
                    @if($limit !== null && $currentCount >= $limit) disabled @endif
                    class="text-white text-sm font-bold px-6 py-2 rounded-lg {{ ($limit !== null && $currentCount >= $limit) ? 'bg-gray-400 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700' }} transition">
                登録する
            </button>
        </div>
    </form>
</div>
@endsection
