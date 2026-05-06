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

            {{-- LINEログイン --}}
            <a href="{{ route('auth.line') }}/"
               style="background-color:#06C755;"
               onmouseover="this.style.backgroundColor='#05b54d'" onmouseout="this.style.backgroundColor='#06C755'"
               class="flex items-center justify-center gap-3 w-full text-white font-bold py-3 px-6 rounded-lg transition text-sm mb-6">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63.349 0 .631.285.631.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.281.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
                </svg>
                LINEでログイン
            </a>

            <div class="flex items-center gap-3 mb-6">
                <div class="flex-1 border-t border-gray-200"></div>
                <span class="text-xs text-gray-400">または</span>
                <div class="flex-1 border-t border-gray-200"></div>
            </div>

            {{-- メール/パスワードログイン --}}
            <form action="{{ route('login') }}/" method="POST" class="space-y-4">
                @csrf

                @if($errors->has('line'))
                    <p class="text-sm text-red-600 bg-red-50 px-3 py-2 rounded">{{ $errors->first('line') }}</p>
                @endif

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

        <p class="text-center text-xs text-gray-400 mt-6">
            <a href="{{ route('top') }}/" class="hover:text-gray-600">← サイトトップに戻る</a>
        </p>

    </div>
</div>
@endsection
