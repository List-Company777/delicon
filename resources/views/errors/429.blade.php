@extends('layouts.app')

@section('title', 'アクセスが一時的に制限されています')
@section('robots', 'noindex, nofollow')

@section('content')
<div class="max-w-lg mx-auto px-4 py-16 text-center">

    <p class="text-6xl font-bold text-orange-400 mb-4">429</p>
    <h1 class="text-xl font-bold text-gray-700 mb-3">アクセスが一時的に制限されています</h1>
    <p class="text-gray-500 text-sm mb-8">
        短時間に複数回のログイン試行があったため、セキュリティのためアクセスを一時的に制限しました。<br>
        しばらく時間をおいてから再度お試しください。
    </p>

    <div class="bg-orange-50 border border-orange-200 rounded-xl px-6 py-4 mb-8 text-left">
        <p class="text-xs font-bold text-orange-700 mb-2">制限が解除されるまでの目安</p>
        <p class="text-sm text-orange-800">約 <span class="font-bold">15分</span> 後に再試行できます。</p>
    </div>

    <div class="space-y-3">
        <a href="{{ route('login') }}"
           class="block w-full px-6 py-3 bg-business-700 hover:bg-business-600 text-white text-sm font-bold rounded-lg transition">
            ログインページへ戻る
        </a>
        <a href="{{ route('password.request') }}"
           class="block w-full px-6 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm rounded-lg transition">
            パスワードをお忘れの方はこちら
        </a>
    </div>

</div>
@endsection
