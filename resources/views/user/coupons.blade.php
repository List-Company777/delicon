@extends('layouts.app')
@section('title', 'クーポン一覧')
@section('robots', 'noindex')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-[#F0ECE4] mb-6 flex items-center gap-3">
        <span class="w-1 h-7 bg-deli-500 rounded-full inline-block"></span>
        マイページ
    </h1>

    <div class="flex gap-4 mb-8 text-sm border-b border-surface-300">
        <a href="{{ route('user.dashboard') }}/" class="text-[#6A6A7E] hover:text-[#C8C4BC] pb-2 transition">お気に入り / 閲覧履歴</a>
        <a href="{{ route('user.settings') }}/?tab=notify" class="text-[#6A6A7E] hover:text-[#C8C4BC] pb-2 transition">新人通知</a>
        <a href="{{ route('user.settings') }}/?tab=prefs" class="text-[#6A6A7E] hover:text-[#C8C4BC] pb-2 transition">好み</a>
        <a href="{{ route('user.coupons') }}/" class="text-deli-400 border-b-2 border-deli-500 pb-2">クーポン</a>
    </div>

    <div class="bg-amber-900/20 border border-amber-700/40 rounded-xl px-4 py-3 mb-6 text-sm text-amber-300">
        ⚠ クーポンを利用する際は、<strong>サイトに登録したお名前を店舗スタッフにお伝えください。</strong>お名前の確認が取れない場合、クーポンを適用できないことがあります。
    </div>

    @if($coupons->isEmpty())
    <div class="bg-surface-600 border border-surface-400 rounded-xl p-10 text-center text-[#6A6A7E]">
        <p class="text-3xl mb-3">🎟</p>
        <p>まだクーポンはありません。</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($coupons as $coupon)
        @php
            $expired = $coupon->expires_at->isPast();
            $used    = $coupon->used_at !== null;
        @endphp
        <div @class([
            'border rounded-xl p-5',
            'bg-surface-600 border-surface-400'   => !$expired && !$used,
            'bg-surface-700 border-surface-500 opacity-60' => $expired || $used,
        ])>
            <div class="flex items-start justify-between gap-3 flex-wrap">
                <div>
                    <p class="text-xs text-[#6A6A7E] mb-1">{{ $coupon->shop?->name }}</p>
                    <p class="text-2xl font-bold text-deli-400">¥{{ number_format($coupon->discount_amount) }} 割引</p>
                    @if($coupon->message)
                    <p class="text-sm text-[#C8C4BC] mt-1">{{ $coupon->message }}</p>
                    @endif
                    @if($coupon->min_order_amount)
                    <p class="text-xs text-[#6A6A7E] mt-1">¥{{ number_format($coupon->min_order_amount) }} 以上のご利用で適用</p>
                    @endif
                </div>
                <div class="text-right shrink-0">
                    @if($used)
                        <span class="inline-block text-xs bg-surface-500 text-[#4A4A5E] px-3 py-1 rounded-full">使用済み</span>
                    @elseif($expired)
                        <span class="inline-block text-xs bg-surface-500 text-[#4A4A5E] px-3 py-1 rounded-full">期限切れ</span>
                    @else
                        <span class="inline-block text-xs bg-deli-900/40 text-deli-400 border border-deli-700/40 px-3 py-1 rounded-full font-medium">利用可能</span>
                    @endif
                </div>
            </div>
            <div class="mt-4 bg-surface-800 rounded-lg px-4 py-3 flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs text-[#4A4A5E] mb-1">クーポンコード</p>
                    <p class="font-mono text-lg font-bold tracking-widest text-[#E8E4DC]">{{ $coupon->code }}</p>
                </div>
                <div class="text-right text-xs text-[#6A6A7E]">
                    <p>有効期限</p>
                    <p class="font-medium {{ $expired ? 'text-red-400' : 'text-[#C8C4BC]' }}">{{ $coupon->expires_at->format('Y/m/d') }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
