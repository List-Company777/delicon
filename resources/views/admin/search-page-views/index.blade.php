@extends('layouts.admin')
@section('title', '検索PV分析')

@php
$genderLabel = ['business' => '夜遊び', 'male' => '男性', 'female' => '女性'];
$genderColor = [
    'business' => 'bg-amber-100 text-amber-700',
    'male'     => 'bg-blue-100 text-blue-700',
    'female'   => 'bg-pink-100 text-pink-700',
];
@endphp

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-700">検索PV分析</h1>
    <p class="text-xs text-gray-400">正規化ページごとの閲覧数（過去 {{ $days }} 日間）</p>
</div>

{{-- フィルター --}}
<div class="flex flex-wrap items-center gap-3 mb-6">
    {{-- 期間 --}}
    <div class="flex gap-1 bg-white border border-gray-200 rounded-lg p-1 text-xs">
        @foreach([7 => '7日', 30 => '30日', 90 => '90日'] as $d => $label)
        <a href="{{ request()->fullUrlWithQuery(['days' => $d]) }}"
           class="px-3 py-1 rounded {{ $days == $d ? 'bg-gray-800 text-white' : 'text-gray-500 hover:bg-gray-50' }} transition">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- 性別 --}}
    <div class="flex gap-1 bg-white border border-gray-200 rounded-lg p-1 text-xs">
        <a href="{{ request()->fullUrlWithQuery(['gender' => 'all']) }}"
           class="px-3 py-1 rounded {{ $gender === 'all' ? 'bg-gray-800 text-white' : 'text-gray-500 hover:bg-gray-50' }} transition">
            全体
        </a>
        @foreach(['business' => '夜遊び', 'male' => '男性', 'female' => '女性'] as $g => $lbl)
        <a href="{{ request()->fullUrlWithQuery(['gender' => $g]) }}"
           class="px-3 py-1 rounded {{ $gender === $g ? 'bg-gray-800 text-white' : 'text-gray-500 hover:bg-gray-50' }} transition">
            {{ $lbl }}
        </a>
        @endforeach
    </div>

    <span class="text-xs text-gray-500 ml-auto">
        合計 <span class="font-bold text-gray-700">{{ number_format($totalPv) }}</span> PV
        ／ {{ $rows->count() }} ページ
    </span>
</div>

{{-- テーブル --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500">
            <tr>
                <th class="text-left px-4 py-3 font-medium w-8">#</th>
                <th class="text-left px-4 py-3 font-medium">カテゴリ</th>
                <th class="text-left px-4 py-3 font-medium">エリア</th>
                <th class="text-left px-4 py-3 font-medium">職種 / ジャンル</th>
                <th class="text-right px-4 py-3 font-medium">PV数</th>
                <th class="px-4 py-3 w-48"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($rows as $i => $row)
            @php
                $maxTotal = $rows->first()?->total ?? 1;
                $barWidth = round($row->total / $maxTotal * 100);
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $i + 1 }}</td>
                <td class="px-4 py-2.5">
                    <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium {{ $genderColor[$row->gender] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $genderLabel[$row->gender] ?? $row->gender }}
                    </span>
                </td>
                <td class="px-4 py-2.5 text-gray-700">
                    {{ $row->area_name ?? ($row->area_slug === 'all' ? '全国' : $row->area_slug) }}
                </td>
                <td class="px-4 py-2.5 text-gray-700">
                    {{ $row->job_name ?? ($row->job_slug === 'all' ? '全職種' : $row->job_slug) }}
                </td>
                <td class="px-4 py-2.5 text-right font-bold text-gray-800">
                    {{ number_format($row->total) }}
                </td>
                <td class="px-4 py-2.5">
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-yellow-400 h-1.5 rounded-full" style="width: {{ $barWidth }}%"></div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-12 text-center text-gray-400">データがありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
