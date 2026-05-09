@extends('layouts.manage')
@section('title', 'シフト申請')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-[#F0ECE4] mb-2">シフト申請</h1>
    <p class="text-xs text-gray-400 mb-6">在籍女性から届いたシフト申請を確認・承認できます。承認するとシフトに自動反映されます。</p>

    @if(session('success'))
    <div class="mb-4 bg-emerald-100 border border-emerald-300 text-emerald-800 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- 承認待ち --}}
    <div class="mb-8">
        <h2 class="text-sm font-bold text-gray-300 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>
            承認待ち
            @if($pending->isNotEmpty())
            <span class="bg-amber-400 text-amber-900 text-xs font-black px-1.5 py-0.5 rounded-full">{{ $pending->count() }}</span>
            @endif
        </h2>

        @if($pending->isEmpty())
        <p class="text-sm text-gray-400 py-6 text-center bg-white/5 rounded-xl border border-white/10">承認待ちのシフト申請はありません</p>
        @else
        <div class="space-y-2">
            @foreach($pending as $req)
            <div class="bg-white border border-gray-200 rounded-xl p-4 flex flex-wrap items-center gap-3 justify-between">
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-100 shrink-0">
                        <img src="{{ $req->cast->img_url }}" alt="" class="img-onerror-cast w-full h-full object-cover">
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800">{{ $req->cast->name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $req->work_date->format('m月d日(') }}{{ ['日','月','火','水','木','金','土'][$req->work_date->dayOfWeek] }}{{ ')' }}
                            @if($req->start_time || $req->end_time)
                            　{{ substr($req->start_time ?? '', 0, 5) }}〜{{ substr($req->end_time ?? '', 0, 5) }}
                            @endif
                            @if($req->note) <span class="text-gray-400">{{ $req->note }}</span> @endif
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('manage.shift-requests.approve', $req->id) }}/">
                        @csrf @method('PATCH')
                        <button class="text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 px-4 py-1.5 rounded-lg font-bold transition">承認</button>
                    </form>
                    <form method="POST" action="{{ route('manage.shift-requests.reject', $req->id) }}/"
                          onsubmit="return confirm('この申請を却下しますか？')">
                        @csrf @method('PATCH')
                        <button class="text-xs bg-red-50 hover:bg-red-100 text-red-500 border border-red-200 px-4 py-1.5 rounded-lg transition">却下</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- 直近7日の処理済み --}}
    @if($recent->isNotEmpty())
    <div>
        <h2 class="text-sm font-bold text-gray-300 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-gray-400 inline-block"></span>
            処理済み（直近7日）
        </h2>
        <div class="space-y-2">
            @foreach($recent as $req)
            <div class="bg-white/5 border border-white/10 rounded-xl p-3 flex flex-wrap items-center gap-3 justify-between opacity-70">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full overflow-hidden bg-gray-100 shrink-0">
                        <img src="{{ $req->cast->img_url }}" alt="" class="img-onerror-cast w-full h-full object-cover">
                    </div>
                    <p class="text-xs text-gray-300">
                        {{ $req->cast->name }} ／
                        {{ $req->work_date->format('m/d') }}
                        @if($req->start_time) {{ substr($req->start_time, 0, 5) }}〜{{ substr($req->end_time ?? '', 0, 5) }} @endif
                    </p>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $req->isApproved() ? 'bg-emerald-900/50 text-emerald-400' : 'bg-red-900/30 text-red-400' }}">
                    {{ $req->isApproved() ? '承認済' : '却下' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
