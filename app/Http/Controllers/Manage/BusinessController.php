<?php

namespace App\Http\Controllers\Manage;

use App\Models\ShopDetail;
use App\Models\ShopExternalUrl;
use App\Models\ShopSetPrice;
use App\Models\ShopPricePlan;
use App\Models\ShopExtensionPrice;
use App\Models\ShopOtherCharge;
use Illuminate\Http\Request;

class BusinessController extends BaseController
{
    public function edit()
    {
        $shop         = $this->shopOrFail();
        $shop->load(['pricePlans.setPrices', 'pricePlans.extensionPrices', 'otherCharges']);
        $detail       = $shop->detail ?? new ShopDetail(['shop_id' => $shop->id, 'status' => 'inactive']);
        $setPrices    = $shop->setPrices;
        $pricePlans   = $shop->pricePlans;
        $otherCharges = $shop->otherCharges;
        $externalUrls = $shop->externalUrls;
        $urlTypes     = ShopExternalUrl::TYPES;
        return view('manage.business.edit', compact('shop', 'detail', 'setPrices', 'pricePlans', 'otherCharges', 'externalUrls', 'urlTypes'));
    }

    public function update(Request $request)
    {
        $shop = $this->shopOrFail();

        $request->validate([
            'content'                            => ['nullable', 'string', 'max:2000'],
            'short_description'                  => ['nullable', 'string', 'max:30'],
            'nomination_fee'                     => ['nullable', 'string', 'max:100'],
            'all_you_can_drink'                  => ['boolean'],
            'tax_included'                       => ['nullable', 'in:0,1'],
            'service_charge'                     => ['nullable', 'string', 'max:50'],
            'has_karaoke'                        => ['boolean'],
            'has_private_room'                   => ['boolean'],
            'discount_first_set'                 => ['boolean'],
            'discount_custom'                    => ['nullable', 'string', 'max:200'],
            'is_hotlink'                         => ['boolean'],
            'hotlink_url'                        => ['nullable', 'url', 'max:500'],
            'opening_hours'                      => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'closing_hours'                      => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'opening_days'                       => ['nullable', 'array'],
            'opening_days.*'                     => ['in:Mo,Tu,We,Th,Fr,Sa,Su'],
            'holiday'                            => ['nullable', 'string', 'max:100'],
            'status'                             => ['required', 'in:active,inactive'],
            'plans'                              => ['nullable', 'array', 'max:3'],
            'plans.*.name'                       => ['nullable', 'string', 'max:100'],
            'plans.*.set_prices'                 => ['nullable', 'array', 'max:5'],
            'plans.*.set_prices.*.time_from'     => ['nullable', 'string', 'max:10'],
            'plans.*.set_prices.*.time_to'       => ['nullable', 'string', 'max:10'],
            'plans.*.set_prices.*.price'         => ['nullable', 'string', 'max:100'],
            'plans.*.extension_prices'           => ['nullable', 'array', 'max:3'],
            'plans.*.extension_prices.*.label'   => ['nullable', 'string', 'max:50'],
            'plans.*.extension_prices.*.price'   => ['nullable', 'string', 'max:50'],
            'other_charges'                      => ['nullable', 'array', 'max:5'],
            'other_charges.*.label'              => ['nullable', 'string', 'max:50'],
            'other_charges.*.price'              => ['nullable', 'string', 'max:50'],
            'external_urls'                      => ['nullable', 'array', 'max:6'],
            'external_urls.*.url_type'           => ['nullable', 'in:' . implode(',', array_keys(ShopExternalUrl::TYPES))],
            'external_urls.*.url'                => ['nullable', 'url', 'max:500'],
        ]);

        $taxRaw      = $request->input('tax_included');
        $taxIncluded = ($taxRaw === '' || $taxRaw === null) ? null : (bool) $taxRaw;

        $detail = ShopDetail::updateOrCreate(
            ['shop_id' => $shop->id],
            [
                'content'           => $request->content,
                'short_description' => $request->input('short_description'),
                'nomination_fee'    => $request->nomination_fee,
                'all_you_can_drink' => $request->boolean('all_you_can_drink'),
                'tax_included'      => $taxIncluded,
                'service_charge'    => $request->service_charge,
                'has_karaoke'        => $request->boolean('has_karaoke'),
                'has_private_room'   => $request->boolean('has_private_room'),
                'discount_first_set' => $request->boolean('discount_first_set'),
                'discount_custom'    => $request->input('discount_custom'),
                'is_hotlink'         => $shop->hasBudget() ? $request->boolean('is_hotlink') : ($detail->is_hotlink ?? false),
                'hotlink_url'        => $shop->hasBudget() ? ($request->boolean('is_hotlink') ? $request->input('hotlink_url') : null) : ($detail->hotlink_url ?? null),
                'opening_hours'     => $request->opening_hours,
                'closing_hours'     => $request->closing_hours,
                'opening_days'      => $request->input('opening_days', []),
                'holiday'           => $request->holiday,
                'status'            => $request->status,
            ]
        );

        // プラン・セット料金・延長料金：既存を全削除してから再作成
        // plan_id は nullable なので set_prices も先に削除する
        $shop->setPrices()->delete();
        $shop->pricePlans()->delete();

        foreach (array_values($request->input('plans', [])) as $i => $planData) {
            $hasContent = !empty(array_filter($planData['set_prices'] ?? [], fn($r) => $r['price'] ?? null))
                        || !empty(array_filter($planData['extension_prices'] ?? [], fn($r) => $r['price'] ?? null));
            if (!$hasContent && empty($planData['name'])) {
                continue;
            }

            $plan = $shop->pricePlans()->create([
                'name'       => $planData['name'] ?: null,
                'sort_order' => $i + 1,
            ]);

            foreach (array_values($planData['set_prices'] ?? []) as $j => $row) {
                if (empty($row['price'])) {
                    continue;
                }
                $plan->setPrices()->create([
                    'shop_id'    => $shop->id,
                    'time_from'  => $row['time_from'] ?: null,
                    'time_to'    => $row['time_to'] ?: null,
                    'price'      => $row['price'],
                    'sort_order' => $j + 1,
                ]);
            }

            foreach (array_values($planData['extension_prices'] ?? []) as $k => $row) {
                if (empty($row['price'])) {
                    continue;
                }
                $plan->extensionPrices()->create([
                    'label'      => $row['label'] ?: '',
                    'price'      => $row['price'],
                    'sort_order' => $k + 1,
                ]);
            }
        }

        // その他料金：全削除→再挿入
        $shop->otherCharges()->delete();
        foreach (array_values($request->input('other_charges', [])) as $m => $row) {
            if (empty($row['price'])) {
                continue;
            }
            $shop->otherCharges()->create([
                'label'      => $row['label'] ?: '',
                'price'      => $row['price'],
                'sort_order' => $m + 1,
            ]);
        }

        // 外部URL：全削除→再挿入
        $shop->externalUrls()->delete();
        foreach (array_values($request->input('external_urls', [])) as $i => $row) {
            if (empty($row['url'])) {
                continue;
            }
            ShopExternalUrl::create([
                'shop_id'    => $shop->id,
                'url_type'   => $row['url_type'],
                'url'        => $row['url'],
                'sort_order' => $i,
            ]);
        }

        return back()->with('success', '営業情報を更新しました');
    }
}
