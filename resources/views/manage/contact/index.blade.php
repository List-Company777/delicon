@extends('layouts.app')

@section('title', 'お問い合わせ・要望')
@section('robots', 'noindex, follow')

@section('content')
<div class="bg-business-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold text-lg">店舗管理</h1>
        <div class="flex items-center gap-4 text-sm">
            <span class="opacity-70">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="opacity-70 hover:opacity-100 transition">ログアウト</button>
            </form>
        </div>
    </div>
</div>

@include('manage._nav')

<div class="max-w-4xl mx-auto px-4 pb-12">

    <h2 class="text-lg font-bold text-gray-800 mb-1">お問い合わせ・要望</h2>
    <p class="text-sm text-gray-500 mb-3">掲載内容の変更依頼・機能への要望などをお気軽にご送信ください。</p>
    <p class="text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 mb-6 leading-relaxed">
        エリアや業種の追加などのご要望は参考にさせていただきますが、サイトポリシーなどに照らしてお応えできない場合もございます。あらかじめご了承ください。
    </p>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form action="{{ route('manage.contact.send') }}" method="POST" class="space-y-5">
            @csrf

            {{-- 店舗名（表示のみ） --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">店舗名</label>
                <p class="text-sm text-gray-800 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                    {{ $shop->name }}
                </p>
            </div>

            {{-- カテゴリ --}}
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                    カテゴリ <span class="text-red-500">*</span>
                </label>
                <select id="category" name="category"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-business-400 @error('category') border-red-400 @enderror">
                    <option value="">選択してください</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
                @error('category')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- 件名 --}}
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                    件名 <span class="text-red-500">*</span>
                </label>
                <input type="text" id="subject" name="subject"
                       value="{{ old('subject', '[' . $shop->name . ' ID:' . $shop->id . '] ') }}"
                       maxlength="100"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-business-400 @error('subject') border-red-400 @enderror">
                @error('subject')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- 本文 --}}
            <div>
                <label for="body" class="block text-sm font-medium text-gray-700 mb-1">
                    内容 <span class="text-red-500">*</span>
                </label>
                <textarea id="body" name="body" rows="8"
                          maxlength="3000"
                          placeholder="お問い合わせ内容をご入力ください（3000文字以内）"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-business-400 resize-y @error('body') border-red-400 @enderror">{{ old('body') }}</textarea>
                @error('body')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="w-full bg-business-700 hover:bg-business-600 text-white font-medium py-2.5 rounded-lg transition text-sm">
                    送信する
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
