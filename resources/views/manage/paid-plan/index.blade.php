@extends('layouts.app')

@section('title', '掲載プラン')

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

<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

    @if(session('plan_applied'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            有料プランの申し込みを送信しました。管理者が確認後、予算が追加されます。
        </div>
    @endif
    {{-- bid_price_updated flash: hidden --}}

    @if($shop->status !== 'active')
        <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
            <p class="text-sm">掲載審査が完了すると有料掲載の設定ができます。</p>
        </div>
    @else

    {{-- 予算・入札単価: hidden --}}

    {{-- クリック数: hidden (女性統計で代替) --}}

    {{-- 予算追加・申し込みフォーム: hidden --}}

    @endif {{-- active --}}

</div>
@endsection

