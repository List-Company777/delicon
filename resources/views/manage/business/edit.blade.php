@extends('layouts.app')
@section('title', '営業情報の編集')
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

    <h2 class="text-lg font-bold text-gray-800 mb-6">営業情報</h2>

    <form action="{{ route('manage.business.update') }}" method="POST" class="space-y-4">
        @csrf @method('PUT')

        {{-- 基本情報テーブル --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-36 whitespace-nowrap">公開設定</th>
                <td class="px-4 py-3">
                    <label class="inline-flex items-center gap-2 mr-6">
                        <input type="radio" name="status" value="active" {{ old('status', $detail->status) === 'active' ? 'checked' : '' }}>
                        <span class="text-green-700 font-medium">公開</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="status" value="inactive" {{ old('status', $detail->status) !== 'active' ? 'checked' : '' }}>
                        <span class="text-gray-500">非公開</span>
                    </label>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">ひとこと紹介</th>
                <td class="px-4 py-3" x-data="{ val: {{ Js::from(old('short_description', $detail->short_description ?? '')) }} }">
                    <input type="text" name="short_description"
                           x-model="val"
                           maxlength="40"
                           placeholder="例：新宿最大級のキャバクラ！アフターもOK"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                    <p class="text-xs text-gray-400 mt-1"><span x-text="val.length"></span> / 40文字。店舗名の下に表示される短いキャッチコピーです</p>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">店舗紹介文</th>
                <td class="px-4 py-3">
                    <textarea name="content" rows="5"
                              class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-business-500">{{ old('content', $detail->content) }}</textarea>
                    <p class="text-xs text-gray-400 mt-1">最大2000文字</p>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">指名料</th>
                <td class="px-4 py-3">
                    <input type="text" name="nomination_fee" value="{{ old('nomination_fee', $detail->nomination_fee) }}"
                           placeholder="例：本指名1,000円・場内指名500円"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">飲み放題</th>
                <td class="px-4 py-3">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="all_you_can_drink" value="1"
                               {{ old('all_you_can_drink', $detail->all_you_can_drink) ? 'checked' : '' }}>
                        あり
                    </label>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">税</th>
                <td class="px-4 py-3">
                    @php
                        $taxVal = old('tax_included', $detail->tax_included === null ? '' : ($detail->tax_included ? '1' : '0'));
                    @endphp
                    <select name="tax_included" class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                        <option value="" {{ $taxVal === '' ? 'selected' : '' }}>未設定</option>
                        <option value="1" {{ $taxVal === '1' ? 'selected' : '' }}>税込</option>
                        <option value="0" {{ $taxVal === '0' ? 'selected' : '' }}>税別</option>
                    </select>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">サービス料</th>
                <td class="px-4 py-3">
                    <input type="text" name="service_charge" value="{{ old('service_charge', $detail->service_charge) }}"
                           placeholder="例：10%、1,000円、込み"
                           class="w-48 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap align-top">割引・特典</th>
                <td class="px-4 py-3 space-y-3">
                    <label class="inline-flex items-start gap-2">
                        <input type="checkbox" name="discount_first_set" value="1"
                               {{ old('discount_first_set', $detail->discount_first_set ?? false) ? 'checked' : '' }}
                               class="mt-0.5">
                        <span>初回セット料金10%オフ</span>
                    </label>
                    <p class="text-xs text-gray-400">チェックを入れると、店舗ページと検索結果に「初回10%オフ」と表示されます</p>
                    <div>
                        <input type="text" name="discount_custom"
                               value="{{ old('discount_custom', $detail->discount_custom ?? '') }}"
                               placeholder="例：新規様限定！指名料1時間無料"
                               maxlength="200"
                               class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                        <p class="text-xs text-gray-400 mt-1">独自の割引・特典を自由に入力（200文字以内）</p>
                    </div>
                </td>
            </tr>
            @if($shop->hasBudget())
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap align-top">外部サイト誘導</th>
                <td class="px-4 py-3 space-y-3" x-data="{ hotlink: {{ old('is_hotlink', $detail->is_hotlink ?? false) ? 'true' : 'false' }} }">
                    <label class="inline-flex items-start gap-2">
                        <input type="checkbox" name="is_hotlink" value="1"
                               x-model="hotlink"
                               {{ old('is_hotlink', $detail->is_hotlink ?? false) ? 'checked' : '' }}
                               class="mt-0.5">
                        <span>有効にする</span>
                    </label>
                    <p class="text-xs text-gray-400">有効にすると、検索結果や店舗ページのボタンクリック時に指定したURLへ直接移動します。クリック課金は入札単価＋20円/クリックになります。有料プランへの加入が必要です。</p>
                    <div x-show="hotlink" x-cloak>
                        <input type="url" name="hotlink_url"
                               value="{{ old('hotlink_url', $detail->hotlink_url ?? '') }}"
                               placeholder="https://example.com/shop/..."
                               maxlength="500"
                               class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                        <p class="text-xs text-gray-400 mt-1">誘導先のURL（500文字以内）</p>
                    </div>
                </td>
            </tr>
            @endif
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">カラオケ</th>
                <td class="px-4 py-3">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="has_karaoke" value="1"
                               {{ old('has_karaoke', $detail->has_karaoke) ? 'checked' : '' }}>
                        あり
                    </label>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">個室</th>
                <td class="px-4 py-3">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="has_private_room" value="1"
                               {{ old('has_private_room', $detail->has_private_room) ? 'checked' : '' }}>
                        あり
                    </label>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">営業曜日</th>
                <td class="px-4 py-3">
                    @php
                        $dayLabels = ['Mo' => '月', 'Tu' => '火', 'We' => '水', 'Th' => '木', 'Fr' => '金', 'Sa' => '土', 'Su' => '日'];
                        $selectedDays = old('opening_days', $detail->opening_days ?? []);
                    @endphp
                    <div class="flex flex-wrap gap-4">
                        @foreach($dayLabels as $val => $label)
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" name="opening_days[]" value="{{ $val }}"
                                       {{ in_array($val, $selectedDays) ? 'checked' : '' }}
                                       class="rounded">
                                <span class="text-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-1">設定すると Google に営業時間が表示されやすくなります</p>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">営業開始</th>
                <td class="px-4 py-3">
                    <input type="text" name="opening_hours" value="{{ old('opening_hours', $detail->opening_hours) }}"
                           placeholder="例：20:00"
                           class="w-32 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                    <span class="text-xs text-gray-400 ml-2">HH:MM 形式</span>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">営業終了</th>
                <td class="px-4 py-3">
                    <input type="text" name="closing_hours" value="{{ old('closing_hours', $detail->closing_hours) }}"
                           placeholder="例：05:00"
                           class="w-32 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                    <span class="text-xs text-gray-400 ml-2">深夜は翌日時刻で入力（例：05:00）</span>
                </td>
            </tr>
            <tr>
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">定休日</th>
                <td class="px-4 py-3">
                    <input type="text" name="holiday" value="{{ old('holiday', $detail->holiday) }}"
                           placeholder="例：不定休"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </td>
            </tr>
        </table>
        </div>

        {{-- 料金プラン（動的・最大3プラン） --}}
        @php
            $initialPlans = old('plans')
                ? collect(old('plans'))->values()->toArray()
                : $pricePlans->map(fn($p) => [
                    'name' => $p->name ?? '',
                    'set_prices' => $p->setPrices->map(fn($r) => [
                        'time_from' => $r->time_from ?? '',
                        'time_to'   => $r->time_to ?? '',
                        'price'     => $r->price,
                    ])->values()->toArray(),
                    'extension_prices' => $p->extensionPrices->map(fn($r) => [
                        'label' => $r->label,
                        'price' => $r->price,
                    ])->values()->toArray(),
                ])->values()->toArray();
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-5"
             x-data="{
                 plans: {{ Js::from($initialPlans) }},
                 addPlan() {
                     if (this.plans.length < 3) this.plans.push({ name: '', set_prices: [], extension_prices: [] });
                 },
                 removePlan(i) { this.plans.splice(i, 1); },
                 addSetPrice(pi) {
                     if (this.plans[pi].set_prices.length < 5)
                         this.plans[pi].set_prices.push({ time_from: '', time_to: '', price: '' });
                 },
                 addExtPrice(pi) {
                     if (this.plans[pi].extension_prices.length < 3)
                         this.plans[pi].extension_prices.push({ label: '', price: '' });
                 },
             }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-700">セット料金・料金プラン</h3>
                <button type="button" @click="addPlan()"
                        x-show="plans.length < 3"
                        class="text-xs text-business-700 border border-business-300 rounded px-3 py-1 hover:bg-business-50 transition">
                    ＋ 料金システムを追加
                </button>
            </div>

            <template x-for="(plan, pi) in plans" :key="pi">
                <div class="border border-gray-200 rounded-xl p-4 mb-3">
                    {{-- プラン名 --}}
                    <div class="flex items-center gap-2 mb-3">
                        <input type="text" :name="`plans[${pi}][name]`" x-model="plan.name"
                               placeholder="例: VIP / members / 一般（プラン名は省略可）"
                               class="flex-1 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                        <button type="button" @click="removePlan(pi)"
                                class="text-xs text-red-400 hover:text-red-600 border border-red-200 rounded px-2 py-1.5 transition shrink-0">
                            このプランを削除
                        </button>
                    </div>

                    {{-- セット料金 --}}
                    <p class="text-xs font-medium text-gray-500 mb-1.5">セット料金（最大5件）</p>
                    <div class="space-y-2 mb-2">
                        <template x-for="(row, si) in plan.set_prices" :key="si">
                            <div class="flex items-center gap-2">
                                <input type="text" :name="`plans[${pi}][set_prices][${si}][time_from]`" x-model="row.time_from"
                                       placeholder="19:00" class="w-20 border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:border-business-500">
                                <span class="text-gray-400 text-sm">〜</span>
                                <input type="text" :name="`plans[${pi}][set_prices][${si}][time_to]`" x-model="row.time_to"
                                       placeholder="21:00" class="w-20 border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:border-business-500">
                                <input type="text" :name="`plans[${pi}][set_prices][${si}][price]`" x-model="row.price"
                                       placeholder="6,000円" class="flex-1 border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:border-business-500">
                                <button type="button" @click="plan.set_prices.splice(si, 1)"
                                        class="text-gray-400 hover:text-red-400 transition shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <p x-show="plan.set_prices.length === 0" class="text-xs text-gray-400">時間帯ごとの料金を登録できます</p>
                    </div>
                    <button type="button" @click="addSetPrice(pi)"
                            x-show="plan.set_prices.length < 5"
                            class="text-xs text-business-700 hover:underline mb-3">＋ セット料金を追加</button>

                    {{-- 延長料金 --}}
                    <p class="text-xs font-medium text-gray-500 mb-1.5">延長料金（最大3件）</p>
                    <div class="space-y-2 mb-2">
                        <template x-for="(row, ei) in plan.extension_prices" :key="ei">
                            <div class="flex items-center gap-2">
                                <input type="text" :name="`plans[${pi}][extension_prices][${ei}][label]`" x-model="row.label"
                                       placeholder="例: 延長30分" class="w-36 border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:border-business-500">
                                <input type="text" :name="`plans[${pi}][extension_prices][${ei}][price]`" x-model="row.price"
                                       placeholder="5,000円" class="flex-1 border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:border-business-500">
                                <button type="button" @click="plan.extension_prices.splice(ei, 1)"
                                        class="text-gray-400 hover:text-red-400 transition shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <p x-show="plan.extension_prices.length === 0" class="text-xs text-gray-400">延長料金がある場合に登録できます</p>
                    </div>
                    <button type="button" @click="addExtPrice(pi)"
                            x-show="plan.extension_prices.length < 3"
                            class="text-xs text-business-700 hover:underline">＋ 延長料金を追加</button>
                </div>
            </template>

            <p x-show="plans.length === 0" class="text-xs text-gray-400">「料金システムを追加」ボタンで料金プランを登録できます（最大3プラン）</p>
            <p class="text-xs text-gray-400 mt-2">時間は HH:MM 形式。終了時間に「LAST」も使用可</p>
        </div>

        {{-- その他料金（動的行） --}}
        @php
            $initialOtherCharges = old('other_charges')
                ? collect(old('other_charges'))->values()->toArray()
                : $otherCharges->map(fn($r) => ['label' => $r->label, 'price' => $r->price])->values()->toArray();
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-5"
             x-data="{
                 rows: {{ Js::from($initialOtherCharges) }},
                 add() {
                     if (this.rows.length < 5) this.rows.push({ label: '', price: '' });
                 },
                 remove(i) { this.rows.splice(i, 1); }
             }">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-gray-700">その他料金</h3>
                <button type="button" @click="add()"
                        x-show="rows.length < 5"
                        class="text-xs text-business-700 hover:underline">+ 追加</button>
            </div>
            <div class="space-y-2">
                <template x-for="(row, i) in rows" :key="i">
                    <div class="flex items-center gap-2">
                        <input type="text" :name="`other_charges[${i}][label]`" x-model="row.label"
                               placeholder="例: ボトルキープ" class="w-40 border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:border-business-500">
                        <input type="text" :name="`other_charges[${i}][price]`" x-model="row.price"
                               placeholder="10,000円〜" class="flex-1 border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:border-business-500">
                        <button type="button" @click="remove(i)"
                                class="text-gray-400 hover:text-red-400 transition shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
                <p x-show="rows.length === 0" class="text-xs text-gray-400">ボトル代・テーブルチャージなど任意の料金を登録できます（最大5件）</p>
            </div>
        </div>

        {{-- 外部URL（動的行） --}}
        <div class="bg-white rounded-xl shadow-sm p-5"
             x-data="{
                 rows: {{ Js::from(
                     old('external_urls')
                         ? collect(old('external_urls'))->values()->toArray()
                         : $externalUrls->map(fn($r) => ['url_type' => $r->url_type, 'url' => $r->url])->values()->toArray()
                 ) }},
                 types: {{ Js::from($urlTypes) }},
                 add() {
                     if (this.rows.length < 6) this.rows.push({ url_type: 'website', url: '' });
                 },
                 remove(i) { this.rows.splice(i, 1); }
             }">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-gray-700">外部リンク</h3>
                <button type="button" @click="add()"
                        x-show="rows.length < 6"
                        class="text-xs text-business-700 hover:underline">+ 追加</button>
            </div>
            <div class="space-y-2">
                <template x-for="(row, i) in rows" :key="i">
                    <div class="flex items-center gap-2">
                        <select :name="`external_urls[${i}][url_type]`" x-model="row.url_type"
                                class="w-40 border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:border-business-500 shrink-0">
                            <template x-for="(label, val) in types" :key="val">
                                <option :value="val" :selected="row.url_type === val" x-text="label"></option>
                            </template>
                        </select>
                        <input type="url" :name="`external_urls[${i}][url]`" x-model="row.url"
                               placeholder="https://example.com"
                               class="flex-1 border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:border-business-500">
                        <button type="button" @click="remove(i)"
                                class="text-gray-400 hover:text-red-400 transition shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
                <p x-show="rows.length === 0" class="text-xs text-gray-400">「追加」ボタンでSNSや公式サイトのURLを登録できます（最大6件）</p>
            </div>
        </div>

        {{-- よくある質問（営業面） --}}
        <div class="mt-6 mb-4"
             x-data="{
                 items: {{ Js::from(old('faq', $detail->faq ?? [])) }},
                 add() { if (this.items.length < 3) this.items.push({ q: '', a: '' }); },
                 remove(i) { this.items.splice(i, 1); }
             }">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-bold text-gray-700">よくある質問（営業）</h3>
                <button type="button" @click="add()"
                        x-show="items.length < 3"
                        class="text-xs text-business-700 hover:underline">+ 追加</button>
            </div>
            <p class="text-xs text-gray-500 mb-3">電話でのお客様からのお問い合わせでよく聞かれることとその答えをわかりやすく記載してください。</p>
            <div class="space-y-3">
                <template x-for="(item, i) in items" :key="i">
                    <div class="border border-gray-200 rounded-lg p-3 space-y-2 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-500" x-text="`Q${i + 1}`"></span>
                            <button type="button" @click="remove(i)" class="text-gray-400 hover:text-red-400 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <input type="text" :name="`faq[${i}][q]`" x-model="item.q"
                               placeholder="質問（例：予約は必要ですか？）"
                               maxlength="100"
                               class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                        <textarea :name="`faq[${i}][a]`" x-model="item.a"
                                  placeholder="回答（例：ご予約は不要です。当日直接ご来店ください。）"
                                  maxlength="300" rows="2"
                                  class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500 resize-none"></textarea>
                    </div>
                </template>
                <p x-show="items.length === 0" class="text-xs text-gray-400">「追加」ボタンでよくある質問を登録できます（最大3件）</p>
            </div>
        </div>

        <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-800 leading-relaxed mb-4">
            <p class="font-bold mb-1">🔍 検索結果への反映について</p>
            <ul class="space-y-0.5 text-blue-700">
                <li>・<span class="font-medium">飲み放題・カラオケ・個室</span>：検索画面の絞り込みチップに反映されます。チェックを入れると該当チップで絞り込んだ際に表示されます。</li>
                <li>・<span class="font-medium">割引・特典（初回割引）</span>：「初回割引」チップで絞り込まれる際の条件になります。検索結果カードにもバッジが表示されます。</li>
            </ul>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="bg-business-700 hover:bg-business-600 text-white text-sm font-bold px-8 py-2.5 rounded-lg transition">
                保存する
            </button>
        </div>
    </form>
</div>
@endsection
