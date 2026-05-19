@extends('layouts.admin')
@section('title', $shop->name . ' — 店舗詳細')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.shops.index') }}/" class="text-sm text-gray-400 hover:text-gray-600">← 店舗審査一覧</a>
    <h1 class="text-xl font-bold text-gray-700">{{ $shop->name }}</h1>
    @if($shop->status === 'active')
        <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 border border-green-200 rounded-full">掲載中</span>
    @elseif($shop->status === 'pending')
        <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 border border-yellow-200 rounded-full">申請中</span>
    @else
        <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 border border-gray-200 rounded-full">非公開</span>
    @endif
    @if($shop->users->isNotEmpty())
    <form action="{{ route('admin.shops.loginAs', $shop->id) }}/" method="POST" class="ml-auto">
        @csrf
        <button type="submit" class="text-xs px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">管理画面でログイン</button>
    </form>
    @endif
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-6 text-sm">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- 左カラム --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- 基本情報 --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-600">基本情報</h2>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs text-gray-400 font-normal w-32 whitespace-nowrap">店舗ID</th>
                    <td class="px-5 py-3 text-gray-700">#{{ $shop->id }}</td>
                </tr>
                <tr>
                    <th class="text-left px-5 py-3 text-xs text-gray-400 font-normal whitespace-nowrap">店舗名（かな）</th>
                    <td class="px-5 py-3 text-gray-700">{{ $shop->name }}{{ $shop->kana ? '（' . $shop->kana . '）' : '' }}</td>
                </tr>
                <tr>
                    <th class="text-left px-5 py-3 text-xs text-gray-400 font-normal whitespace-nowrap">業種</th>
                    <td class="px-5 py-3 text-gray-700">{{ $shop->shopType?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <th class="text-left px-5 py-3 text-xs text-gray-400 font-normal whitespace-nowrap">ジャンル</th>
                    <td class="px-5 py-3 text-gray-700">{{ $shop->genre?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <th class="text-left px-5 py-3 text-xs text-gray-400 font-normal whitespace-nowrap">都道府県 / 小エリア</th>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="text-gray-500 text-sm">{{ $shop->prefecture?->name ?? '—' }}</span>
                            <form method="POST" action="{{ route('admin.shops.updateArea', $shop->id) }}/" class="flex items-center gap-2">
                                @csrf @method('PATCH')
                                <select name="area_id" class="border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:border-yellow-400 w-52">
                                    <option value="">— 未設定 —</option>
                                    @php $currentPrefId = null; @endphp
                                    @foreach($areas as $area)
                                        @if($area->prefecture_id !== $currentPrefId)
                                            @if($currentPrefId !== null)</optgroup>@endif
                                            <optgroup label="{{ $area->prefecture->name }}">
                                            @php $currentPrefId = $area->prefecture_id; @endphp
                                        @endif
                                        <option value="{{ $area->id }}" {{ $shop->area_id == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                    @endforeach
                                    @if($currentPrefId !== null)</optgroup>@endif
                                </select>
                                <button type="submit" class="px-3 py-1 bg-yellow-500 text-white text-xs rounded-lg hover:bg-yellow-600 transition">設定</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="text-left px-5 py-3 text-xs text-gray-400 font-normal whitespace-nowrap">住所</th>
                    <td class="px-5 py-3 text-gray-700">{{ $shop->address ?: '—' }}</td>
                </tr>
                <tr>
                    <th class="text-left px-5 py-3 text-xs text-gray-400 font-normal whitespace-nowrap">電話番号</th>
                    <td class="px-5 py-3 text-gray-700">{{ $shop->tel ?: '—' }}</td>
                </tr>
                <tr>
                    <th class="text-left px-5 py-3 text-xs text-gray-400 font-normal whitespace-nowrap">最寄り駅</th>
                    <td class="px-5 py-3 text-gray-700">
                        @if($shop->nearest_station_name)
                            {{ $shop->nearest_line }} {{ $shop->nearest_station_name }}駅
                            @if($shop->nearest_station_walk) 徒歩{{ $shop->nearest_station_walk }}分 @endif
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="text-left px-5 py-3 text-xs text-gray-400 font-normal whitespace-nowrap">XML連携</th>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $shop->xml_source ? $shop->xml_source . ' / ' . $shop->xml_id : '—' }}</td>
                </tr>
                <tr>
                    <th class="text-left px-5 py-3 text-xs text-gray-400 font-normal whitespace-nowrap">申請日時</th>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $shop->updated_at->format('Y/m/d H:i') }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        {{-- 営業情報 --}}
        @if($shop->detail)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-600">営業情報</h2>
                <span @class([
                    'text-xs px-2 py-0.5 rounded-full',
                    'bg-green-100 text-green-700' => $shop->detail->status === 'active',
                    'bg-gray-100 text-gray-400'   => $shop->detail->status !== 'active',
                ])>{{ $shop->detail->status === 'active' ? '公開中' : '非公開' }}</span>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs text-gray-400 font-normal w-32 whitespace-nowrap">営業時間</th>
                    <td class="px-5 py-3 text-gray-700">
                        @if($shop->detail->opening_hours)
                            {{ $shop->detail->opening_hours }}〜{{ $shop->detail->closing_hours }}
                        @else —
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="text-left px-5 py-3 text-xs text-gray-400 font-normal whitespace-nowrap">セット料金</th>
                    <td class="px-5 py-3 text-gray-700">
                        @if($shop->setPrices->isNotEmpty())
                            @foreach($shop->setPrices as $p)
                                <span class="mr-3">{{ $p->time_label }} {{ $p->price }}</span>
                            @endforeach
                        @elseif($shop->detail->set_price)
                            {{ $shop->detail->set_price }}
                        @else —
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="text-left px-5 py-3 text-xs text-gray-400 font-normal whitespace-nowrap">飲み放題 / カラオケ</th>
                    <td class="px-5 py-3 text-gray-700 text-xs">
                        {{ $shop->detail->all_you_can_drink ? '飲み放題あり' : '—' }}
                        　{{ $shop->detail->has_karaoke ? 'カラオケあり' : '' }}
                    </td>
                </tr>
                @if($shop->externalUrls->isNotEmpty())
                <tr>
                    <th class="text-left px-5 py-3 text-xs text-gray-400 font-normal whitespace-nowrap">外部URL</th>
                    <td class="px-5 py-3 text-xs space-y-1">
                        @foreach($shop->externalUrls as $url)
                            <div><span class="text-gray-400">{{ $url->label }}：</span><a href="{{ $url->url }}" target="_blank" class="text-blue-500 hover:underline break-all">{{ $url->url }}</a></div>
                        @endforeach
                    </td>
                </tr>
                @endif
                </tbody>
            </table>
        </div>
        @else
        <div class="bg-white rounded-xl shadow-sm px-5 py-4 text-sm text-gray-400">営業情報：未登録</div>
        @endif

        {{-- 求人一覧 --}}
        @php
            $castJobs  = $shop->jobs->filter(fn($j) => in_array($j->search_group, ['female', 'both']));
            $staffJobs = $shop->jobs->filter(fn($j) => $j->search_group === 'male');
        @endphp
        @if($shop->jobs->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-600">求人（{{ $shop->jobs->count() }}件）</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2 text-xs text-gray-400 font-normal">タイトル</th>
                        <th class="text-left px-4 py-2 text-xs text-gray-400 font-normal w-20">職種</th>
                        <th class="text-left px-4 py-2 text-xs text-gray-400 font-normal w-16">対象</th>
                        <th class="text-left px-4 py-2 text-xs text-gray-400 font-normal w-16">状態</th>
                        <th class="text-left px-4 py-2 text-xs text-gray-400 font-normal w-16">画像</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($shop->jobs as $job)
                    <tr>
                        <td class="px-4 py-2 text-gray-700 text-xs">{{ $job->title }}</td>
                        <td class="px-4 py-2 text-gray-500 text-xs">{{ $job->jobType?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-xs">
                            @if($job->search_group === 'female') <span class="text-pink-500">女性</span>
                            @elseif($job->search_group === 'male') <span class="text-blue-500">男性</span>
                            @else <span class="text-gray-500">両方</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-xs">
                            @if($job->status === 'active') <span class="text-green-600">公開</span>
                            @elseif($job->status === 'draft') <span class="text-gray-400">下書き</span>
                            @else <span class="text-gray-400">非公開</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-xs">{{ $job->image_path ? '✓' : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>

    {{-- 右カラム --}}
    <div class="space-y-6">

        {{-- メイン画像 --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-600">メイン画像</h2>
            </div>
            <div class="p-4">
                @if($shop->main_image)
                    <picture>
                        <source srcset="{{ asset('storage/' . \App\Services\ImageService::webpPath($shop->main_image)) }}" type="image/webp">
                        <img src="{{ asset('storage/' . $shop->main_image) }}" alt="{{ $shop->name }}"
                             class="w-full rounded-lg object-cover">
                    </picture>
                @else
                    <div class="h-24 bg-gray-100 rounded-lg flex items-center justify-center text-gray-300 text-sm">画像なし</div>
                @endif
            </div>
        </div>

        {{-- オーナー情報 --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-600">オーナー</h2>
            </div>
            <div class="p-5 text-sm">
                @php $owner = $shop->users->first(); @endphp
                @if($owner)
                    <p class="font-medium text-gray-700">{{ $owner->name }}</p>
                    <p class="text-gray-400 text-xs mt-0.5">{{ $owner->email }}</p>
                    <p class="text-gray-400 text-xs mt-1">
                        最終ログイン：{{ $owner->last_login_at?->format('Y/m/d H:i') ?? '未ログイン' }}
                    </p>
                    <p class="text-gray-400 text-xs mt-0.5">
                        メール認証：{{ $owner->email_verified_at ? '済み' : '未認証' }}
                    </p>
                @else
                    <p class="text-gray-400">オーナー未登録</p>
                @endif
                {{-- 代理店紐づけ --}}
                <div class="mt-4 border-t border-gray-100 pt-3">
                    <p class="text-xs text-gray-400 mb-2">代理店</p>
                    <form action="{{ route('admin.shops.updatePartner', $shop->id) }}/" method="POST" class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <select name="partner_id"
                                class="flex-1 border border-gray-300 rounded px-2 py-1.5 text-xs focus:outline-none focus:border-business-500 min-w-0">
                            <option value="">（なし）</option>
                            @foreach($partners as $p)
                                <option value="{{ $p->id }}" {{ $shop->partner_id == $p->id ? 'selected' : '' }}>
                                    [{{ $p->type === 'management' ? '管理代行' : '紹介' }}] {{ $p->company_name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="text-xs text-business-700 hover:underline whitespace-nowrap">保存</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- 審査アクション --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-600">審査・操作</h2>
            </div>
            <div class="p-5 space-y-4">

                {{-- 届出書 --}}
                @if($shop->permit_type)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-1">
                    <p class="text-xs font-bold text-blue-700 mb-1">届出書情報</p>
                    @if($shop->permit_type === 'uploaded')
                        <p class="text-xs text-blue-600 mb-1">📄 届出書アップロード済み</p>
                        @if($shop->permit_document_path)
                        <a href="{{ route('admin.shops.permit-download', $shop->id) }}/"
                           class="text-xs text-blue-700 underline hover:text-blue-900 font-medium" target="_blank">
                            届出書を確認する →
                        </a>
                        @endif
                    @else
                        <p class="text-xs text-blue-600">✅ 「届出・許可は不要」と申告済み</p>
                    @endif
                </div>
                @else
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-1">
                    <p class="text-xs text-amber-700">⚠ 届出書情報なし（古い申請 or 未申請）</p>
                </div>
                @endif

                {{-- 承認 --}}
                @if($shop->status !== 'active')
                <form action="{{ route('admin.shops.approve', $shop->id) }}/" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full py-2 bg-green-500 hover:bg-green-400 text-white text-sm font-bold rounded-lg transition"
                            onclick="return confirm('「{{ $shop->name }}」を承認しますか？')">
                        ✓ 承認して掲載開始
                    </button>
                </form>
                @endif

                {{-- 却下 --}}
                @if($shop->status !== 'inactive')
                <form action="{{ route('admin.shops.reject', $shop->id) }}/" method="POST" class="space-y-2">
                    @csrf
                    <textarea name="note" rows="2"
                              placeholder="却下理由（任意・メールに記載されます）"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-gray-400 resize-none"></textarea>
                    <button type="submit"
                            class="w-full py-2 bg-gray-200 hover:bg-gray-300 text-gray-600 text-sm font-bold rounded-lg transition"
                            onclick="return confirm('「{{ $shop->name }}」を却下しますか？')">
                        却下（非公開に変更）
                    </button>
                </form>
                @endif

                {{-- プラン変更 --}}
                <div class="border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-400 mb-2">掲載プラン</p>
                    @php
                        $planLabels = [
                            1 => '1：VIP（¥80,000）',
                            2 => '2：ミドル（¥40,000）',
                            3 => '3：ベーシック（¥20,000）',
                            4 => '4：無料上位（バナー）',
                            5 => '5：無料',
                        ];
                    @endphp
                    <form action="{{ route('admin.shops.updatePlan', $shop->id) }}/" method="POST" class="space-y-2">
                        @csrf @method('PATCH')
                        <div class="flex items-center gap-2">
                            <select name="plan" id="plan_select_{{ $shop->id }}" class="border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:border-business-500" onchange="toggleBannerPlan(this, {{ $shop->id }})">
                                @foreach($planLabels as $val => $label)
                                <option value="{{ $val }}" {{ $shop->plan == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="text-sm text-business-700 hover:underline"
                                    onclick="return confirm('プランを変更しますか？')">
                                変更
                            </button>
                        </div>
                        <div id="banner_plan_row_{{ $shop->id }}" class="{{ $shop->plan == 3 ? '' : 'hidden' }} flex items-center gap-2">
                            <input type="checkbox" name="is_banner_plan" value="1" id="is_banner_{{ $shop->id }}"
                                   {{ $shop->is_banner_plan ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-business-600">
                            <label for="is_banner_{{ $shop->id }}" class="text-xs text-gray-600">バナー設置によるベーシック</label>
                        </div>
                    </form>
                    @if($shop->paid_since)
                    <p class="text-xs text-gray-400 mt-1">有料継続開始日：{{ $shop->paid_since }}</p>
                    @endif
                </div>

                {{-- 入札単価（active時） --}}
                @if($shop->status === 'active')
                <div class="border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-400 mb-2">入札単価</p>
                    <form action="{{ route('admin.shops.updateBidPrice', $shop->id) }}/" method="POST" class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <input type="number" name="bid_price" value="{{ $shop->bid_price }}"
                               min="10" max="9990" step="10"
                               class="w-24 border border-gray-300 rounded px-3 py-1.5 text-sm text-right focus:outline-none focus:border-business-500">
                        <span class="text-sm text-gray-400">円</span>
                        <button type="submit" class="text-sm text-business-700 hover:underline">更新</button>
                    </form>
                    <p class="text-xs text-gray-400 mt-1">残高：{{ number_format($shop->budget_balance) }}円</p>
                </div>

                {{-- 残高補充（active時） --}}
                <div class="border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-400 mb-2">残高補充</p>
                    @if(session('success') && str_contains(session('success'), '残高に追加'))
                        <p class="text-xs text-green-600 mb-2">{{ session('success') }}</p>
                    @endif
                    <form action="{{ route('admin.shops.addBudget', $shop->id) }}/" method="POST" class="flex items-center gap-2">
                        @csrf
                        <input type="number" name="amount" placeholder="例: 999999"
                               min="1000" max="9999999" step="1000"
                               class="w-32 border border-gray-300 rounded px-3 py-1.5 text-sm text-right focus:outline-none focus:border-business-500">
                        <span class="text-sm text-gray-400">円</span>
                        <button type="submit" class="text-sm text-business-700 hover:underline">追加</button>
                    </form>
                </div>
                @endif

                {{-- 公開ページ --}}
                @if($shop->status === 'active')
                <div class="border-t border-gray-100 pt-4">
                    <a href="{{ route('shop.show', $shop->id) }}/" target="_blank"
                       class="text-xs text-blue-500 hover:underline">公開ページを確認 →</a>
                </div>
                @endif

                {{-- 削除 --}}
                <div class="border-t border-gray-100 pt-4">
                    <form action="{{ route('admin.shops.destroy', $shop->id) }}/" method="POST"
                          onsubmit="return confirm('「{{ $shop->name }}」を完全に削除します。求人・応募履歴・オーナーアカウントも削除されます。\nこの操作は取り消せません。本当に削除しますか？')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-bold rounded-lg border border-red-200 transition">
                            アカウントを削除
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleBannerPlan(select, shopId) {
    const row = document.getElementById('banner_plan_row_' + shopId);
    row.classList.toggle('hidden', parseInt(select.value) !== 3);
}
</script>
@endpush
