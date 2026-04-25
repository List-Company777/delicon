<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopPricePlan extends Model
{
    protected $fillable = ['shop_id', 'name', 'sort_order'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function setPrices(): HasMany
    {
        return $this->hasMany(ShopSetPrice::class, 'plan_id')->orderBy('sort_order');
    }

    public function extensionPrices(): HasMany
    {
        return $this->hasMany(ShopExtensionPrice::class, 'plan_id')->orderBy('sort_order');
    }
}
