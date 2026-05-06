@extends('layouts.app')
@section('title', 'パスワード変更')
@section('content')
@include('manage._nav')

<div class="max-w-lg mx-auto py-8 px-4">

    {{-- LINE連携 --}}
    <h2 class="text-lg font-bold text-gray-700 mb-3">LINE連携</h2>
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->has('line'))
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
            {{ $errors->first('line') }}
        </div>
    @endif
    <div class="bg-white rounded-xl shadow-sm px-4 py-4 mb-8 flex items-center justify-between">
        @if(auth()->user()->line_user_id)
            <div class="flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-green-400"></span>
                <span class="text-sm text-gray-700">LINE連携中</span>
            </div>
            <span class="text-xs text-gray-400">連携済み</span>
        @else
            <span class="text-sm text-gray-500">LINEアカウントが未連携です</span>
            <a href="{{ route('auth.line.connect') }}/"
               class="inline-flex items-center gap-2 bg-[#06C755] hover:bg-[#05b34c] text-white text-sm font-bold px-4 py-2 rounded-lg transition">
                <svg viewBox="0 0 24 24" class="w-4 h-4 fill-current"><path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63.349 0 .631.285.631.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.281.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/></svg>
                LINEを連携する
            </a>
        @endif
    </div>

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
