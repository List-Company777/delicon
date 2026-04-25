@extends('layouts.app')

@section('title', '応募完了 | ' . $job->shop->name)
@section('description', $job->shop->name . 'へのご応募ありがとうございます。')
@section('robots', 'noindex, follow')

@section('content')

<div class="max-w-2xl mx-auto px-4 py-16 text-center">

    {{-- 完了アイコン --}}
    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <h1 class="text-2xl font-bold text-gray-800 mb-3">応募が完了しました</h1>
    <p class="text-gray-500 text-sm mb-8">
        ご応募ありがとうございます。<br>
        ご登録のメールアドレスに確認メールをお送りしました。
    </p>

    {{-- 応募先サマリ --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-8 text-left">
        <p class="text-xs text-gray-400 mb-2">応募した求人</p>
        <p class="font-bold text-gray-800 mb-1">{{ $job->title }}</p>
        <p class="text-sm text-gray-500">{{ $job->shop->name }}</p>
    </div>

    {{-- 次のアクション --}}
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 mb-10 text-left text-sm text-gray-600 leading-relaxed">
        <p class="font-bold text-yellow-700 mb-2">この後の流れ</p>
        <ol class="list-decimal list-inside space-y-1">
            <li>店舗担当者がご応募内容を確認します</li>
            <li>メールまたはお電話にてご連絡いたします</li>
            <li>面接日程を調整後、ご来店いただきます</li>
        </ol>
    </div>

    {{-- ボタン群 --}}
    <div class="flex flex-col sm:flex-row gap-3 justify-center mb-10">
        <a href="{{ route('top') }}"
           class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-3 px-8 rounded-xl transition">
            トップページへ戻る
        </a>
        <a href="{{ route('search', ['gender' => $job->search_group === 'male' ? 'male' : 'female']) }}"
           class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-3 px-8 rounded-xl transition">
            他の求人を見る
        </a>
    </div>

</div>

{{-- 関連求人（複数応募を促す） --}}
@if($relatedJobs->isNotEmpty())
@php
    $applyColor = match($job->search_group) {
        'male'  => ['border' => 'border-male-200',   'bg' => 'bg-male-50',   'hover' => 'hover:bg-male-100',   'text' => 'text-male-600',   'arrow' => 'text-male-400'],
        default => ['border' => 'border-female-100', 'bg' => 'bg-female-50', 'hover' => 'hover:bg-female-100', 'text' => 'text-female-600', 'arrow' => 'text-female-400'],
    };
@endphp
<div class="max-w-2xl mx-auto px-4 pb-16">
    <h2 class="text-sm font-bold text-gray-600 mb-3 text-center">こちらの求人もいかがですか？</h2>
    <div class="space-y-2">
        @foreach($relatedJobs as $rJob)
        <a href="{{ url('/track/job/' . $rJob->id) . '/' }}"
           rel="nofollow"
           class="flex items-center justify-between p-3 rounded-xl border {{ $applyColor['border'] }} {{ $applyColor['bg'] }} {{ $applyColor['hover'] }} transition group">
            <div class="min-w-0">
                <p class="text-xs {{ $applyColor['text'] }} font-medium">
                    {{ $rJob->jobType?->name ?? '求人' }} &nbsp;·&nbsp; {{ $rJob->shop->area?->name ?? '' }}
                </p>
                <p class="text-sm font-bold text-gray-800 truncate">{{ $rJob->title }}</p>
                <p class="text-xs text-gray-500 truncate">{{ $rJob->shop->name }}</p>
            </div>
            <span class="{{ $applyColor['arrow'] }} ml-3 shrink-0 group-hover:translate-x-0.5 transition-transform">›</span>
        </a>
        @endforeach
    </div>
</div>
@endif

{{-- 最近見た求人（読み取りのみ・登録はjob/showで完了済み） --}}
<div x-data="recentlyViewedComplete()" x-init="init()" class="max-w-2xl mx-auto px-4 pb-16" x-show="items.length > 0" x-cloak>
    <h2 class="text-sm font-bold text-gray-600 mb-3 text-center">最近見た求人</h2>
    <div class="flex gap-3 overflow-x-auto pb-2">
        <template x-for="item in items" :key="item.id">
            <a :href="item.url" class="shrink-0 w-36 bg-white border border-gray-200 rounded-xl p-3 hover:shadow-sm transition">
                <p class="text-xs text-gray-400 mb-1" x-text="item.type"></p>
                <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-tight mb-1" x-text="item.title"></p>
                <p class="text-xs text-gray-400 truncate" x-text="item.shop"></p>
            </a>
        </template>
    </div>
</div>

@endsection

@push('scripts')
<script>
function recentlyViewedComplete() {
    const GROUP_KEY = {
        female: 'nw_recent_jobs_female',
        male:   'nw_recent_jobs_male',
        both:   'nw_recent_jobs_business',
    };
    const key = GROUP_KEY[@json($job->search_group)] || 'nw_recent_jobs_female';
    const currentId = {{ $job->id }};

    return {
        items: [],
        init() {
            let list = [];
            try { list = JSON.parse(localStorage.getItem(key) || '[]'); } catch(e) {}
            this.items = list.filter(i => i.id !== currentId).slice(0, 6);
        }
    };
}
</script>
@endpush
