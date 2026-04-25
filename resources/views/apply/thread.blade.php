@extends('layouts.app')

@section('title', $application->shop->name . ' とのメッセージ')
{{-- 非ログインページなのでnoindex --}}
@section('robots', 'noindex, follow')

@section('content')
<div class="bg-gray-900 text-white py-4">
    <div class="max-w-2xl mx-auto px-4">
        <h1 class="font-bold text-base">{{ $application->shop->name }}</h1>
        <p class="text-xs text-gray-400 mt-0.5">求人：{{ $application->job->title ?? '（求人削除済み）' }}</p>
    </div>
</div>

<div class="max-w-2xl mx-auto px-4 py-6">

    @if(session('sent'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-4 text-sm">
            返信を送信しました。
        </div>
    @endif

    <p class="text-xs text-gray-500 mb-5">
        {{ $application->applicant_name }} さんと {{ $application->shop->name }} のメッセージ履歴です。
        このページのURLは他の方に教えないようにしてください。
    </p>

    {{-- スレッド --}}
    <div class="space-y-3 mb-6">
        @forelse($application->messages as $msg)
            @if($msg->sender === 'shop')
                <div class="flex justify-start">
                    <div class="max-w-sm bg-white rounded-xl rounded-bl-sm px-4 py-3 shadow-sm">
                        <p class="text-xs text-gray-400 mb-1">{{ $application->shop->name }}</p>
                        <p class="text-sm text-gray-800 whitespace-pre-line">{{ $msg->body }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $msg->created_at->format('m/d H:i') }}</p>
                    </div>
                </div>
            @else
                <div class="flex justify-end">
                    <div class="max-w-sm bg-gray-800 text-white rounded-xl rounded-br-sm px-4 py-3">
                        <p class="text-sm whitespace-pre-line">{{ $msg->body }}</p>
                        <p class="text-xs opacity-60 mt-1 text-right">{{ $msg->created_at->format('m/d H:i') }}</p>
                    </div>
                </div>
            @endif
        @empty
            <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-400">
                <p class="text-sm">まだメッセージはありません。</p>
                <p class="text-xs mt-1">店舗からメッセージが届いたらメールでお知らせします。</p>
            </div>
        @endforelse
    </div>

    {{-- 返信フォーム --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <form action="{{ route('apply.thread.message', $token) }}" method="POST">
            @csrf
            <label class="text-xs text-gray-600 block mb-2 font-medium">返信する</label>
            @error('body')
                <p class="text-xs text-red-600 mb-1">{{ $message }}</p>
            @enderror
            <textarea name="body" rows="4" placeholder="メッセージを入力..."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-500 resize-none">{{ old('body') }}</textarea>
            <div class="flex justify-end mt-2">
                <button type="submit"
                        class="bg-gray-800 hover:bg-gray-700 text-white text-sm px-5 py-2 rounded-lg transition font-medium">
                    送信
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
