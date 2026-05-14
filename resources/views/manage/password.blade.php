@extends('layouts.app')
@section('title', 'パスワード変更')
@section('content')
<div class="bg-business-700 text-white py-4">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <h1 class="font-bold text-lg">店舗管理</h1>
        <div class="flex items-center gap-4 text-sm">
            <span class="opacity-70">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}/" method="POST">
                @csrf
                <button type="submit" class="opacity-70 hover:opacity-100 transition">ログアウト</button>
            </form>
        </div>
    </div>
</div>

@include('manage._nav')

<div class="max-w-lg mx-auto py-8 px-4">


    <h2 class="text-lg font-bold text-gray-700 mb-3">パスワード変更</h2>

    @if($errors->has('current_password') || $errors->has('password'))
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->only(['current_password', 'password']) as $msgs)
                    @foreach((array)$msgs as $e)<li>{{ $e }}</li>@endforeach
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('manage.password.update') }}/" method="POST"
          class="bg-white rounded-xl shadow-sm overflow-hidden">
        @csrf
        @method('PUT')
        <table class="w-full text-sm">
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 w-40 whitespace-nowrap">
                    現在のパスワード <span class="text-red-400">*</span>
                </th>
                <td class="px-4 py-3">
                    <input type="password" name="current_password" required autocomplete="current-password"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-yellow-400 @error('current_password') border-red-400 @enderror">
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">
                    新しいパスワード <span class="text-red-400">*</span>
                </th>
                <td class="px-4 py-3">
                    <input type="password" name="password" required autocomplete="new-password"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-yellow-400 @error('password') border-red-400 @enderror">
                    <p class="text-xs text-gray-400 mt-1">8文字以上</p>
                </td>
            </tr>
            <tr class="border-b border-gray-100">
                <th class="bg-gray-50 text-gray-500 font-normal text-left px-4 py-3 whitespace-nowrap">
                    新しいパスワード（確認）<span class="text-red-400">*</span>
                </th>
                <td class="px-4 py-3">
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-yellow-400">
                </td>
            </tr>
        </table>
        <div class="px-4 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button type="submit"
                    class="bg-gray-800 hover:bg-gray-700 text-white text-sm font-bold px-6 py-2 rounded-lg transition">
                変更する
            </button>
        </div>
    </form>
</div>

@endsection
