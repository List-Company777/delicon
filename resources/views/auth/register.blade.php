@extends('layouts.app')

@section('title', '店舗登録')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">店舗登録</h1>
            @if(session('line_name'))
                <p class="text-sm text-gray-500 mt-1">
                    LINE認証完了：<span class="font-medium text-gray-700">{{ session('line_name') }}</span> さん
                </p>
            @else
                <p class="text-sm text-gray-500 mt-1">アカウントと店舗情報を登録してください</p>
            @endif
        </div>

        <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-4 mb-4">
            <p class="text-sm font-bold text-green-800 mb-2">掲載は基本無料です</p>
            <ul class="text-xs text-green-700 space-y-1">
                <li>✓ 求人・営業情報を無料で掲載</li>
                <li>✓ 登録はメールまたはLINEで最短5分</li>
                <li>✓ 最短即日で掲載可能</li>
            </ul>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-3 mb-6">
            <p class="text-xs text-red-700 leading-relaxed">
                <span class="font-bold">【掲載対象外】</span>
                風俗営業等規制法に基づく許可・届出のない違法な営業形態の店舗、およびソープランド・デリヘル・ファッションヘルス等のアダルト系業態は掲載をお断りしております。
            </p>
        </div>

        @if(isset($partner) && $partner)
        <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-700 text-sm px-4 py-3 rounded-lg">
            <span class="font-medium">{{ $partner->company_name }}</span> さんのご紹介でご登録いただいています
        </div>
        @endif

        {{-- LINEで登録（LINEアカウントなし or LINE認証済みの場合は非表示） --}}
        @if(!session('line_user_id'))
        <a href="{{ route('auth.line') }}"
           style="background-color:#06C755;"
           onmouseover="this.style.backgroundColor='#05b54d'" onmouseout="this.style.backgroundColor='#06C755'"
           class="flex items-center justify-center gap-3 w-full text-white font-bold py-3 px-6 rounded-lg transition text-sm mb-4">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.630 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63.349 0 .631.285.631.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.630 0 .344-.281.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
            </svg>
            LINEで登録
        </a>

        <div class="flex items-center gap-3 mb-4">
            <div class="flex-1 border-t border-gray-200"></div>
            <span class="text-xs text-gray-400">またはメールで登録</span>
            <div class="flex-1 border-t border-gray-200"></div>
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
            <form action="{{ route('register') }}" method="POST" class="space-y-5">
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
                                <span class="text-xs text-gray-400 font-normal">（LINEが使えない場合のログイン用）</span>
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

                    {{-- www.up-stage.info連携店舗の引き継ぎ検索 --}}
                    <div class="mb-5 border border-blue-200 bg-blue-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-blue-800 font-bold mb-2"><a href="https://www.up-stage.info/" target="_blank" rel="noopener" class="underline hover:text-blue-900">アップステージ</a>に掲載中のお店をお持ちの方</p>
                        <p class="text-xs text-blue-600 mb-2 leading-relaxed">店舗名またはアップステージの店舗IDで検索して引き継ぎ登録すると、ボーイ求人が自動連携されます。</p>
                        <div class="relative">
                            <input type="text" x-model="searchQuery" @input="onSearchInput()"
                                   placeholder="店舗名または店舗IDで検索..."
                                   class="w-full border border-blue-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 bg-white">
                            <div x-show="searching" class="absolute right-3 top-2.5 text-xs text-gray-400">検索中...</div>
                        </div>
                        <div x-show="searchResults.length > 0" class="mt-1 bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                            <template x-for="shop in searchResults" :key="shop.id">
                                <button type="button" @click="selectShop(shop)"
                                        class="w-full text-left px-4 py-2.5 hover:bg-blue-50 border-b border-gray-100 last:border-0 transition">
                                    <p class="text-sm font-medium text-gray-800" x-text="shop.name"></p>
                                    <p class="text-xs text-gray-500 mt-0.5"
                                       x-text="[shop.genre, shop.prefecture, shop.area].filter(Boolean).join(' / ')"></p>
                                </button>
                            </template>
                        </div>
                        <div x-show="searchQuery.length >= 1 && !searching && searchResults.length === 0"
                             class="mt-1 text-xs text-gray-400 px-1">
                            該当する店舗が見つかりませんでした
                        </div>
                    </div>

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
                            <a href="{{ route('terms') }}" target="_blank" class="text-business-700 hover:underline">サービス利用規約</a>
                            に同意します
                        </span>
                    </label>
                    <label class="flex items-start gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="agree_advertiser" value="1" required
                               class="mt-0.5 rounded @error('agree_advertiser') outline outline-red-400 @enderror">
                        <span>
                            <a href="{{ route('advertiser') }}" target="_blank" class="text-business-700 hover:underline">掲載規約</a>
                            に同意します
                        </span>
                    </label>
                    <label class="flex items-start gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="agree_privacy" value="1" required
                               class="mt-0.5 rounded @error('agree_privacy') outline outline-red-400 @enderror">
                        <span>
                            <a href="{{ route('privacy') }}" target="_blank" class="text-business-700 hover:underline">プライバシーポリシー</a>
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
                <a href="{{ route('login') }}" class="text-business-700 hover:underline">ログイン</a>
            </div>
        </div>

    </div>
</div>
@endsection
