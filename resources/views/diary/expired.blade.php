@extends('layouts.app')
@section('title', 'URLの有効期限が切れています')
@section('robots', 'noindex')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm text-center">
        <div class="w-16 h-16 rounded-full bg-surface-500 border border-surface-300 flex items-center justify-center mx-auto mb-5">
            <svg class="w-8 h-8 text-[#6A6A7E]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
        </div>

        <h1 class="text-[#E8E4DC] font-bold text-xl mb-3">URLの有効期限が切れています</h1>
        <p class="text-[#6A6A7E] text-sm leading-relaxed mb-8">
            このURLは有効期限が切れています。<br>
            お店の方にURLの再発行をお願いしてください。
        </p>

        <a href="{{ url('/') }}"
           class="inline-block text-sm text-[#6A6A7E] hover:text-[#C8C4BC] transition underline underline-offset-2">
            トップページへ戻る
        </a>
    </div>
</div>
@endsection
