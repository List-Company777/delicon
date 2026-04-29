@extends('layouts.app')
@section('title', '店舗基本情報の編集')
@section('content')
<div class="bg-business-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold">店舗管理</h1>
        <form action="{{ route('logout') }}" method="POST">
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

    <form action="{{ route('manage.shop.update') }}" method="POST" class="bg-white rounded-xl shadow-sm overflow-hidden">
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
                    <p class="text-xs text-gray-400 mt-0.5">変更は<a href="{{ route('manage.contact') }}" class="underline hover:text-gray-600">お問い合わせフォーム</a>からご連絡ください</p>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">都道府県</th>
                <td class="px-4 py-3">
                    <span class="text-gray-700">{{ $shop->prefecture?->name ?? '—' }}</span>
                    <p class="text-xs text-gray-400 mt-0.5">変更は<a href="{{ route('manage.contact') }}" class="underline hover:text-gray-600">お問い合わせフォーム</a>からご連絡ください</p>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">エリア</th>
                <td class="px-4 py-3">
                    <span class="text-gray-700">{{ $shop->area?->name ?? '—' }}</span>
                    <p class="text-xs text-gray-400 mt-0.5">変更は<a href="{{ route('manage.contact') }}" class="underline hover:text-gray-600">お問い合わせフォーム</a>からご連絡ください</p>
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
@endsection
