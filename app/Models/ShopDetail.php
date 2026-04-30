<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopDetail extends Model
{
    protected $fillable = [
        'shop_id', 'content', 'faq', 'short_description', 'website_url', 'set_price', 'nomination_fee',
        'all_you_can_drink', 'tax_included', 'service_charge', 'has_karaoke', 'has_private_room',
        'discount_first_set', 'discount_custom',
        'opening_hours', 'closing_hours', 'opening_days', 'holiday',
        'image_paths', 'status', 'is_hotlink', 'hotlink_url', 'click_count',
    ];

    protected $casts = [
        'faq'                => 'array',
        'image_paths'        => 'array',
        'opening_days'       => 'array',
        'all_you_can_drink'  => 'boolean',
        'tax_included'       => 'boolean',
        'has_karaoke'        => 'boolean',
        'has_private_room'   => 'boolean',
        'discount_first_set' => 'boolean',
        'is_hotlink'         => 'boolean',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
