<?php

namespace App\Http\Controllers\Manage;

use App\Models\ShopNews;
use Illuminate\Http\Request;

class ShopNewsController extends BaseController
{
    public function index()
    {
        $shop = $this->shopOrFail();
        $news = ShopNews::where('shop_id', $shop->id)
            ->orderByDesc('is_pinned')
            ->latest()
            ->get();
        return view('manage.shop.news', compact('shop', 'news'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'body'      => ['required', 'string', 'max:1000'],
            'is_pinned' => ['boolean'],
        ]);

        $shop = $this->shopOrFail();

        // ピン固定は最大3件
        if ($request->boolean('is_pinned')) {
            $pinnedCount = ShopNews::where('shop_id', $shop->id)->where('is_pinned', true)->count();
            if ($pinnedCount >= 3) {
                return back()->withErrors(['is_pinned' => '必ず表示は最大3件までです'])->withInput();
            }
        }

        ShopNews::create([
            'shop_id'   => $shop->id,
            'body'      => $request->input('body'),
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        // 最新10件を超えた分を削除（ピン固定以外の古いものから）
        $this->pruneNews($shop->id);

        return back()->with('success', 'お知らせを追加しました');
    }

    public function togglePin(ShopNews $news)
    {
        $shop = $this->shopOrFail();
        abort_if($news->shop_id !== $shop->id, 403);

        if (!$news->is_pinned) {
            $pinnedCount = ShopNews::where('shop_id', $shop->id)->where('is_pinned', true)->count();
            if ($pinnedCount >= 3) {
                return back()->withErrors(['pin' => '必ず表示は最大3件までです']);
            }
        }

        $news->update(['is_pinned' => !$news->is_pinned]);
        return back()->with('success', $news->is_pinned ? 'ピン固定しました' : 'ピン固定を解除しました');
    }

    public function destroy(ShopNews $news)
    {
        $shop = $this->shopOrFail();
        abort_if($news->shop_id !== $shop->id, 403);
        $news->delete();
        return back()->with('success', 'お知らせを削除しました');
    }

    private function pruneNews(int $shopId): void
    {
        $ids = ShopNews::where('shop_id', $shopId)
            ->orderByDesc('is_pinned')
            ->latest()
            ->pluck('id');

        if ($ids->count() > 10) {
            ShopNews::whereIn('id', $ids->slice(10)->values())->delete();
        }
    }
}
