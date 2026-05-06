@extends('layouts.admin')

@section('title', '有料プラン申し込み審査')

@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-700">有料プラン申し込み審査</h1>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
        {{ session('success') }}
    </div>
@endif

{{-- タブ --}}
<div class="flex gap-1 mb-6 border-b border-gray-200">
    @foreach(['pending' => '審査待ち', 'approved' => '承認済み', 'rejected' => '却下', 'all' => 'すべて'] as $s => $label)
    <a href="{{ route('admin.plan-applications.index', ['status' => $s]) }}/"
       class="{{ $status === $s
           ? 'border-b-2 border-yellow-500 text-yellow-600 font-bold'
           : 'text-gray-500 hover:text-gray-700' }}
          px-4 py-2 text-sm transition -mb-px whitespace-nowrap">
        {{ $label }}
        <span class="ml-1 text-xs {{ $status === $s ? 'text-yellow-500' : 'text-gray-400' }}">
            {{ number_format($counts[$s]) }}
        </span>
    </a>
    @endforeach
</div>

@if($applications->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center text-gray-400">
        <p>該当する申し込みはありません</p>
    </div>
@else
<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-10">ID</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">店舗名</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">代理店</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-28">申込金額</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-28">希望入札単価</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-28">申込日時</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-20">状態</th>
                <th class="px-4 py-3 w-64"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($applications as $application)
            @php $shop = $application->shop; @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $application->id }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">
                    {{ $shop->name ?? '—' }}
                    @php $owner = $shop->users->first(); @endphp
                    @if($owner)
                        <br><span class="text-xs text-gray-400">{{ $owner->email }}</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    {{ $shop->partner?->company_name ?? '—' }}
                </td>
                <td class="px-4 py-3 text-gray-800 font-medium">
                    {{ number_format($application->amount) }}円
                </td>
                <td class="px-4 py-3 text-gray-800">
                    {{ number_format($application->bid_price_requested) }}円
                </td>
                <td class="px-4 py-3 text-xs text-gray-400">
                    {{ $application->created_at->format('Y/m/d H:i') }}
                </td>
                <td class="px-4 py-3">
                    @if($application->status === 'pending')
                        <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700">審査待ち</span>
                    @elseif($application->status === 'approved')
                        <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">承認済み</span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-600">却下</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if($application->status === 'pending')
                        <div class="flex items-center gap-2">
                            {{-- 承認 --}}
                            <form action="{{ route('admin.plan-applications.approve', $application) }}/" method="POST"
                                  x-data="{ open: false }" @submit.prevent="open ? $el.submit() : (open = true)">
                                @csrf
                                <div x-show="open" class="mb-2 space-y-1" x-cloak>
                                    <input type="text" name="plan_name"
                                           placeholder="nightwork-list 広告掲載料 4月1日～"
                                           class="border border-gray-300 rounded px-2 py-1 text-xs w-56 focus:outline-none">
                                    <p class="text-xs text-gray-400">品目名（invoy請求書に記載されます）</p>
                                </div>
                                <button type="submit"
                                        class="text-xs px-3 py-1.5 bg-green-600 hover:bg-green-500 text-white rounded transition">
                                    <span x-text="open ? '確定' : '承認'">承認</span>
                                </button>
                            </form>

                            {{-- 却下（メモ付き） --}}
                            <form action="{{ route('admin.plan-applications.reject', $application) }}/" method="POST"
                                  x-data="{ open: false }" @submit.prevent="open ? $el.submit() : (open = true)">
                                @csrf
                                <div x-show="open" class="mb-2" x-cloak>
                                    <input type="text" name="note" placeholder="却下理由（任意）"
                                           class="border border-gray-300 rounded px-2 py-1 text-xs w-40 focus:outline-none">
                                </div>
                                <button type="submit"
                                        class="text-xs px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded transition">
                                    <span x-text="open ? '確定' : '却下'">却下</span>
                                </button>
                            </form>
                        </div>
                    @elseif($application->status === 'approved')
                        <p class="text-xs text-gray-400">承認日：{{ $application->approved_at?->format('Y/m/d') }}</p>
                    @elseif($application->note)
                        <p class="text-xs text-gray-400">{{ $application->note }}</p>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $applications->appends(request()->query())->links() }}
</div>
@endif

@endsection
