@extends('layouts.app')

@section('title', '新しいパスワードを設定')
@section('robots', 'noindex, follow')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">新しいパスワードを設定</h1>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-8">

            <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="block text-sm text-gray-600 mb-1">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email', request('email')) }}" required autofocus
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500 @error('email') border-red-400 @enderror">
                    @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">新しいパスワード <span class="text-xs text-gray-400">（8文字以上）</span></label>
                    <input type="password" name="password" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500 @error('password') border-red-400 @enderror">
                    @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">パスワード（確認）</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500">
                </div>

                <button type="submit"
                        class="w-full bg-business-700 hover:bg-business-600 text-white font-medium py-2.5 rounded-lg transition text-sm">
                    パスワードを設定する
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
