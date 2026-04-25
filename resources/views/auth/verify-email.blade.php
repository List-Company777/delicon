@extends('layouts.app')

@section('title', 'メールアドレスの確認')
@section('robots', 'noindex, follow')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <div class="w-16 h-16 bg-business-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-business-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>

            <h1 class="text-xl font-bold text-gray-800 mb-2">メールアドレスを確認してください</h1>
            <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                ご登録のメールアドレスに確認メールを送信しました。<br>
                メール内のリンクをクリックして認証を完了してください。
            </p>

            @if(session('resent'))
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
                    確認メールを再送しました。
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                        class="w-full bg-business-700 hover:bg-business-600 text-white font-medium py-2.5 rounded-lg transition text-sm">
                    確認メールを再送する
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 transition">
                    ログアウト
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
