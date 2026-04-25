@extends('layouts.app')

@section('title', '応募管理')

@section('content')
<div class="bg-business-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold text-lg">店舗管理</h1>
        <div class="flex items-center gap-4 text-sm">
            <span class="opacity-70">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="opacity-70 hover:opacity-100 transition">ログアウト</button>
            </form>
        </div>
    </div>
</div>

@include('manage._nav')

<div class="max-w-4xl mx-auto px-4 py-8">

    <h2 class="text-lg font-bold text-gray-800 mb-6">応募管理</h2>

    @if($applications->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
            <p class="text-sm">まだ応募はありません</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($applications as $app)
            @php
                $unread = $app->messages->where('sender', 'applicant')->whereNull('read_at')->count();
                $statusLabel = match($app->status) {
                    'new'       => ['新規', 'bg-blue-100 text-blue-700'],
                    'contacted' => ['連絡済み', 'bg-yellow-100 text-yellow-700'],
                    'hired'     => ['採用', 'bg-green-100 text-green-700'],
                    'rejected'  => ['不採用', 'bg-gray-100 text-gray-500'],
                    default     => [$app->status, 'bg-gray-100 text-gray-500'],
                };
            @endphp
            <a href="{{ route('manage.applications.show', $app->id) }}"
               class="block bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-bold text-gray-800">{{ $app->applicant_name }}</span>
                            @if($app->applicant_age)
                                <span class="text-xs text-gray-400">{{ $app->applicant_age }}歳</span>
                            @endif
                            @if($unread > 0)
                                <span class="text-xs bg-red-500 text-white rounded-full px-2 py-0.5 font-bold">
                                    返信{{ $unread }}件
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 truncate">
                            {{ $app->job ? ($app->job->jobType?->name ?? $app->job->title) . ($app->job->employment_type ? '（' . ['PART_TIME'=>'アルバイト','CONTRACTOR'=>'業務委託','FULL_TIME'=>'正社員','PER_DIEM'=>'日払い','OTHER'=>'その他'][$app->job->employment_type] . '）' : '') : '（求人削除済み）' }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">{{ $app->created_at->format('Y/m/d H:i') }}</p>
                    </div>
                    <span @class(['text-xs px-2 py-1 rounded-full font-medium shrink-0', $statusLabel[1]])>
                        {{ $statusLabel[0] }}
                    </span>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $applications->links() }}
        </div>
    @endif
</div>
@endsection
