@extends('layouts.admin')
@section('title', $user->name . ' さんの口コミ')
@section('content')
@include('manage._nav')
<div class="max-w-4xl mx-auto px-4 pb-12">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('manage.review.index') }}/" class="text-sm text-gray-500 hover:underline">← 投稿者一覧</a>
        <span class="text-gray-300">/</span>
        <h1 class="text-xl font-bold text-gray-800">{{ $user->name }} さんの口コミ</h1>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded p-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    {{-- 口コミ一覧 --}}
    <div class="mb-8">
        <h2 class="text-base font-semibold text-gray-700 mb-3">投稿した口コミ（{{ $reviews->count() }}件）</h2>
        @if($reviews->isEmpty())
        <p class="text-sm text-gray-400">口コミがありません。</p>
        @else
        <div class="space-y-3">
            @foreach($reviews as $review)
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-amber-400 font-bold text-sm">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                            @if($review->cast)
                            <span class="text-xs text-gray-400">対象キャスト：{{ $review->cast->name }}</span>
                            @endif
                            <span class="text-xs text-gray-400">{{ $review->created_at->format('Y/m/d') }}</span>
                        </div>
                        @if($review->title)
                        <p class="font-semibold text-gray-800 text-sm mb-1">{{ $review->title }}</p>
                        @endif
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $review->body }}</p>
                    </div>
                    <div class="shrink-0 flex flex-col gap-1.5">
                        @if($review->status === 'pending')
                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded font-medium">審査中</span>
                        @elseif($review->status === 'approved')
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded font-medium">公開中</span>
                        @else
                        <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded font-medium">非承認</span>
                        @endif
                        <form action="{{ route('manage.review.status', $review->id) }}/" method="POST">
                            @csrf @method('PATCH')
                            @if($review->status !== 'approved')
                            <button type="submit" name="status" value="approved" class="text-xs text-green-600 hover:underline">承認</button>
                            @endif
                            @if($review->status !== 'rejected')
                            <button type="submit" name="status" value="rejected" class="text-xs text-red-500 hover:underline ml-1">非承認</button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- クーポン送付フォーム --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-700 mb-4">割引クーポンを送付する</h2>
        <form action="{{ route('manage.review.coupon.send', $user->id) }}/" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">割引金額 <span class="text-red-500">*</span></label>
                    <select name="discount_amount" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-red-400">
                        <option value="500">500円割引</option>
                        <option value="750">750円割引</option>
                        <option value="1000" selected>1,000円割引</option>
                        <option value="1500">1,500円割引</option>
                        <option value="2000">2,000円割引</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">最低利用金額（任意）</label>
                    <div class="flex items-center gap-1">
                        <input type="number" name="min_order_amount" min="0" step="1000" placeholder="例：10000"
                               class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-red-400">
                        <span class="text-sm text-gray-500 shrink-0">円以上</span>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">有効期限 <span class="text-red-500">*</span></label>
                <input type="date" name="expires_at" required
                       value="{{ now()->addDays(30)->format('Y-m-d') }}"
                       min="{{ now()->addDay()->format('Y-m-d') }}"
                       class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-red-400">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">適用条件（任意）</label>
                <input type="text" name="conditions" maxlength="500" placeholder="例：初回ご利用限定、平日限定など"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-red-400">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">メッセージ（任意）</label>
                <textarea name="message" rows="3" maxlength="1000" placeholder="口コミありがとうございます。次回ご利用の際にお使いください。"
                          class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-red-400 resize-none"></textarea>
            </div>
            @error('expires_at')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
            <div class="text-right">
                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold px-6 py-2 rounded-lg transition">
                    クーポンをメール送付する
                </button>
            </div>
        </form>
    </div>

    {{-- 送付済みクーポン履歴 --}}
    @if($sentCoupons->isNotEmpty())
    <div>
        <h2 class="text-base font-semibold text-gray-700 mb-3">送付済みクーポン（{{ $sentCoupons->count() }}件）</h2>
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-2 text-gray-600 font-medium">コード</th>
                        <th class="text-left px-4 py-2 text-gray-600 font-medium">割引額</th>
                        <th class="text-left px-4 py-2 text-gray-600 font-medium">有効期限</th>
                        <th class="text-left px-4 py-2 text-gray-600 font-medium">状態</th>
                        <th class="text-left px-4 py-2 text-gray-600 font-medium">送付日</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($sentCoupons as $coupon)
                    <tr>
                        <td class="px-4 py-2 font-mono font-bold text-gray-800">{{ $coupon->code }}</td>
                        <td class="px-4 py-2 text-red-600 font-bold">¥{{ number_format($coupon->discount_amount) }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $coupon->expires_at->format('Y/m/d') }}</td>
                        <td class="px-4 py-2">
                            @if($coupon->is_used)
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded">使用済み</span>
                            @elseif($coupon->is_expired)
                            <span class="text-xs bg-red-100 text-red-500 px-2 py-0.5 rounded">期限切れ</span>
                            @else
                            <span class="text-xs bg-green-100 text-green-600 px-2 py-0.5 rounded">有効</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-gray-400 text-xs">{{ $coupon->sent_at?->format('Y/m/d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
