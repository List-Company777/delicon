@extends('layouts.app')

@section('title', 'パスワード再設定')
@section('robots', 'noindex, follow')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">パスワード再設定</h1>
            <p class="text-sm text-gray-500 mt-1">登録済みのメールアドレスを入力してください</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-8">

            @if(session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-6 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-gray-600 mb-1">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500 @error('email') border-red-400 @enderror">
                    @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="w-full bg-business-700 hover:bg-business-600 text-white font-medium py-2.5 rounded-lg transition text-sm">
                    再設定メールを送信
                </button>
            </form>

            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-sm text-business-700 hover:underline">ログインに戻る</a>
            </div>
        </div>
    </div>
</div>
@endsection
