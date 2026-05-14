@extends('layouts.app')
@section('title', '口コミ管理')
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

<div class="max-w-4xl mx-auto px-4 pb-12">
    <h2 class="text-xl font-bold text-gray-800 mb-3">口コミ管理</h2>
    <p class="text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 mb-6">口コミの削除は、正当なサービスの評価であると思われる場合には、有料掲載でも基本的に受け付けておりません。誹謗中傷などについては削除いたしますので、ご申告ください。</p>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif

    @if($reviews->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
        <p class="text-4xl mb-3">📝</p>
        <p>まだ口コミ投稿はありません。</p>
    </div>
    @else
    <div class="space-y-4">
        @foreach($reviews as $review)
        <div class="bg-white rounded-xl shadow-sm p-4">
            {{-- ヘッダー行 --}}
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-2">
                        <span class="text-sm font-bold text-gray-800">{{ $review->cast?->name ?? '（削除済み）' }}</span>
                        <span class="text-amber-500 text-sm">{{ str_repeat('★', $review->rating) }}<span class="text-gray-200">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                        <span class="text-xs text-gray-400">{{ $review->nickname ?? '匿名' }}</span>
                        <span class="text-xs text-gray-400">{{ $review->created_at->format('Y/m/d') }}</span>
                        @if($review->coupon_sent)
                        <span class="text-xs bg-yellow-50 border border-yellow-200 text-yellow-700 px-2 py-0.5 rounded-full font-medium">🎟 クーポン送付済み</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $review->body }}</p>
                </div>
                {{-- 削除依頼ボタン --}}
                @if($shop->isPaid())
                <div class="shrink-0 flex flex-col gap-1.5 items-end">
                    @if($review->deletion_requested_at)
                    <span class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 text-gray-400 whitespace-nowrap">削除依頼済み</span>
                    @else
                    <form method="POST" action="{{ route('manage.review.delete-request', $review->id) }}/"
                          onsubmit="return confirm('この口コミの削除を依頼しますか？')">
                        @csrf
                        <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition whitespace-nowrap">削除依頼</button>
                    </form>
                    @endif
                </div>
                @endif
            </div>

            {{-- 店舗からの返信 --}}
            @if($review->shop_reply)
            <div class="mt-3 bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-700">
                <p class="text-xs text-gray-400 mb-1">店舗からの返信 <span class="ml-2">{{ $review->shop_replied_at?->format('Y/m/d') }}</span></p>
                <p class="leading-relaxed whitespace-pre-wrap">{{ $review->shop_reply }}</p>
                <form method="POST" action="{{ route('manage.review.reply.delete', $review->id) }}/" class="mt-2"
                      onsubmit="return confirm('返信を削除しますか？')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition">返信を削除</button>
                </form>
            </div>
            @endif

            {{-- アクション行（返信・クーポン）--}}
            <div class="mt-3 flex flex-wrap gap-2 border-t border-gray-100 pt-3"
                 x-data="{ showReply: false, showCoupon: false }">

                {{-- 返信フォームトグル --}}
                <button type="button"
                        @click="showReply = !showReply"
                        class="text-xs border border-gray-300 text-gray-600 hover:bg-gray-50 px-3 py-1.5 rounded-lg transition">
                    {{ $review->shop_reply ? '返信を編集' : '返信する' }}
                </button>

                {{-- クーポン送付（会員投稿のみ・有料店舗のみ） --}}
                @if($review->user_id && !$review->coupon_sent)
                <button type="button"
                        @click="showCoupon = !showCoupon"
                        class="text-xs border border-yellow-300 text-yellow-700 hover:bg-yellow-50 px-3 py-1.5 rounded-lg transition">
                    🎟 クーポン送付
                </button>
                @endif

                {{-- 返信フォーム --}}
                <div x-show="showReply" x-transition class="w-full mt-2">
                    <form method="POST" action="{{ route('manage.review.reply', $review->id) }}/">
                        @csrf
                        <textarea name="shop_reply" rows="3" maxlength="1000" required
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-800 focus:outline-none focus:border-business-400 resize-y"
                                  placeholder="返信内容を入力してください（最大1000文字）">{{ $review->shop_reply }}</textarea>
                        <div class="flex gap-2 mt-2">
                            <button type="submit" class="text-xs bg-business-700 hover:bg-business-800 text-white px-4 py-1.5 rounded-lg transition">返信を投稿</button>
                            <button type="button" @click="showReply = false" class="text-xs border border-gray-300 text-gray-500 hover:bg-gray-50 px-4 py-1.5 rounded-lg transition">キャンセル</button>
                        </div>
                    </form>
                </div>

                {{-- クーポン送付フォーム --}}
                @if($review->user_id && !$review->coupon_sent)
                <div x-show="showCoupon" x-transition class="w-full mt-2 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-xs text-yellow-700 mb-3 font-medium">クーポンをメールで送付します</p>
                    <form method="POST" action="{{ route('manage.review.coupon.send', $review->id) }}/">
                        @csrf
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="text-xs text-gray-600 block mb-1">割引金額 <span class="text-red-400">*</span></label>
                                <div class="flex items-center gap-1" x-data="{ amt: 1000 }">
                                    <button type="button" @click="amt = Math.max(500, amt - 500)"
                                            class="w-7 h-7 flex items-center justify-center border border-gray-300 rounded text-gray-600 hover:bg-gray-100 text-base leading-none">−</button>
                                    <input type="hidden" name="discount_amount" :value="amt">
                                    <span class="w-16 text-center text-sm font-medium text-gray-800" x-text="amt.toLocaleString()"></span>
                                    <button type="button" @click="amt = Math.min(100000, amt + 500)"
                                            class="w-7 h-7 flex items-center justify-center border border-gray-300 rounded text-gray-600 hover:bg-gray-100 text-base leading-none">＋</button>
                                    <span class="text-xs text-gray-500">円</span>
                                </div>
                            </div>
                            <div>
                                <label class="text-xs text-gray-600 block mb-1">有効期限</label>
                                <div class="flex items-center gap-1">
                                    <input type="number" name="expires_days" value="30" min="1" max="365" required
                                           class="w-16 border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:border-yellow-400">
                                    <span class="text-xs text-gray-500">日間</span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="text-xs text-gray-600 block mb-1">メッセージ（任意・合言葉などでもOK）</label>
                            <input type="text" name="message" maxlength="200"
                                   class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:border-yellow-400"
                                   placeholder="口コミありがとうございます！">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    onclick="return confirm('クーポンを送付しますか？（1回限りです）')"
                                    class="text-xs bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-1.5 rounded-lg transition">送付する</button>
                            <button type="button" @click="showCoupon = false" class="text-xs border border-gray-300 text-gray-500 hover:bg-gray-50 px-4 py-1.5 rounded-lg transition">キャンセル</button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $reviews->links() }}</div>
    @endif
</div>
@endsection
