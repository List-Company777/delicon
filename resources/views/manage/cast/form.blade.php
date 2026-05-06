@extends('layouts.app')
@section('title', $job ? 'キャスト求人の編集' : 'キャスト求人の追加')
@section('content')
<div class="bg-business-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold">店舗管理</h1>
        <form action="{{ route('logout') }}/" method="POST">@csrf<button class="text-sm opacity-70 hover:opacity-100">ログアウト</button></form>
    </div>
</div>

@include('manage._nav')

<div class="max-w-4xl mx-auto px-4 pb-12">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('manage.cast.index') }}/" class="text-sm text-gray-400 hover:text-gray-600">← キャスト求人一覧</a>
        <h2 class="text-lg font-bold text-gray-800">{{ $job ? '求人を編集' : '求人を追加' }}</h2>
    </div>

    @if($job)
        <form action="{{ route('manage.cast.update', $job->id) }}/" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm overflow-hidden">
            @csrf @method('PUT')
    @else
        <form action="{{ route('manage.cast.store') }}/" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm overflow-hidden">
            @csrf
    @endif
        @include('manage.cast._form', ['job' => $job, 'jobTypes' => $jobTypes])
        <div class="px-4 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
            <a href="{{ route('manage.cast.index') }}/" class="text-sm text-gray-400 hover:text-gray-600">キャンセル</a>
            <button type="submit" class="bg-business-700 hover:bg-business-600 text-white text-sm font-bold px-6 py-2 rounded-lg transition">
                {{ $job ? '更新する' : '追加する' }}
            </button>
        </div>
    </form>
</div>
@endsection
