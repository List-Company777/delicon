@extends('layouts.app')
@section('title', 'キャスト求人の編集')
@section('content')
<div class="bg-business-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold">店舗管理</h1>
        <form action="{{ route('logout') }}" method="POST">@csrf<button class="text-sm opacity-70 hover:opacity-100">ログアウト</button></form>
    </div>
</div>

@include('manage._nav')

<div class="max-w-4xl mx-auto px-4 pb-12">
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <h2 class="text-lg font-bold text-gray-800 mb-6">キャスト求人</h2>

    <form action="{{ route('manage.cast.update') }}" method="POST" class="bg-white rounded-xl shadow-sm overflow-hidden">
        @csrf @method('PUT')
        @include('manage.cast._form', ['job' => $job, 'jobTypes' => $jobTypes])
        <div class="px-4 py-4 bg-gray-50 border-t border-gray-100 text-right">
            <button type="submit" class="bg-business-700 hover:bg-business-600 text-white text-sm font-bold px-6 py-2 rounded-lg transition">
                保存する
            </button>
        </div>
    </form>
</div>
@endsection
