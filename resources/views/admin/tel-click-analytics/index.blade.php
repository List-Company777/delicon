@extends('layouts.admin')

@section('title', '電話クリック分析')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-700">電話クリック分析</h1>
    <div class="flex gap-2">
        @foreach([7 => '7日', 30 => '30日', 90 => '90日'] as $days => $label)
        <a href="?period={{ $days }}"
           class="px-3 py-1.5 rounded text-sm font-medium transition
                  {{ $period === $days ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- 都道府県別 --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-sm font-bold text-gray-600 mb-4">都道府県別</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-400 border-b">
                    <th class="text-left pb-2">都道府県</th>
                    <th class="text-right pb-2">件数</th>
                    <th class="text-right pb-2">割合</th>
                </tr>
            </thead>
            <tbody>
                @php $totalPref = $byPref->sum('cnt'); @endphp
                @forelse($byPref as $row)
                <tr class="border-b border-gray-50">
                    <td class="py-2 text-gray-700">{{ $row->name }}</td>
                    <td class="py-2 text-right font-bold text-gray-800">{{ number_format($row->cnt) }}</td>
                    <td class="py-2 text-right text-gray-400">
                        {{ $totalPref > 0 ? number_format($row->cnt / $totalPref * 100, 1) : 0 }}%
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="py-4 text-center text-gray-400">データなし</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 系統別 --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-sm font-bold text-gray-600 mb-4">系統別</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-400 border-b">
                    <th class="text-left pb-2">系統</th>
                    <th class="text-right pb-2">件数</th>
                    <th class="text-right pb-2">割合</th>
                </tr>
            </thead>
            <tbody>
                @php $totalType = $byType->sum('cnt'); @endphp
                @forelse($byType as $row)
                <tr class="border-b border-gray-50">
                    <td class="py-2 text-gray-700">{{ $row->name }}</td>
                    <td class="py-2 text-right font-bold text-gray-800">{{ number_format($row->cnt) }}</td>
                    <td class="py-2 text-right text-gray-400">
                        {{ $totalType > 0 ? number_format($row->cnt / $totalType * 100, 1) : 0 }}%
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="py-4 text-center text-gray-400">データなし</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 年齢層別 --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-sm font-bold text-gray-600 mb-4">年齢層別</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-400 border-b">
                    <th class="text-left pb-2">年齢層</th>
                    <th class="text-right pb-2">件数</th>
                    <th class="text-right pb-2">割合</th>
                </tr>
            </thead>
            <tbody>
                @php $totalAge = $byAge->sum('cnt'); @endphp
                @forelse($byAge as $row)
                <tr class="border-b border-gray-50">
                    <td class="py-2 text-gray-700">{{ $row->age_group }}</td>
                    <td class="py-2 text-right font-bold text-gray-800">{{ number_format($row->cnt) }}</td>
                    <td class="py-2 text-right text-gray-400">
                        {{ $totalAge > 0 ? number_format($row->cnt / $totalAge * 100, 1) : 0 }}%
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="py-4 text-center text-gray-400">データなし</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- カップ別 --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-sm font-bold text-gray-600 mb-4">カップ別</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-400 border-b">
                    <th class="text-left pb-2">カップ</th>
                    <th class="text-right pb-2">件数</th>
                    <th class="text-right pb-2">割合</th>
                </tr>
            </thead>
            <tbody>
                @php $totalCup = $byCup->sum('cnt'); @endphp
                @forelse($byCup as $row)
                <tr class="border-b border-gray-50">
                    <td class="py-2 text-gray-700">{{ $row->name }}カップ</td>
                    <td class="py-2 text-right font-bold text-gray-800">{{ number_format($row->cnt) }}</td>
                    <td class="py-2 text-right text-gray-400">
                        {{ $totalCup > 0 ? number_format($row->cnt / $totalCup * 100, 1) : 0 }}%
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="py-4 text-center text-gray-400">データなし</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
