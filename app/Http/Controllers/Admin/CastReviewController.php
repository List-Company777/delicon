<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CastReview;
use App\Models\LoginLog;
use Illuminate\Support\Facades\DB;

class CastReviewController extends Controller
{
    public function index()
    {
        $reviews = CastReview::with(['cast.shop', 'user'])
            ->orderByRaw("FIELD(is_approved, 0, 1)")
            ->orderByDesc('id')
            ->paginate(30);

        // 口コミIPと一致するログインIPを持つユーザーをまとめて取得
        $reviewIps = $reviews->pluck('ip_address')->filter()->unique()->values()->all();
        $ipMatches = [];
        if ($reviewIps) {
            $rows = LoginLog::whereIn('ip_address', $reviewIps)
                ->with('user:id,name,email,role')
                ->select('ip_address', 'user_id', 'created_at')
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('ip_address');
            foreach ($rows as $ip => $logs) {
                $ipMatches[$ip] = $logs->unique('user_id')->map(fn($l) => $l->user)->filter()->values();
            }
        }

        return view('admin.cast-reviews.index', compact('reviews', 'ipMatches'));
    }

    public function approve(CastReview $review)
    {
        $review->update(['is_approved' => true]);
        return back()->with('success', '口コミを承認しました');
    }

    public function reject(CastReview $review)
    {
        $review->delete();
        return back()->with('success', '口コミを削除しました');
    }
}
