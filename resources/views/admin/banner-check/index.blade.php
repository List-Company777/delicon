@extends('layouts.admin')

@section('title', 'バナー確認（HP一覧）')

@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-700">バナー確認（HP一覧）</h1>
    <p class="text-sm text-gray-400">バナー設置によるベーシック・無料上位の店舗一覧</p>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-10">ID</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">店舗名</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">エリア</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">現在のプラン</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500">HP URL</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-28">最終確認</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-64">プラン変更</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($shops as $shop)
            @php
                $planLabels = [
                    1 => ['label' => 'VIP',       'color' => 'bg-yellow-100 text-yellow-700'],
                    2 => ['label' => 'ミドル',    'color' => 'bg-purple-100 text-purple-700'],
                    3 => ['label' => 'ベーシック', 'color' => 'bg-blue-100 text-blue-700'],
                    4 => ['label' => '無料上位',  'color' => 'bg-green-100 text-green-700'],
                    5 => ['label' => '無料',      'color' => 'bg-gray-100 text-gray-500'],
                ];
                $planInfo   = $planLabels[$shop->plan] ?? $planLabels[5];
                $websiteUrl = $shop->detail?->website_url;
                $checkedAt  = $shop->banner_checked_at;
            @endphp
            <tr class="hover:bg-gray-50 transition {{ $checkedAt ? '' : 'bg-red-50/30' }}">
                <td class="px-4 py-3 text-gray-400 text-xs">{{ $shop->id }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">
                    <a href="{{ route('admin.shops.show', $shop->id) }}/" class="hover:underline">
                        {{ $shop->name }}
                    </a>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    {{ $shop->area?->prefecture?->prefecture ?? '—' }}
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $planInfo['color'] }}">
                        {{ $planInfo['label'] }}
                        @if($shop->plan === 3 && $shop->is_banner_plan)
                            <span class="text-gray-400">（バナー）</span>
                        @endif
                    </span>
                </td>
                <td class="px-4 py-3 text-xs">
                    @if($websiteUrl)
                        <a href="{{ $websiteUrl }}" target="_blank" rel="noopener"
                           class="text-blue-600 hover:underline break-all">
                            {{ $websiteUrl }}
                        </a>
                    @else
                        <span class="text-gray-300">未設定</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs">
                    @if($checkedAt)
                        <span class="text-gray-500">{{ $checkedAt->format('m/d') }}</span>
                        <span class="block text-gray-300 text-[10px]">{{ $checkedAt->format('H:i') }}</span>
                    @else
                        <span class="text-red-400 font-medium">未確認</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1.5">
                        <form action="{{ route('admin.shops.updatePlan', $shop->id) }}/" method="POST"
                              class="flex items-center gap-1.5">
                            @csrf @method('PATCH')
                            <select name="plan"
                                    class="border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:border-yellow-400">
                                <option value="1" {{ $shop->plan == 1 ? 'selected' : '' }}>VIP</option>
                                <option value="2" {{ $shop->plan == 2 ? 'selected' : '' }}>ミドル</option>
                                <option value="3" {{ $shop->plan == 3 ? 'selected' : '' }}>ベーシック</option>
                                <option value="4" {{ $shop->plan == 4 ? 'selected' : '' }}>無料上位</option>
                                <option value="5" {{ $shop->plan == 5 ? 'selected' : '' }}>無料</option>
                            </select>
                            <input type="hidden" name="is_banner_plan" value="{{ $shop->is_banner_plan ? 1 : 0 }}">
                            <button type="submit"
                                    class="text-xs px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded transition">
                                変更
                            </button>
                        </form>
                        <form action="{{ route('admin.banner-check.check', $shop->id) }}/" method="POST">
                            @csrf
                            <button type="submit"
                                    class="text-xs px-2 py-1 {{ $checkedAt ? 'bg-gray-200 hover:bg-gray-300 text-gray-600' : 'bg-green-600 hover:bg-green-500 text-white' }} rounded transition">
                                OK
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                    該当する店舗はありません
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<p class="text-xs text-gray-400 mt-3">{{ $shops->count() }} 件</p>

@endsection
