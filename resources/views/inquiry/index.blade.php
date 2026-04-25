@extends('layouts.app')
@section('title', 'お問い合わせ')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">

    <h1 class="text-2xl font-bold text-gray-800 mb-2">お問い合わせ</h1>
    <p class="text-sm text-gray-500 mb-8">サービスに関するご質問・ご意見をお送りください。</p>

    {{-- 注意書き --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 mb-8 text-sm text-amber-800">
        <p class="font-bold mb-1">店舗・求人情報のご指摘について</p>
        <p>掲載されている店舗・求人情報への通報（閉店・虚偽情報・不適切内容等）は、各店舗ページ・求人ページ下部の「通報する」フォームからお願いします。</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-5 py-4 mb-6 text-sm">
        お問い合わせを送信しました。内容を確認の上、ご連絡いたします。
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 mb-6 text-sm">
        @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <div x-data="{ step: 'input', form: {} }"
         x-init="$watch('step', v => v === 'input' && ($el.scrollIntoView({ behavior: 'smooth' })))">

        {{-- 入力フォーム --}}
        <form @submit.prevent="
                form = {
                    name:     $refs.name.value,
                    email:    $refs.email.value,
                    category: $refs.category.value,
                    body:     $refs.body.value,
                };
                step = 'confirm';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            "
            x-show="step === 'input'"
            class="bg-white rounded-xl shadow-sm overflow-hidden">

            <div class="divide-y divide-gray-100">
                <div class="flex flex-col sm:flex-row">
                    <label class="bg-gray-50 px-5 py-3 text-sm font-medium text-gray-600 w-full sm:w-40 shrink-0 flex items-center gap-1">
                        お名前 <span class="text-red-400 text-xs">必須</span>
                    </label>
                    <div class="px-5 py-3 flex-1">
                        <input x-ref="name" type="text" name="name"
                               value="{{ old('name') }}" required maxlength="50"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-yellow-400">
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row">
                    <label class="bg-gray-50 px-5 py-3 text-sm font-medium text-gray-600 w-full sm:w-40 shrink-0 flex items-center gap-1">
                        メールアドレス <span class="text-red-400 text-xs">必須</span>
                    </label>
                    <div class="px-5 py-3 flex-1">
                        <input x-ref="email" type="email" name="email"
                               value="{{ old('email') }}" required maxlength="200"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-yellow-400">
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row">
                    <label class="bg-gray-50 px-5 py-3 text-sm font-medium text-gray-600 w-full sm:w-40 shrink-0 flex items-center gap-1">
                        カテゴリ <span class="text-red-400 text-xs">必須</span>
                    </label>
                    <div class="px-5 py-3 flex-1">
                        <select x-ref="category" name="category" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-yellow-400">
                            <option value="">選択してください</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row">
                    <label class="bg-gray-50 px-5 py-3 text-sm font-medium text-gray-600 w-full sm:w-40 shrink-0 flex items-center gap-1">
                        お問い合わせ内容 <span class="text-red-400 text-xs">必須</span>
                    </label>
                    <div class="px-5 py-3 flex-1">
                        <textarea x-ref="body" name="body" required rows="6" maxlength="3000"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-yellow-400 resize-none">{{ old('body') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button type="submit"
                        class="bg-gray-800 hover:bg-gray-700 text-white text-sm font-bold px-8 py-2.5 rounded-lg transition">
                    確認する →
                </button>
            </div>
        </form>

        {{-- 確認画面 --}}
        <form action="{{ route('inquiry.send') }}" method="POST"
              x-show="step === 'confirm'" x-cloak
              class="bg-white rounded-xl shadow-sm overflow-hidden">
            @csrf

            <div class="px-5 py-3 bg-yellow-50 border-b border-yellow-100">
                <p class="text-sm font-bold text-yellow-800">以下の内容で送信します。ご確認ください。</p>
            </div>

            <div class="divide-y divide-gray-100">
                <div class="flex flex-col sm:flex-row">
                    <span class="bg-gray-50 px-5 py-3 text-sm text-gray-500 w-full sm:w-40 shrink-0">お名前</span>
                    <span class="px-5 py-3 text-sm text-gray-800 flex-1" x-text="form.name"></span>
                    <input type="hidden" name="name" :value="form.name">
                </div>
                <div class="flex flex-col sm:flex-row">
                    <span class="bg-gray-50 px-5 py-3 text-sm text-gray-500 w-full sm:w-40 shrink-0">メールアドレス</span>
                    <span class="px-5 py-3 text-sm text-gray-800 flex-1" x-text="form.email"></span>
                    <input type="hidden" name="email" :value="form.email">
                </div>
                <div class="flex flex-col sm:flex-row">
                    <span class="bg-gray-50 px-5 py-3 text-sm text-gray-500 w-full sm:w-40 shrink-0">カテゴリ</span>
                    <span class="px-5 py-3 text-sm text-gray-800 flex-1" x-text="form.category"></span>
                    <input type="hidden" name="category" :value="form.category">
                </div>
                <div class="flex flex-col sm:flex-row">
                    <span class="bg-gray-50 px-5 py-3 text-sm text-gray-500 w-full sm:w-40 shrink-0">お問い合わせ内容</span>
                    <span class="px-5 py-3 text-sm text-gray-800 flex-1 whitespace-pre-wrap" x-text="form.body"></span>
                    <input type="hidden" name="body" :value="form.body">
                </div>
            </div>

            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <button type="button" @click="step = 'input'"
                        class="text-sm text-gray-400 hover:text-gray-600 transition">
                    ← 修正する
                </button>
                <button type="submit"
                        class="bg-gray-800 hover:bg-gray-700 text-white text-sm font-bold px-8 py-2.5 rounded-lg transition">
                    送信する
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
