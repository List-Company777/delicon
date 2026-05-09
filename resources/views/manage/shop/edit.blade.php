@extends('layouts.app')
@section('title', '店舗基本情報の編集')
@section('content')
<div class="bg-business-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold">店舗管理</h1>
        <form action="{{ route('logout') }}/" method="POST">
            @csrf
            <button class="text-sm opacity-70 hover:opacity-100">ログアウト</button>
        </form>
    </div>
</div>

@include('manage._nav')

<div class="max-w-4xl mx-auto px-4 pb-12">
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <h2 class="text-lg font-bold text-gray-800 mb-6">店舗基本情報</h2>

    <form action="{{ route('manage.shop.update') }}/" method="POST" class="bg-white rounded-xl shadow-sm overflow-hidden">
        @csrf @method('PUT')
        <table class="w-full text-sm">
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-36 whitespace-nowrap">店舗名 <span class="text-red-400">*</span></th>
                <td class="px-4 py-3">
                    <input type="text" name="name" value="{{ old('name', $shop->name) }}" required
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">フリガナ</th>
                <td class="px-4 py-3">
                    <input type="text" name="kana" value="{{ old('kana', $shop->kana) }}"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </td>
            </tr>
            {{-- 業種・都道府県・エリアは管理者のみ変更可（読み取り専用） --}}
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">業種</th>
                <td class="px-4 py-3">
                    <span class="text-gray-700">{{ $shop->genre?->name ?? '—' }}</span>
                    <p class="text-xs text-gray-400 mt-0.5">変更は<a href="{{ route('manage.contact') }}/" class="underline hover:text-gray-600">お問い合わせフォーム</a>からご連絡ください</p>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">都道府県</th>
                <td class="px-4 py-3">
                    <span class="text-gray-700">{{ $shop->prefecture?->name ?? '—' }}</span>
                    <p class="text-xs text-gray-400 mt-0.5">変更は<a href="{{ route('manage.contact') }}/" class="underline hover:text-gray-600">お問い合わせフォーム</a>からご連絡ください</p>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">小エリア</th>
                <td class="px-4 py-3">
                    @if($shop->area)
                        <span class="text-gray-700">{{ $shop->area->name }}</span>
                    @else
                        <span class="text-orange-500 font-medium">未設定</span>
                        <p class="text-xs text-orange-600 mt-1 leading-relaxed">
                            小エリアが未設定です。検索結果に正しく表示されるよう、ご希望の小エリアを<a href="{{ route('manage.contact') }}/" class="underline hover:text-orange-800">お問い合わせフォーム</a>よりお知らせください。
                        </p>
                    @endif
                    @if($shop->area)
                        <p class="text-xs text-gray-400 mt-0.5">変更は<a href="{{ route('manage.contact') }}/" class="underline hover:text-gray-600">お問い合わせフォーム</a>からご連絡ください</p>
                    @endif
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">郵便番号</th>
                <td class="px-4 py-3">
                    <input type="text" name="postal_code" value="{{ old('postal_code', $shop->postal_code) }}"
                           placeholder="例：160-0021" maxlength="8"
                           class="w-40 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500 @error('postal_code') border-red-400 @enderror">
                    @error('postal_code')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">市区町村</th>
                <td class="px-4 py-3">
                    <input type="text" name="address_locality" value="{{ old('address_locality', $shop->address_locality) }}"
                           placeholder="例：新宿区歌舞伎町"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500 @error('address_locality') border-red-400 @enderror">
                    <p class="text-xs text-gray-400 mt-0.5">区・町・丁目まで入力してください（検索エンジン向け住所情報に使用）</p>
                    @error('address_locality')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">番地・建物名 <span class="text-red-400">*</span></th>
                <td class="px-4 py-3">
                    <input type="text" name="address" value="{{ old('address', $shop->address) }}" required
                           placeholder="例：1-1-1 ○○ビル3F"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500 @error('address') border-red-400 @enderror">
                    @error('address')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">電話番号 <span class="text-red-400">*</span></th>
                <td class="px-4 py-3">
                    <input type="tel" name="tel" value="{{ old('tel', $shop->tel) }}" required
                           placeholder="例：03-0000-0000"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500 @error('tel') border-red-400 @enderror">
                    @error('tel')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">路線</th>
                <td class="px-4 py-3">
                    <input type="text" name="nearest_line" value="{{ old('nearest_line', $shop->nearest_line) }}"
                           placeholder="例：東京メトロ丸ノ内線"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">最寄り駅</th>
                <td class="px-4 py-3">
                    <input type="text" name="nearest_station_name" value="{{ old('nearest_station_name', $shop->nearest_station_name) }}"
                           placeholder="例：新宿三丁目"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">徒歩（分）</th>
                <td class="px-4 py-3">
                    <input type="number" name="nearest_station_walk" value="{{ old('nearest_station_walk', $shop->nearest_station_walk) }}"
                           min="1" max="99" class="w-24 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">LINE ID</th>
                <td class="px-4 py-3">
                    <input type="text" name="line_id" value="{{ old('line_id', $shop->line_id) }}"
                           placeholder="店舗公式LINEのID"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-business-500">
                    <p class="text-xs text-gray-400 mt-0.5">求職者が友だち追加するための公開LINE IDです</p>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">拠点（ベース）</th>
                <td class="px-4 py-3">
                    <input type="text" name="base" value="{{ old('base', $shop->base) }}"
                           placeholder="例：新宿・渋谷エリア"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">キャッチコピー</th>
                <td class="px-4 py-3">
                    <input type="text" name="catche" value="{{ old('catche', $shop->catche) }}" maxlength="200"
                           placeholder="例：超高級デリヘル！業界トップクラスの在籍数"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">システム説明</th>
                <td class="px-4 py-3">
                    <textarea name="system_text" id="shop-system-text" rows="4" maxlength="5000"
                              class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400 resize-y">{{ old('system_text', $shop->system_text) }}</textarea>
                    <p class="text-xs text-gray-400 mt-0.5">デリヘルのシステム・サービス内容の説明</p>
                    @php $shopTextLen = mb_strlen($shop->base ?? '') + mb_strlen($shop->system_text ?? ''); @endphp
                    @if($shopTextLen < 100)
                    <p id="shop-noindex-warning" class="mt-2 text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                        ⚠ 店舗紹介文（拠点＋システム説明）の合計が100文字未満のため、店舗詳細ページは現在<strong>検索エンジンの対象外（noindex）</strong>になっています。合計100文字以上になると検索対象になります。（現在 <span id="shop-text-len">{{ $shopTextLen }}</span>文字）
                    </p>
                    @endif
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">クーポン</th>
                <td class="px-4 py-3">
                    <textarea name="coupon" rows="3" maxlength="2000"
                              class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400 resize-y">{{ old('coupon', $shop->coupon) }}</textarea>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">営業時間</th>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3 flex-wrap">
                        <div class="flex items-center gap-2">
                            <input type="text" name="open_time" value="{{ old('open_time', $shop->open_time) }}"
                                   placeholder="09:00" class="w-24 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                            <span class="text-gray-500 text-sm">〜</span>
                            <input type="text" name="close_time" value="{{ old('close_time', $shop->close_time) }}"
                                   placeholder="翌05:00" class="w-24 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="all_time" value="1" @checked(old('all_time', $shop->all_time))
                                   class="w-4 h-4 accent-red-600">
                            <span class="text-sm text-gray-700">24時間営業</span>
                        </label>
                    </div>
                    <div class="mt-2 flex items-center gap-2">
                        <span class="text-xs text-gray-500 whitespace-nowrap">定休日：</span>
                        <input type="text" name="rest_day" value="{{ old('rest_day', $shop->rest_day) }}"
                               placeholder="例：年中無休" maxlength="100"
                               class="flex-1 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                    </div>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">料金目安</th>
                <td class="px-4 py-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 w-16">60分</span>
                            <input type="number" name="price_60" value="{{ old('price_60', $shop->price_60) }}" min="0"
                                   class="w-28 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                            <span class="text-xs text-gray-500">円</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 w-16">90分</span>
                            <input type="number" name="price_90" value="{{ old('price_90', $shop->price_90) }}" min="0"
                                   class="w-28 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                            <span class="text-xs text-gray-500">円</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 w-16">120分</span>
                            <input type="number" name="price_120" value="{{ old('price_120', $shop->price_120) }}" min="0"
                                   class="w-28 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                            <span class="text-xs text-gray-500">円</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 w-16">高級コース</span>
                            <input type="number" name="price_high" value="{{ old('price_high', $shop->price_high) }}" min="0"
                                   class="w-28 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                            <span class="text-xs text-gray-500">円〜</span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">営業エリア</th>
                <td class="px-4 py-3">
                    <textarea name="eigyo_area" rows="3" maxlength="2000"
                              class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400 resize-y">{{ old('eigyo_area', $shop->eigyo_area) }}</textarea>
                    <p class="text-xs text-gray-400 mt-0.5">例：新宿・渋谷・池袋・六本木など</p>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">特色タグ</th>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-3">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="tags[]" value="素人系"
                                   @checked(in_array('素人系', old('tags', $shop->tags ?? [])))
                                   class="w-4 h-4 accent-red-600">
                            <span class="text-sm text-gray-700">素人系</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="tags[]" value="人妻・熟女"
                                   @checked(in_array('人妻・熟女', old('tags', $shop->tags ?? [])))
                                   class="w-4 h-4 accent-red-600">
                            <span class="text-sm text-gray-700">人妻・熟女</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="tags[]" value="巨乳"
                                   @checked(in_array('巨乳', old('tags', $shop->tags ?? [])))
                                   class="w-4 h-4 accent-red-600">
                            <span class="text-sm text-gray-700">巨乳</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="tags[]" value="格安"
                                   @checked(in_array('格安', old('tags', $shop->tags ?? [])))
                                   class="w-4 h-4 accent-red-600">
                            <span class="text-sm text-gray-700">格安</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="tags[]" value="高級"
                                   @checked(in_array('高級', old('tags', $shop->tags ?? [])))
                                   class="w-4 h-4 accent-red-600">
                            <span class="text-sm text-gray-700">高級</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="tags[]" value="ロリ系"
                                   @checked(in_array('ロリ系', old('tags', $shop->tags ?? [])))
                                   class="w-4 h-4 accent-red-600">
                            <span class="text-sm text-gray-700">ロリ系</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="tags[]" value="スレンダー"
                                   @checked(in_array('スレンダー', old('tags', $shop->tags ?? [])))
                                   class="w-4 h-4 accent-red-600">
                            <span class="text-sm text-gray-700">スレンダー</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="tags[]" value="ぽっちゃり"
                                   @checked(in_array('ぽっちゃり', old('tags', $shop->tags ?? [])))
                                   class="w-4 h-4 accent-red-600">
                            <span class="text-sm text-gray-700">ぽっちゃり</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="tags[]" value="美人系"
                                   @checked(in_array('美人系', old('tags', $shop->tags ?? [])))
                                   class="w-4 h-4 accent-red-600">
                            <span class="text-sm text-gray-700">美人系</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="tags[]" value="ギャル系"
                                   @checked(in_array('ギャル系', old('tags', $shop->tags ?? [])))
                                   class="w-4 h-4 accent-red-600">
                            <span class="text-sm text-gray-700">ギャル系</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="tags[]" value="OL系"
                                   @checked(in_array('OL系', old('tags', $shop->tags ?? [])))
                                   class="w-4 h-4 accent-red-600">
                            <span class="text-sm text-gray-700">OL系</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="tags[]" value="コスプレ"
                                   @checked(in_array('コスプレ', old('tags', $shop->tags ?? [])))
                                   class="w-4 h-4 accent-red-600">
                            <span class="text-sm text-gray-700">コスプレ</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">選択したタグで検索絞り込みができます</p>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">プレイスペース</th>
                <td class="px-4 py-3">
                    <input type="text" name="eigyo_space" value="{{ old('eigyo_space', $shop->eigyo_space) }}" maxlength="200"
                           placeholder="例：お客様自宅・ホテル"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-red-400">
                </td>
            </tr>
        </table>
        <div class="mx-4 my-4 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-800 leading-relaxed">
            <p class="font-bold mb-1">🔍 検索結果への反映について</p>
            <ul class="space-y-0.5 text-blue-700">
                <li>・<span class="font-medium">都道府県・エリア</span>：エリア別の検索ページ（例：/female/shinjuku/cast/）に掲載されます。</li>
                <li>・<span class="font-medium">業種</span>：フリーワード検索の対象になります。業種名で検索したユーザーにヒットします。</li>
                <li>・<span class="font-medium">最寄り路線・最寄り駅</span>：駅名・路線名でのエリア検索でヒットするようになります。正確に入力してください。</li>
            </ul>
        </div>
        <div class="px-4 py-4 bg-gray-50 border-t border-gray-100 text-right">
            <button type="submit" class="bg-business-700 hover:bg-business-600 text-white text-sm font-bold px-6 py-2 rounded-lg transition">
                保存する
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script @nonce>
(function() {
    var baseEl   = document.querySelector('[name="base"]');
    var sysEl    = document.getElementById('shop-system-text');
    var lenEl    = document.getElementById('shop-text-len');
    var warn     = document.getElementById('shop-noindex-warning');
    if (!sysEl) return;

    function update() {
        var len = [...(baseEl ? baseEl.value : '')].length + [...sysEl.value].length;
        if (lenEl) lenEl.textContent = len;
        if (warn) warn.style.display = len >= 100 ? 'none' : '';
    }

    sysEl.addEventListener('input', update);
    if (baseEl) baseEl.addEventListener('input', update);
})();
</script>
@endpush
@endsection
