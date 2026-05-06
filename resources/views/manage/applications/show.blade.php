@extends('layouts.app')

@section('title', $application->applicant_name . ' さんの応募')

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

<div class="max-w-3xl mx-auto px-4 py-8">

    <a href="{{ route('manage.applications.index') }}/"
       class="text-sm text-business-700 hover:underline mb-6 inline-block">← 応募一覧に戻る</a>

    @if(session('sent'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-4 text-sm">
            メッセージを送信しました。応募者にメールで通知しました。
        </div>
    @endif
    @if(session('status_updated') === 'rejected')
        <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg px-4 py-3 mb-4 text-sm">
            不採用に更新しました。応募者にお断りメールを送信しました。
        </div>
    @elseif(session('status_updated'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-4 text-sm">
            ステータスを更新しました。
        </div>
    @endif

    {{-- 応募者情報 + 不採用ダイアログ（同一Alpine.jsスコープ） --}}
    <div x-data="{ confirming: false }" @keydown.escape.window="confirming = false">

        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-gray-800 mb-1">{{ $application->applicant_name }}</h2>
                    @if($application->applicant_age)
                        <p class="text-xs text-gray-500">{{ $application->applicant_age }}歳</p>
                    @endif
                    <p class="text-xs text-gray-500 mt-0.5">{{ $application->applicant_email }}</p>
                    @if($application->applicant_tel)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $application->applicant_tel }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-2">
                        応募求人：{{ $application->job->title ?? '（求人削除済み）' }}
                    </p>
                    <p class="text-xs text-gray-400">応募日時：{{ $application->created_at->format('Y/m/d H:i') }}</p>
                </div>

                {{-- ステータス変更 --}}
                <div class="shrink-0">
                    <form x-ref="statusForm"
                          action="{{ route('manage.applications.status', $application->id) }}/" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" x-ref="statusInput" value="{{ $application->status }}">
                        <select
                            class="text-xs border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:border-business-500"
                            @change="
                                if ($event.target.value === 'rejected') {
                                    confirming = true;
                                    $event.target.value = '{{ $application->status }}';
                                } else {
                                    $refs.statusInput.value = $event.target.value;
                                    $refs.statusForm.submit();
                                }
                            ">
                            <option value="new"       @selected($application->status === 'new')>新規</option>
                            <option value="contacted" @selected($application->status === 'contacted')>連絡済み</option>
                            <option value="hired"     @selected($application->status === 'hired')>採用</option>
                            <option value="rejected"  @selected($application->status === 'rejected')>不採用</option>
                        </select>
                    </form>
                </div>
            </div>

            @if($application->message)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">応募時のメッセージ</p>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $application->message }}</p>
                </div>
            @endif
        </div>

        {{-- 不採用確認ダイアログ（カード外に配置してfixedが正しく機能するように） --}}
        <div x-show="confirming" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center"
             style="background-color: rgba(0,0,0,0.5);"
             @click.self="confirming = false">
            <div class="bg-white rounded-xl shadow-xl p-6 mx-8 max-w-xs w-full">
                <h3 class="font-bold text-gray-800 mb-2">不採用メールを送信します</h3>
                <p class="text-sm text-gray-600 mb-1">
                    <span class="font-medium">{{ $application->applicant_name }}</span> さんに
                </p>
                <p class="text-sm text-gray-600 mb-4">お断りのメールが自動で送信されます。よろしいですか？</p>
                <div class="flex gap-3">
                    <button type="button"
                        @click="$refs.statusInput.value = 'rejected'; $refs.statusForm.submit()"
                        style="background-color: #dc2626; color: #ffffff;"
                        class="flex-1 text-sm font-medium py-2 rounded-lg transition hover:opacity-90">
                        メールを送って不採用にする
                    </button>
                    <button type="button"
                        @click="confirming = false"
                        class="flex-1 border border-gray-300 text-gray-600 text-sm py-2 rounded-lg hover:bg-gray-50 transition">
                        キャンセル
                    </button>
                </div>
            </div>
        </div>

    </div>

    {{-- メッセージスレッド --}}
    <div class="space-y-3 mb-4">
        @forelse($application->messages as $msg)
            @if($msg->sender === 'shop')
                <div class="flex justify-end">
                    <div class="max-w-sm bg-business-700 text-white rounded-xl rounded-br-sm px-4 py-3">
                        <p class="text-sm whitespace-pre-line">{{ $msg->body }}</p>
                        <p class="text-xs opacity-60 mt-1 text-right">{{ $msg->created_at->format('Y/m/d H:i') }}</p>
                    </div>
                </div>
            @else
                <div class="flex justify-start">
                    <div class="max-w-sm bg-white rounded-xl rounded-bl-sm px-4 py-3 shadow-sm">
                        <p class="text-xs text-gray-400 mb-1">{{ $application->applicant_name }}</p>
                        <p class="text-sm text-gray-800 whitespace-pre-line">{{ $msg->body }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $msg->created_at->format('m/d H:i') }}</p>
                    </div>
                </div>
            @endif
        @empty
            <p class="text-center text-xs text-gray-400 py-4">まだメッセージはありません</p>
        @endforelse
    </div>

    {{-- 返信フォーム --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <form action="{{ route('manage.applications.message', $application->id) }}/" method="POST">
            @csrf
            <label class="text-xs text-gray-600 block mb-2 font-medium">メッセージを送る</label>
            @error('body')
                <p class="text-xs text-red-600 mb-1">{{ $message }}</p>
            @enderror
            <textarea name="body" rows="4" placeholder="メッセージを入力..."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-business-500 resize-none">{{ old('body') }}</textarea>
            <div class="flex justify-between items-center mt-2">
                <p class="text-xs text-gray-400">送信すると応募者にメールで通知されます</p>
                <button type="submit"
                        class="bg-business-700 hover:bg-business-600 text-white text-sm px-5 py-2 rounded-lg transition font-medium">
                    送信
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
