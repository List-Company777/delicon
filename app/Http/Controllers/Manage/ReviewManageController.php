<?php
namespace App\Http\Controllers\Manage;

use App\Mail\DiscountCouponMail;
use App\Models\DiscountCoupon;
use App\Models\ShopReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReviewManageController extends BaseController
{
    // 口コミ投稿者一覧
    public function index()
    {
        $shop = $this->shopOrFail();

        $reviewers = ShopReview::where('shop_id', $shop->id)
            ->with('user:id,name,email,created_at')
            ->selectRaw('user_id, COUNT(*) as review_count, MAX(created_at) as last_reviewed_at, SUM(rating) as total_rating')
            ->groupBy('user_id')
            ->orderByDesc('last_reviewed_at')
            ->get()
            ->map(fn($r) => (object)[
                'user'            => $r->user,
                'review_count'    => $r->review_count,
                'last_reviewed_at'=> $r->last_reviewed_at,
                'avg_rating'      => round($r->total_rating / $r->review_count, 1),
            ]);

        return view('manage.review.index', compact('shop', 'reviewers'));
    }

    // 特定ユーザーの口コミ一覧 + クーポン送付フォーム
    public function showUser(int $userId)
    {
        $shop    = $this->shopOrFail();
        $user    = User::findOrFail($userId);
        $reviews = ShopReview::where('shop_id', $shop->id)
            ->where('user_id', $userId)
            ->with('cast:id,name')
            ->orderByDesc('created_at')
            ->get();

        $sentCoupons = DiscountCoupon::where('shop_id', $shop->id)
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return view('manage.review.user', compact('shop', 'user', 'reviews', 'sentCoupons'));
    }

    // 口コミステータス変更（承認/否認）
    public function updateStatus(Request $request, int $reviewId)
    {
        $shop   = $this->shopOrFail();
        $review = ShopReview::where('shop_id', $shop->id)->findOrFail($reviewId);
        $review->update(['status' => $request->input('status')]);
        return back()->with('success', '口コミのステータスを更新しました');
    }

    // 割引クーポン送付
    public function sendCoupon(Request $request, int $userId)
    {
        $shop = $this->shopOrFail();
        $user = User::findOrFail($userId);

        $data = $request->validate([
            'discount_amount'    => ['required', 'integer', 'min:100', 'max:100000'],
            'min_order_amount'   => ['nullable', 'integer', 'min:0'],
            'conditions'         => ['nullable', 'string', 'max:500'],
            'message'            => ['nullable', 'string', 'max:1000'],
            'expires_at'         => ['required', 'date', 'after:today'],
        ]);

        $coupon = DiscountCoupon::create([
            'shop_id'          => $shop->id,
            'user_id'          => $userId,
            'code'             => DiscountCoupon::generateCode(),
            'discount_amount'  => $data['discount_amount'],
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'conditions'       => $data['conditions'] ?? null,
            'message'          => $data['message'] ?? null,
            'expires_at'       => $data['expires_at'],
            'sent_at'          => now(),
        ]);

        try {
            Mail::to($user->email)->send(new DiscountCouponMail($coupon));
        } catch (\Throwable $e) {
            \Log::warning('Coupon mail failed: ' . $e->getMessage());
        }

        return back()->with('success', "{$user->name} さんに割引クーポンを送付しました（コード: {$coupon->code}）");
    }
}
