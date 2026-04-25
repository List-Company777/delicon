@extends('layouts.app')
@section('title', 'キャスト求人')
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
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    @php $castCount = $castJobs->count(); $maxJobs = $shop->castJobLimit(); @endphp
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-800">キャスト求人</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ $castCount }} / {{ $maxJobs }} 件（{{ $shop->hasBudget() ? '有料プラン' : '無料プラン' }}）</p>
        </div>
        @if($castCount < $maxJobs)
            <a href="{{ route('manage.cast.create') }}"
               class="bg-business-700 hover:bg-business-600 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
                + 求人を追加
            </a>
        @else
            <span class="text-xs text-gray-400 border border-gray-200 rounded-lg px-4 py-2">上限（{{ $maxJobs }}件）に達しています</span>
        @endif
    </div>

    @if($castJobs->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
            <p>キャスト求人がありません</p>
            <a href="{{ route('manage.cast.create') }}" class="mt-3 inline-block text-sm text-business-700 hover:underline">求人を追加する →</a>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            @foreach($castJobs as $job)
                <div class="flex items-center gap-4 px-4 py-3 border-b border-gray-100 last:border-0">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 text-sm truncate">{{ $job->title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $job->jobType?->name }}</p>
                    </div>
                    <span @class([
                        'text-xs px-2 py-0.5 rounded-full whitespace-nowrap',
                        'bg-green-100 text-green-700' => $job->status === 'active',
                        'bg-yellow-100 text-yellow-700' => $job->status === 'draft',
                        'bg-gray-100 text-gray-400'   => $job->status === 'inactive',
                    ])>
                        {{ ['active'=>'公開中','inactive'=>'非公開','draft'=>'下書き'][$job->status] }}
                    </span>
                    <a href="{{ route('manage.cast.edit', $job->id) }}"
                       class="text-xs text-business-700 hover:underline whitespace-nowrap">編集</a>
                    <form action="{{ route('manage.cast.destroy', $job->id) }}" method="POST"
                          onsubmit="return confirm('この求人を削除しますか？')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600">削除</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
