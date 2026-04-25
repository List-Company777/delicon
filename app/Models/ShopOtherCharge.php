<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopOtherCharge extends Model
{
    protected $fillable = ['shop_id', 'label', 'price', 'sort_order'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
