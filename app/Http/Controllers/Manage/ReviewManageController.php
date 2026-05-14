<?php
namespace App\Http\Controllers\Manage;

use App\Mail\DiscountCouponMail;
use App\Mail\ReviewRepliedMail;
use App\Mail\ShopContactMail;
use App\Models\Cast;
use App\Models\CastReview;
use App\Models\DiscountCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReviewManageController extends BaseController
{
    public function index()
    {
        $shop    = $this->shopOrFail();
        $castIds = Cast::where('shop_id', $shop->id)->pluck('id');

        $reviews = CastReview::whereIn('cast_id', $castIds)
            ->with('cast:id,name')
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('manage.review.index', compact('shop', 'reviews'));
    }

    public function requestDeletion(int $reviewId)
    {
        $shop    = $this->shopOrFail();

        if (!$shop->isPaid()) {
            return back()->withErrors(['error' => '削除依頼は有料掲載店舗のみ利用できます。']);
        }

        $castIds = Cast::where('shop_id', $shop->id)->pluck('id');
        $review  = CastReview::whereIn('cast_id', $castIds)->findOrFail($reviewId);

        if ($review->deletion_requested_at) {
            return back()->with('success', 'すでに削除依頼済みです。');
        }

        $review->update(['deletion_requested_at' => now()]);

        $body = implode("\n", [
            '口コミ削除依頼',
            '',
            '口コミID: ' . $review->id,
            '対象女性: ' . ($review->cast?->name ?? '不明'),
            '投稿者: '   . ($review->nickname ?? '匿名'),
            '評価: ★'   . $review->rating,
            '内容: '     . mb_substr($review->body, 0, 200),
        ]);

        $user = auth()->user();
        try {
            Mail::to(config('mail.admin_address', 'nwl-support@nightwork-list.com'))
                ->send(new ShopContactMail(
                    shopName:       $shop->name,
                    senderName:     $user->name,
                    senderEmail:    $user->email,
                    category:       '口コミ削除依頼',
                    contactSubject: '口コミ削除依頼 #' . $review->id,
                    body:           $body,
                ));
        } catch (\Throwable $e) {
            \Log::warning('Review deletion request mail failed: ' . $e->getMessage());
        }

        return back()->with('success', '削除依頼を送信しました。内容を確認の上、ご連絡いたします。');
    }

    public function reply(Request $request, int $reviewId)
    {
        $shop    = $this->shopOrFail();
        $castIds = Cast::where('shop_id', $shop->id)->pluck('id');
        $review  = CastReview::whereIn('cast_id', $castIds)->findOrFail($reviewId);

        $data = $request->validate([
            'shop_reply' => ['required', 'string', 'max:1000'],
        ]);

        $review->update([
            'shop_reply'      => $data['shop_reply'],
            'shop_replied_at' => now(),
        ]);

        // 口コミ投稿者（会員）へ通知
        if ($review->user_id) {
            try {
                Mail::to($review->user->email)->queue(new ReviewRepliedMail($review->load('cast.shop', 'user')));
            } catch (\Throwable $e) {
                \Log::warning('ReviewRepliedMail failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', '返信を投稿しました。');
    }

    public function deleteReply(int $reviewId)
    {
        $shop    = $this->shopOrFail();
        $castIds = Cast::where('shop_id', $shop->id)->pluck('id');
        $review  = CastReview::whereIn('cast_id', $castIds)->findOrFail($reviewId);

        $review->update(['shop_reply' => null, 'shop_replied_at' => null]);

        return back()->with('success', '返信を削除しました。');
    }

    public function sendCoupon(Request $request, int $reviewId)
    {
        $shop    = $this->shopOrFail();
        $castIds = Cast::where('shop_id', $shop->id)->pluck('id');
        $review  = CastReview::whereIn('cast_id', $castIds)->findOrFail($reviewId);

        if (!$review->user_id) {
            return back()->withErrors(['error' => 'ゲスト投稿のためクーポンを送付できません。']);
        }

        if ($review->coupon_sent) {
            return back()->with('success', 'このレビューへのクーポンはすでに送付済みです。');
        }

        $data = $request->validate([
            'discount_amount'  => ['required', 'integer', 'min:500', 'max:100000', 'multiple_of:500'],
            'min_order_amount' => ['nullable', 'integer', 'min:0'],
            'conditions'       => ['nullable', 'string', 'max:200'],
            'message'          => ['nullable', 'string', 'max:500'],
            'expires_days'     => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $coupon = DiscountCoupon::create([
            'shop_id'          => $shop->id,
            'user_id'          => $review->user_id,
            'review_id'        => $review->id,
            'code'             => DiscountCoupon::generateCode(),
            'discount_amount'  => $data['discount_amount'],
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'conditions'       => $data['conditions'] ?? null,
            'message'          => $data['message'] ?? null,
            'expires_at'       => now()->addDays((int) $data['expires_days']),
            'sent_at'          => now(),
        ]);

        $review->update(['coupon_sent' => true]);

        $user = auth()->user();

        try {
            Mail::to($review->user->email)
                ->send(new DiscountCouponMail($coupon));
        } catch (\Throwable $e) {
            \Log::warning('Coupon mail failed: ' . $e->getMessage());
        }

        // 店舗オーナーへの送付確認メール
        $confirmBody = implode("
", [
            'クーポンを送付しました。',
            '',
            '対象者: ' . ($review->user?->name ?? $review->nickname ?? '匿名'),
            '割引金額: ¥' . number_format($coupon->discount_amount),
            'クーポンコード: ' . $coupon->code,
            '有効期限: ' . $coupon->expires_at->format('Y年m月d日'),
            ($coupon->message ? 'メッセージ: ' . $coupon->message : ''),
        ]);
        try {
            Mail::to($user->email)
                ->send(new ShopContactMail(
                    shopName:       $shop->name,
                    senderName:     $user->name,
                    senderEmail:    $user->email,
                    category:       'クーポン送付確認',
                    contactSubject: '【送付完了】クーポン ' . $coupon->code,
                    body:           $confirmBody,
                ));
        } catch (\Throwable $e) {
            \Log::warning('Coupon confirm mail failed: ' . $e->getMessage());
        }

        return back()->with('success', 'クーポンを送付しました。');
    }
}
