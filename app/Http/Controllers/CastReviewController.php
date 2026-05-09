<?php
namespace App\Http\Controllers;

use App\Models\Cast;
use App\Models\CastReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CastReviewController extends Controller
{
    public function store(Request $request, int $castId)
    {
        $cast = Cast::where('status', 'active')->findOrFail($castId);

        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body'   => ['required', 'string', 'min:20', 'max:2000'],
        ], [
            'body.min'      => '口コミ本文は20文字以上で入力してください。',
            'body.required' => '口コミ本文を入力してください。',
        ]);

        CastReview::create([
            'cast_id'    => $cast->id,
            'shop_id'    => $cast->shop_id,
            'user_id'    => Auth::id(),
            'nickname'   => Auth::user()->name,
            'rating'     => $request->rating,
            'body'       => $request->body,
            'is_approved'=> false,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('review_success', '口コミを投稿しました。承認後に公開されます。');
    }
}
