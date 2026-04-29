<?php

namespace App\Http\Controllers\Manage;

use App\Services\ImageService;
use Illuminate\Http\Request;

class ShopInfoController extends BaseController
{
    public function edit()
    {
        $shop = $this->shopOrFail();
        return view('manage.shop.edit', compact('shop'));
    }

    public function update(Request $request)
    {
        $shop = $this->shopOrFail();

        $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'kana'                  => ['nullable', 'string', 'max:100'],
            'address_locality'      => ['nullable', 'string', 'max:100'],
            'address'               => ['required', 'string', 'max:200'],
            'tel'                   => ['required', 'string', 'max:20'],
            'nearest_line'          => ['nullable', 'string', 'max:100'],
            'nearest_station_name'  => ['nullable', 'string', 'max:100'],
            'nearest_station_walk'  => ['nullable', 'integer', 'min:1', 'max:99'],
            'line_id'               => ['nullable', 'string', 'max:50'],
        ]);

        // 業種・都道府県・エリアは管理者のみ変更可のため除外
        $shop->update($request->only([
            'name', 'kana', 'address_locality', 'address', 'tel',
            'nearest_line', 'nearest_station_name', 'nearest_station_walk',
            'line_id',
        ]));

        return back()->with('success', '店舗基本情報を更新しました');
    }

    public function editImage()
    {
        $shop = $this->shopOrFail();
        return view('manage.shop.image', compact('shop'));
    }

    public function storeImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ], [
            'image.mimes' => 'JPEG・PNG・WebPファイルのみ入稿できます',
            'image.max'   => 'ファイルサイズは5MB以下にしてください',
        ]);

        $shop = $this->shopOrFail();
        $path = app(ImageService::class)->saveShopMainImage($request->file('image'), $shop->id);
        $shop->update(['main_image' => $path]);

        return back()->with('success', '画像をアップロードしました');
    }

    public function destroyImage()
    {
        $shop = $this->shopOrFail();
        app(ImageService::class)->deleteShopMainImage($shop->id);
        $shop->update(['main_image' => null]);

        return back()->with('success', '画像を削除しました');
    }
}
