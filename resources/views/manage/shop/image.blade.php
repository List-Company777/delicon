@extends('layouts.app')
@section('title', 'メイン画像の管理')
@section('content')
<div class="bg-business-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold text-lg">店舗管理</h1>
        <div class="flex items-center gap-4 text-sm">
            <span class="opacity-70">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}/" method="POST">
                @csrf
                <button type="submit" class="opacity-70 hover:opacity-100 transition">ログアウト</button>
            </form>
        </div>
    </div>
</div>

@include('manage._nav')

<div class="max-w-4xl mx-auto px-4 pb-12">
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <h2 class="text-lg font-bold text-gray-800 mb-2">バナー画像</h2>
    <p class="text-sm text-gray-500 mb-4">店舗詳細ページの上部に横長バナーとして表示されます。アップロード時に自動で 5:2 比率（900×360px）にトリミングされます。</p>

    {{-- ランキング説明バナー --}}
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4">
        <p class="text-sm font-bold text-amber-800 mb-1">📸 画像を登録すると掲載できる場所が増えます</p>
        <p class="text-xs text-amber-700 leading-relaxed">
            画像がなくてもお店は検索結果に表示されますが、<strong>画像のある店舗はより多くの場所に掲載</strong>されます。
        </p>
    </div>

    {{-- 現在の画像 --}}
    @if($shop->main_image)
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <p class="text-sm font-bold text-gray-600 mb-3">現在の画像</p>
            <div class="relative w-full aspect-[5/2] rounded-lg overflow-hidden bg-gray-100">
                <x-shop-image :src="str_replace('main.jpg', 'main_banner.jpg', $shop->main_image)" :alt="$shop->name" class="absolute inset-0 w-full h-full object-cover" />
            </div>
            <form action="{{ route('manage.shop.image.destroy') }}" method="POST" class="mt-4 text-right"
                  onsubmit="return confirm('画像を削除しますか？')">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm text-red-500 hover:text-red-700">画像を削除する</button>
            </form>
        </div>
    @endif

    {{-- アップロードフォーム --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <p class="text-sm font-bold text-gray-600 mb-1">{{ $shop->main_image ? '画像を差し替える' : '画像をアップロードする' }}</p>
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded text-xs text-blue-700 leading-relaxed">
            <p class="font-bold mb-1">入稿推奨サイズ：900 × 360px（5:2）以上</p>
            <p>・形式：JPEG / PNG / WebP</p>
            <p>・ファイルサイズ：5MB 以下</p>
            <p>・アップロード時に自動で 900×360px（5:2）にクロップされます</p>
            <p>・被写体は中央に配置してください（端が切れる場合があります）</p>
        </div>
        <form action="{{ route('manage.shop.image.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label class="inline-block cursor-pointer mb-3">
                <span class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 transition">
                    📁 ファイルを選択
                </span>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required class="hidden"
                       onchange="this.parentElement.querySelector('span').textContent = this.files[0]?.name ?? 'ファイルを選択'">
            </label>
            @error('image')<p class="text-xs text-red-500 mb-2">{{ $message }}</p>@enderror
            <button type="submit" class="bg-business-700 hover:bg-business-600 text-white text-sm font-bold px-6 py-2 rounded-lg transition">
                アップロード
            </button>
        </form>
    </div>
</div>
@endsection
