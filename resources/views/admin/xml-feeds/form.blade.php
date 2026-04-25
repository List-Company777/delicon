@extends('layouts.admin')
@section('title', $feed ? '連携先を編集' : '連携先を追加')
@section('content')
<div class="bg-gray-800 text-white py-4">
    <div class="max-w-3xl mx-auto px-4">
        <h1 class="font-bold">Admin › XML外部連携 › {{ $feed ? '編集' : '新規追加' }}</h1>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 py-8">
    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $feed ? route('admin.xml-feeds.update', $feed) : route('admin.xml-feeds.store') }}"
          method="POST" class="space-y-6">
        @csrf
        @if($feed) @method('PUT') @endif

        <div class="bg-white rounded-xl shadow-sm p-6 space-y-5">
            <h2 class="text-sm font-bold text-gray-700 border-b border-gray-100 pb-3">基本情報</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">サイト名 <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $feed?->name) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">
                        スラッグ <span class="text-red-400">*</span>
                        <span class="text-xs text-gray-400">（英数字とハイフン）</span>
                    </label>
                    <input type="text" name="slug" value="{{ old('slug', $feed?->slug) }}" required
                           {{ $feed ? 'readonly class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-400"' : 'class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"' }}>
                    @if($feed)
                        <p class="text-xs text-gray-400 mt-1">スラッグは変更できません（shops/jobs テーブルの xml_source と紐付いています）</p>
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">フィードURL</label>
                <input type="url" name="url" value="{{ old('url', $feed?->url) }}"
                       placeholder="https://example.com/feed.xml"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                <p class="text-xs text-gray-400 mt-1">空欄の場合、www.up-stage.info用は .env の UPSTAGE_XML_FEED_URL を使用します</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">連携タイプ <span class="text-red-400">*</span></label>
                    <select name="feed_type" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                        <option value="staff_jobs"    {{ old('feed_type', $feed?->feed_type) === 'staff_jobs'    ? 'selected' : '' }}>スタッフ求人（男性向け）</option>
                        <option value="cast_jobs"     {{ old('feed_type', $feed?->feed_type) === 'cast_jobs'     ? 'selected' : '' }}>キャスト求人（女性向け）</option>
                        <option value="business_info" {{ old('feed_type', $feed?->feed_type) === 'business_info' ? 'selected' : '' }}>営業情報（未実装）</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">状態</label>
                    <select name="status"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                        <option value="active"   {{ old('status', $feed?->status ?? 'active') === 'active'   ? 'selected' : '' }}>アクティブ</option>
                        <option value="inactive" {{ old('status', $feed?->status) === 'inactive' ? 'selected' : '' }}>停止</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="hidden" name="is_own_site" value="0">
                <input type="checkbox" name="is_own_site" id="is_own_site" value="1"
                       {{ old('is_own_site', $feed?->is_own_site) ? 'checked' : '' }}
                       class="rounded">
                <label for="is_own_site" class="text-sm text-gray-700">
                    <span class="font-medium">自社サイト</span>
                    <span class="text-gray-400">— 店舗の引き継ぎ登録・追加案内・入札単価XML連動を有効にする</span>
                </label>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 space-y-5">
            <h2 class="text-sm font-bold text-gray-700 border-b border-gray-100 pb-3">カテゴリ設定</h2>

            <div>
                <label class="block text-sm text-gray-600 mb-1">
                    取り込むカテゴリ
                    <span class="text-xs text-gray-400">（1行1カテゴリ または カンマ区切り。空欄=全件取込）</span>
                </label>
                <textarea name="allowed_categories_text" rows="5"
                          placeholder="キャバクラ&#10;ガールズバー&#10;ホスト"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-blue-500">{{ old('allowed_categories_text', $feed ? implode("\n", $feed->allowed_categories ?? []) : '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">
                    カテゴリ→ジャンルマッピング
                    <span class="text-xs text-gray-400">（JSON形式: {"カテゴリ名": genre_id}）</span>
                </label>
                <textarea name="category_genre_map_json" rows="8"
                          placeholder='{"キャバクラ": 1, "ガールズバー": 4}'
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-blue-500">{{ old('category_genre_map_json', $feed ? json_encode($feed->category_genre_map, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '') }}</textarea>
                @error('category_genre_map_json')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
                <div class="mt-2 text-xs text-gray-400">
                    ジャンルID: キャバクラ=1 / ホストクラブ=2 / ボーイズバー=3 / ガールズバー=4 / スナック=5 / ラウンジ=6 / コンカフェ=7 / クラブ=8 / バー=9 / パブ=11
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 space-y-5" x-data="{}">
            <h2 class="text-sm font-bold text-gray-700 border-b border-gray-100 pb-3">
                入札単価XML連動
                <span class="text-xs font-normal text-gray-400 ml-2">自社サイトのみ有効</span>
            </h2>
            <p class="text-xs text-gray-500">XMLフィード内の該当フィールド名を指定すると、インポート時に入札単価・月次予算が自動同期されます。</p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">入札単価フィールド名</label>
                    <input type="text" name="bid_price_xml_field"
                           value="{{ old('bid_price_xml_field', $feed?->bid_price_xml_field) }}"
                           placeholder="nightwork_bid_price"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-blue-500">
                    <p class="text-xs text-gray-400 mt-1">XML の &lt;nightwork_bid_price&gt;50&lt;/nightwork_bid_price&gt; など</p>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">月次予算フィールド名</label>
                    <input type="text" name="monthly_budget_xml_field"
                           value="{{ old('monthly_budget_xml_field', $feed?->monthly_budget_xml_field) }}"
                           placeholder="nightwork_monthly_budget"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-blue-500">
                    <p class="text-xs text-gray-400 mt-1">XML の &lt;nightwork_monthly_budget&gt;10000&lt;/nightwork_monthly_budget&gt; など</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('admin.xml-feeds.index') }}" class="text-sm text-gray-500 hover:underline">← 戻る</a>
            <button type="submit"
                    class="bg-gray-800 hover:bg-gray-700 text-white text-sm font-bold px-6 py-2.5 rounded-lg transition">
                {{ $feed ? '更新する' : '追加する' }}
            </button>
        </div>
    </form>

    @if($feed)
    <div class="bg-white rounded-xl shadow-sm p-6 mt-6 space-y-4">
        <h2 class="text-sm font-bold text-gray-700 border-b border-gray-100 pb-3">ベンダー予算残高</h2>

        <div class="flex items-center gap-6">
            <div>
                <p class="text-xs text-gray-500 mb-1">現在の残高</p>
                @if($feed->budget_balance === null)
                    <p class="text-lg font-bold text-green-600">無制限</p>
                @else
                    <p class="text-lg font-bold {{ $feed->budget_balance > 0 ? 'text-gray-800' : 'text-red-500' }}">
                        ¥{{ number_format($feed->budget_balance) }}
                    </p>
                    @if($feed->budget_balance === 0)
                        <p class="text-xs text-red-500 mt-0.5">残高0 — クリック課金は無料扱い（表示は継続）</p>
                    @endif
                @endif
            </div>
        </div>

        <form action="{{ route('admin.xml-feeds.add-budget', $feed) }}" method="POST" class="flex items-end gap-3">
            @csrf
            <div>
                <label class="block text-sm text-gray-600 mb-1">追加金額（円）</label>
                <input type="number" name="amount" min="1" max="9999999"
                       placeholder="例：50000"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:border-blue-500">
            </div>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold px-5 py-2 rounded-lg transition">
                予算を追加
            </button>
        </form>

        <p class="text-xs text-gray-400">
            残高を無制限に戻す場合は、フィードのスラッグを確認のうえ直接DBで <code>budget_balance = NULL</code> に設定してください。
        </p>
    </div>
    @endif
</div>
@endsection
