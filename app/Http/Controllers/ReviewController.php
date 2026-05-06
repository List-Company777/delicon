<?php
namespace App\Http\Controllers;

use App\Models\Cast;
use App\Models\Shop;
use App\Models\ShopReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // 口コミ投稿フォーム
    public function create(Request $request)
    {
        $shop = Shop::where('status', 'active')->findOrFail($request->query('shop_id'));
        $casts = Cast::where('shop_id', $shop->id)->where('status', 'active')->orderBy('name')->get(['id','name']);
        return view('review.create', compact('shop', 'casts'));
    }

    // 口コミ保存
    public function store(Request $request)
    {
        $request->validate([
            'shop_id' => ['required', 'exists:shops,id'],
            'cast_id' => ['nullable', 'exists:casts,id'],
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'title'   => ['nullable', 'string', 'max:100'],
            'body'    => ['required', 'string', 'min:20', 'max:2000'],
        ], [
            'body.min' => '口コミ本文は20文字以上で入力してください。',
            'body.required' => '口コミ本文を入力してください。',
        ]);

        ShopReview::create([
            'shop_id' => $request->shop_id,
            'user_id' => Auth::id(),
            'cast_id' => $request->cast_id ?: null,
            'rating'  => $request->rating,
            'title'   => $request->title ?: null,
            'body'    => $request->body,
            'status'  => 'pending',
        ]);

        return redirect()->route('shop.show', $request->shop_id)
            ->with('success', '口コミを投稿しました。承認後に公開されます。');
    }
}
