@extends('layouts.app')

@section('title', '店舗ログイン')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">店舗管理ログイン</h1>
            <p class="text-sm text-gray-500 mt-1">掲載店舗の担当者様向けページです</p>
            <p class="text-xs text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2 mt-3 inline-block">
                掲載は基本無料 ―
                <a href="{{ route('register') }}/" class="font-bold underline hover:text-green-900">新規登録はこちら</a>
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-8">

            {{-- メール/パスワードログイン --}}
            <form action="{{ route('login') }}/" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm text-gray-600 mb-1">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500 @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">パスワード</label>
                    <input type="password" name="password" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500">
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-gray-600">
                        <input type="checkbox" name="remember" class="rounded">
                        ログイン状態を保持
                    </label>
                    <a href="{{ route('password.request') }}/" class="text-gray-400 hover:text-business-700 transition">パスワードを忘れた方</a>
                </div>

                <button type="submit"
                        class="w-full bg-business-700 hover:bg-business-600 text-white font-bold py-2.5 rounded-lg transition text-sm">
                    ログイン
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-gray-500">
                アカウントをお持ちでない方は
                <a href="{{ route('register') }}/" class="text-business-700 hover:underline">新規登録</a>
            </div>
        </div>

        {{-- 一般ユーザー向け --}}
        <div class="mt-6 bg-surface-700 border border-surface-400 rounded-xl p-4 text-center">
            <p class="text-xs text-[#B0AEAD] mb-2">風俗情報を楽しむ一般ユーザーの方はこちら</p>
            <div class="flex gap-2 justify-center">
                <a href="{{ route('visitor.register') }}/"
                   class="text-sm font-bold text-white bg-deli-500 hover:bg-deli-400 px-4 py-2 rounded-lg transition">無料会員登録</a>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            <a href="{{ route('top') }}/" class="hover:text-gray-600">← サイトトップに戻る</a>
        </p>

    </div>
</div>
@endsection
