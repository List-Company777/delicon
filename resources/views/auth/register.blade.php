@extends('layouts.app')

@section('title', '店舗登録')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">店舗登録</h1>
            <p class="text-sm text-gray-500 mt-1">アカウントと店舗情報を登録してください</p>
            </div>

        <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-4 mb-4">
            <p class="text-sm font-bold text-green-800 mb-2">掲載は基本無料です</p>
            <ul class="text-xs text-green-700 space-y-1">
                <li>✓ 最短即日で掲載可能</li>
            </ul>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-3 mb-6">
            <p class="text-xs text-red-700 leading-relaxed">
                <span class="font-bold">【掲載禁止】</span>
                風営法に基づく許可・届出を受けていない違法な営業、または売春・児童ポルノ等の違法行為を業とする店舗の掲載はお断りしております。
            </p>
            <p class="text-xs text-red-700 leading-relaxed mt-2">
                虚偽・ぼったくりの登録はお断りしております。業界の評判を落とすような行為をされる予定の場合には、登録・利用をお控えください。
            </p>
        </div>

        @if(isset($partner) && $partner)
        <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-700 text-sm px-4 py-3 rounded-lg">
            <span class="font-medium">{{ $partner->company_name }}</span> さんのご紹介でご登録いただいています
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm p-8"
             x-data="{
                 claimMode: {{ old('claim_shop_id') ? 'true' : 'false' }},
                 claimShopId: '{{ old('claim_shop_id', '') }}',
                 claimShopName: '{{ old('claim_shop_name', '') }}',
                 claimShopMeta: '{{ old('claim_shop_meta', '') }}',
                 searchQuery: '',
                 searchResults: [],
                 searching: false,
                 searchTimeout: null,
                 searchUrl: '{{ route('api.xml-shops.search') }}',

                 onSearchInput() {
                     clearTimeout(this.searchTimeout);
                     if (this.searchQuery.length < 1) { this.searchResults = []; return; }
                     this.searching = true;
                     this.searchTimeout = setTimeout(() => {
                         fetch(this.searchUrl + '?name=' + encodeURIComponent(this.searchQuery))
                             .then(r => r.json())
                             .then(data => { this.searchResults = data; this.searching = false; })
                             .catch(() => { this.searching = false; });
                     }, 400);
                 },

                 selectShop(shop) {
                     this.claimMode = true;
                     this.claimShopId = shop.id;
                     this.claimShopName = shop.name;
                     this.claimShopMeta = [shop.genre, shop.prefecture, shop.area].filter(Boolean).join(' / ');
                     this.searchResults = [];
                     this.searchQuery = '';
                 },

                 cancelClaim() {
                     this.claimMode = false;
                     this.claimShopId = '';
                     this.claimShopName = '';
                     this.claimShopMeta = '';
                 }
             }">
            <form action="{{ route('register') }}/" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="referral_code" value="{{ session('referral_code', $partner?->referral_code ?? '') }}">
                <input type="hidden" name="claim_shop_id" :value="claimShopId">
                <input type="hidden" name="claim_shop_name" :value="claimShopName">
                <input type="hidden" name="claim_shop_meta" :value="claimShopMeta">

                {{-- 担当者情報 --}}
                <div class="pb-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-600 mb-4">担当者情報</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">お名前 <span class="text-red-400">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500 @error('name') border-red-400 @enderror">
                            @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">
                                メールアドレス <span class="text-red-400">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500 @error('email') border-red-400 @enderror">
                            @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">パスワード <span class="text-red-400">*</span></label>
                            <input type="password" name="password" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500 @error('password') border-red-400 @enderror">
                            <p class="text-xs text-gray-400 mt-1">8文字以上</p>
                            @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">パスワード（確認）<span class="text-red-400">*</span></label>
                            <input type="password" name="password_confirmation" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500">
                        </div>
                    </div>
                </div>

                {{-- 店舗情報（通常登録 / 引き継ぎ登録） --}}

                {{-- 引き継ぎ登録：選択済み --}}
                <div x-show="claimMode" style="display:none">
                    <h2 class="text-sm font-bold text-gray-600 mb-3">引き継ぐ店舗</h2>
                    <div class="bg-blue-50 border border-blue-300 rounded-lg px-4 py-3 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold text-blue-900" x-text="claimShopName"></p>
                            <p class="text-xs text-blue-600 mt-0.5" x-text="claimShopMeta"></p>
                        </div>
                        <button type="button" @click="cancelClaim()"
                                class="text-xs text-gray-400 hover:text-gray-600 whitespace-nowrap">変更</button>
                    </div>
                    <div class="mt-3">
                        <label class="block text-sm text-gray-600 mb-1">電話番号（未設定の場合）</label>
                        <input type="tel" name="tel" value="{{ old('tel') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500">
                    </div>
                </div>

                {{-- 通常登録：店舗情報フォーム --}}
                <div x-show="!claimMode"
                     x-data="{
                         prefectureId: '{{ old('prefecture_id') }}',
                         areaId: '{{ old('area_id') }}',
                         allAreas: {{ Js::from($areas) }},
                         get filteredAreas() {
                             if (!this.prefectureId) return [];
                             return this.allAreas.filter(a => a.prefecture_id == this.prefectureId);
                         },
                         get noAreas() {
                             return this.prefectureId && this.filteredAreas.length === 0;
                         },
                         onPrefectureChange() {
                             if (!this.filteredAreas.some(a => a.id == this.areaId)) {
                                 this.areaId = '';
                             }
                         }
                     }">
                    <h2 class="text-sm font-bold text-gray-600 mb-4">店舗情報</h2>


                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">店舗名 <span class="text-red-400">*</span></label>
                            <input type="text" name="shop_name" value="{{ old('shop_name') }}" :required="!claimMode"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500 @error('shop_name') border-red-400 @enderror">
                            @error('shop_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">業種 <span class="text-red-400">*</span></label>
                            <select name="genre_id" :required="!claimMode"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500 @error('genre_id') border-red-400 @enderror">
                                <option value="">選択してください</option>
                                @foreach($genres as $genre)
                                    <option value="{{ $genre->id }}" {{ old('genre_id') == $genre->id ? 'selected' : '' }}>
                                        {{ $genre->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('genre_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">都道府県 <span class="text-red-400">*</span></label>
                            <select name="prefecture_id" :required="!claimMode" x-model="prefectureId" @change="onPrefectureChange()"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500 @error('prefecture_id') border-red-400 @enderror">
                                <option value="">選択してください</option>
                                @foreach($prefectures as $prefecture)
                                    <option value="{{ $prefecture->id }}">{{ $prefecture->name }}</option>
                                @endforeach
                            </select>
                            @error('prefecture_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="prefectureId">
                            <label class="block text-sm text-gray-600 mb-1">エリア</label>
                            <template x-if="!noAreas">
                                <select name="area_id" x-model="areaId"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500">
                                    <option value="">選択してください</option>
                                    <template x-for="area in filteredAreas" :key="area.id">
                                        <option :value="area.id" :selected="area.id == areaId" x-text="area.name"></option>
                                    </template>
                                </select>
                            </template>
                            <template x-if="noAreas">
                                <p class="text-sm text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                                    選択された都道府県のエリアはまだ準備中です。登録後、お問い合わせフォームよりご連絡ください。
                                </p>
                            </template>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">電話番号</label>
                            <input type="tel" name="tel" value="{{ old('tel') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500">
                        </div>
                    </div>
                </div>

                {{-- 同意チェックボックス --}}
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-2">
                    <label class="flex items-start gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="agree_terms" value="1" required
                               class="mt-0.5 rounded @error('agree_terms') outline outline-red-400 @enderror">
                        <span>
                            <a href="{{ route('terms') }}/" target="_blank" class="text-business-700 hover:underline">サービス利用規約</a>
                            に同意します
                        </span>
                    </label>
                    <label class="flex items-start gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="agree_advertiser" value="1" required
                               class="mt-0.5 rounded @error('agree_advertiser') outline outline-red-400 @enderror">
                        <span>
                            <a href="{{ route('advertiser') }}/" target="_blank" class="text-business-700 hover:underline">掲載規約</a>
                            に同意します
                        </span>
                    </label>
                    <label class="flex items-start gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="agree_privacy" value="1" required
                               class="mt-0.5 rounded @error('agree_privacy') outline outline-red-400 @enderror">
                        <span>
                            <a href="{{ route('privacy') }}/" target="_blank" class="text-business-700 hover:underline">プライバシーポリシー</a>
                            に同意します
                        </span>
                    </label>
                    @error('agree_terms')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    @error('agree_advertiser')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    @error('agree_privacy')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <p class="text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 leading-relaxed">
                    ご希望のエリアや業種がリストにない場合は、登録後に管理画面のお問い合わせフォームからご連絡ください。
                </p>

                <p class="text-xs text-gray-400 leading-relaxed">
                    登録後、内容を確認の上で掲載を開始します。掲載開始まで数日かかる場合があります。
                </p>

                <button type="submit"
                        class="w-full bg-business-700 hover:bg-business-600 text-white font-bold py-3 rounded-lg transition text-sm">
                    登録する
                </button>
            </form>

            <div class="mt-4 text-center text-sm text-gray-500">
                すでにアカウントをお持ちの方は
                <a href="{{ route('login') }}/" class="text-business-700 hover:underline">ログイン</a>
            </div>
        </div>

    </div>
</div>
@endsection
