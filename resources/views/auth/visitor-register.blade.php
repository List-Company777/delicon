@extends('layouts.app')
@section('title', '無料会員登録')
@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-surface-500 border border-surface-300 rounded-2xl p-8">
        <h1 class="text-xl font-bold text-[#F0ECE4] mb-2">会員登録</h1>
        <p class="text-sm text-[#B0AEAD] mb-3">口コミ・お気に入り登録・新人通知などを利用するには会員登録が必要です。</p>
        <ul class="text-xs text-[#8A8A9E] space-y-1 mb-6">
            <li class="flex items-center gap-1.5"><span class="text-deli-400">✓</span> 口コミの投稿・閲覧</li>
            <li class="flex items-center gap-1.5"><span class="text-deli-400">✓</span> お気に入り店舗の保存</li>
            <li class="flex items-center gap-1.5"><span class="text-deli-400">✓</span> 新人キャスト通知の受け取り</li>
        </ul>

        @if($errors->any())
        <div class="mb-4 bg-deli-500/10 border border-deli-500/30 rounded p-3 text-sm text-deli-400">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
        @endif

        <form action="{{ route('visitor.register.store') }}/" method="POST" class="space-y-4">
            @csrf
            @if($redirect ?? null)
            <input type="hidden" name="redirect" value="{{ $redirect }}">
            @endif
            <div>
                <label class="block text-sm text-[#B0AEAD] mb-1">ニックネーム <span class="text-deli-400">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="50"
                       placeholder="表示名（例：田中さん）"
                       class="w-full bg-surface-400 border border-surface-300 rounded-lg px-4 py-2.5 text-[#E8E4DC] text-sm focus:outline-none focus:border-deli-500">
                <p class="text-xs text-[#6A6A7E] mt-1">実名ではなくハンドルネームを推奨します。店舗の管理画面にお気に入り登録者として表示されることがあります。</p>
            </div>
            <div>
                <label class="block text-sm text-[#B0AEAD] mb-1">メールアドレス <span class="text-deli-400">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required maxlength="200"
                       class="w-full bg-surface-400 border border-surface-300 rounded-lg px-4 py-2.5 text-[#E8E4DC] text-sm focus:outline-none focus:border-deli-500">
                <p class="text-xs text-[#6A6A7E] mt-1">実名ではなくハンドルネームを推奨します。店舗の管理画面にお気に入り登録者として表示されることがあります。</p>
            </div>
            <div>
                <label class="block text-sm text-[#B0AEAD] mb-1">パスワード <span class="text-deli-400">*</span></label>
                <input type="password" name="password" required minlength="8"
                       class="w-full bg-surface-400 border border-surface-300 rounded-lg px-4 py-2.5 text-[#E8E4DC] text-sm focus:outline-none focus:border-deli-500">
                <p class="text-xs text-[#6A6A7E] mt-1">実名ではなくハンドルネームを推奨します。店舗の管理画面にお気に入り登録者として表示されることがあります。</p>
            </div>
            <div>
                <label class="block text-sm text-[#B0AEAD] mb-1">パスワード確認 <span class="text-deli-400">*</span></label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="w-full bg-surface-400 border border-surface-300 rounded-lg px-4 py-2.5 text-[#E8E4DC] text-sm focus:outline-none focus:border-deli-500">
                <p class="text-xs text-[#6A6A7E] mt-1">実名ではなくハンドルネームを推奨します。店舗の管理画面にお気に入り登録者として表示されることがあります。</p>
            </div>
            <button type="submit"
                    class="w-full bg-deli-500 hover:bg-deli-400 text-white font-bold py-3 rounded-lg transition mt-2">
                無料登録する
            </button>
        </form>
        <p class="text-center text-sm text-[#8A8A9E] mt-4">
            すでに登録済みの方は <a href="{{ route('login') }}/" class="text-gold-400 hover:underline">ログイン</a>
        </p>
    </div>
</div>
@endsection
