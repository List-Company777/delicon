<?php

namespace App\Http\Controllers\Manage;

use App\Models\Genre;
use App\Services\ImageService;
use Illuminate\Http\Request;

class ShopInfoController extends BaseController
{
    public function edit()
    {
        $shop = $this->shopOrFail();
        $genres = Genre::orderBy('id')->get();
        return view('manage.shop.edit', compact('shop', 'genres'));
    }

    public function update(Request $request)
    {
        $shop = $this->shopOrFail();

        $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'kana'                  => ['nullable', 'string', 'max:100'],
            'postal_code'           => ['nullable', 'string', 'max:8', 'regex:/^\d{3}-?\d{4}$/'],
            'address_locality'      => ['nullable', 'string', 'max:100'],
            'address'               => ['required', 'string', 'max:200'],
            'tel'                   => ['required', 'string', 'max:20'],
            'nearest_line'          => ['nullable', 'string', 'max:100'],
            'nearest_station_name'  => ['nullable', 'string', 'max:100'],
            'nearest_station_walk'  => ['nullable', 'integer', 'min:1', 'max:99'],
            'line_id'               => ['nullable', 'string', 'max:50'],
            // delicon-specific
            'base'                  => ['nullable', 'string', 'max:100'],
            'catche'                => ['nullable', 'string', 'max:200'],
            'sangyo_text1'          => ['nullable', 'string', 'max:30'],
            'sangyo_text2'          => ['nullable', 'string', 'max:30'],
            'sangyo_text3'          => ['nullable', 'string', 'max:30'],
            'system_text'           => ['nullable', 'string', 'max:5000'],
            'coupon'                => ['nullable', 'string', 'max:2000'],
            'open_time'             => ['nullable', 'string', 'max:50'],
            'close_time'            => ['nullable', 'string', 'max:50'],
            'all_time'              => ['boolean'],
            'rest_day'              => ['nullable', 'string', 'max:100'],
            'price_60'              => ['nullable', 'integer', 'min:0', 'max:999999'],
            'price_90'              => ['nullable', 'integer', 'min:0', 'max:999999'],
            'price_120'             => ['nullable', 'integer', 'min:0', 'max:999999'],
            'price_high'            => ['nullable', 'integer', 'min:0', 'max:999999'],
            'eigyo_area'            => ['nullable', 'string', 'max:2000'],
            'eigyo_space'           => ['nullable', 'string', 'max:200'],
            'shop_type_id'          => ['nullable', 'integer', 'exists:shop_types,id'],
            'shop_type_id2'         => ['nullable', 'integer', 'exists:shop_types,id'],
            'tags'                  => ['nullable', 'array'],
            'tags.*'                => ['string', 'max:30'],
            'genre_id'              => ['nullable', 'integer', 'exists:genres,id'],
        ]);

        $shop->update($request->only([
            'name', 'kana', 'postal_code', 'address_locality', 'address', 'tel',
            'nearest_line', 'nearest_station_name', 'nearest_station_walk', 'line_id',
            'base', 'catche', 'sangyo_text1', 'sangyo_text2', 'sangyo_text3', 'system_text', 'coupon',
            'open_time', 'close_time', 'rest_day',
            'price_60', 'price_90', 'price_120', 'price_high',
            'eigyo_area', 'eigyo_space', 'shop_type_id', 'shop_type_id2', 'genre_id',
        ]) + [
            'all_time' => $request->boolean('all_time'),
            'tags'     => $request->input('tags', []),
        ]);

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
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120', 'dimensions:max_width=4000,max_height=4000'],
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
