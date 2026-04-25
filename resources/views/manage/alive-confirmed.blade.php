@extends('layouts.app')
@section('title', '掲載継続を確認しました')
@section('robots', 'noindex, follow')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="w-full max-w-md text-center">
        <div class="bg-white rounded-xl shadow-sm p-10">
            <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-800 mb-2">掲載継続を確認しました</h1>
            <p class="text-sm text-gray-500 mb-1">{{ $shop->name }}</p>
            <p class="text-sm text-gray-400 mt-4">
                引き続きナイトワークリストをご利用いただきありがとうございます。<br>
                掲載は継続されます。
            </p>
            <a href="{{ route('login') }}"
               class="mt-6 inline-block text-sm text-business-700 hover:underline">
                管理画面へログイン →
            </a>
        </div>
    </div>
</div>
@endsection
