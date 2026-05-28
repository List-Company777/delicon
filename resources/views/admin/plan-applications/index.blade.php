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

@if(session('slot_warning'))
@php $sw = session('slot_warning'); @endphp
<div class="bg-red-50 border-2 border-red-400 text-red-800 rounded-lg px-4 py-4 mb-4 text-sm">
    <p class="font-bold text-base mb-2">⚠️ VIP限定枠超過</p>
    <p>{{ $sw['pref_name'] }} の VIP 枠は <strong>{{ $sw['max'] }}枠</strong> ですが、現在 <strong>{{ $sw['current'] }}店舗</strong> が使用中です。</p>
    <p class="mt-1">このまま承認すると枠を超えて掲載されます。強制承認する場合は以下のボタンを押してください。</p>
    <form action="{{ route('admin.plan-applications.approve', $sw['app_id']) }}/" method="POST" class="mt-3 flex items-center gap-3">
        @csrf
        <input type="hidden" name="force_approve" value="1">
        @if(old('plan'))
            <input type="hidden" name="plan" value="{{ old('plan') }}">
        @endif
        @if(old('plan_name'))
            <input type="hidden" name="plan_name" value="{{ old('plan_name') }}">
        @endif
        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-bold rounded transition">
            枠超過を承知の上で強制承認
        </button>
        <a href="{{ route('admin.plan-applications.index') }}/" class="text-sm text-gray-500 hover:text-gray-700">キャンセル</a>
    </form>
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
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-24">種別</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-28">申込プラン</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-28">現在のプラン</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 w-28">適用日 / 期限</th>
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
                <td class="px-4 py-3">
                    @if($application->application_type === 'renewal')
                        <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">継続</span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">新規</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if($application->plan && isset($planLabels[$application->plan]))
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $planLabels[$application->plan]['color'] }}">
                            {{ $planLabels[$application->plan]['label'] }}
                        </span>
                        @if($application->amount > 0)
                            <p class="text-xs text-gray-400 mt-0.5">¥{{ number_format($application->amount) }}/月</p>
                        @endif
                    @else
                        <span class="text-xs text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if($shop && isset($planLabels[$shop->plan]))
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $planLabels[$shop->plan]['color'] }}">
                            {{ $planLabels[$shop->plan]['label'] }}
                        </span>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    @if($application->effective_date)
                        <p>{{ \Carbon\Carbon::parse($application->effective_date)->format('n/j') }}〜</p>
                    @else
                        <p class="text-orange-600">承認後即時</p>
                    @endif
                    @if($application->expires_on)
                        <p class="text-gray-400">〜{{ \Carbon\Carbon::parse($application->expires_on)->format('n/j') }}</p>
                    @endif
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
                                <div x-show="open" class="mb-2 space-y-2" x-cloak>
                                    <div>
                                        <p class="text-xs text-gray-400 mb-1">承認後の掲載プラン</p>
                                        <select name="plan" x-ref="planSelect"
                                                @change="showBanner = ($event.target.value == 3)"
                                                class="border border-gray-300 rounded px-2 py-1 text-xs w-44 focus:outline-none">
                                            <option value="">変更しない</option>
                                            <option value="1" {{ $application->shop->plan == 1 ? 'selected' : '' }}>1：VIP（¥60,000）</option>
                                            <option value="2" {{ $application->shop->plan == 2 ? 'selected' : '' }}>2：ミドル（¥30,000）</option>
                                            <option value="3" {{ $application->shop->plan == 3 ? 'selected' : '' }}>3：ベーシック（¥10,000）</option>
                                            <option value="4" {{ $application->shop->plan == 4 ? 'selected' : '' }}>4：無料上位（バナー）</option>
                                            <option value="5" {{ $application->shop->plan == 5 ? 'selected' : '' }}>5：無料</option>
                                        </select>
                                    </div>
                                    <div x-data="{ showBanner: {{ $application->shop->plan == 3 ? 'true' : 'false' }} }" x-show="showBanner" class="flex items-center gap-2">
                                        <input type="checkbox" name="is_banner_plan" value="1" id="banner_{{ $application->id }}"
                                               {{ $application->shop->is_banner_plan ? 'checked' : '' }}
                                               class="rounded border-gray-300">
                                        <label for="banner_{{ $application->id }}" class="text-xs text-gray-600">バナー設置によるベーシック</label>
                                    </div>
                                    <div>
                                        <input type="text" name="plan_name"
                                               placeholder="deliconサービス費 6月分"
                                               class="border border-gray-300 rounded px-2 py-1 text-xs w-56 focus:outline-none">
                                        <p class="text-xs text-gray-400 mt-0.5">品目名（請求書に記載されます）</p>
                                    </div>
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
