<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopSetPrice extends Model
{
    protected $fillable = ['shop_id', 'plan_id', 'time_from', 'time_to', 'price', 'sort_order'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ShopPricePlan::class, 'plan_id');
    }

    public function getTimeLabelAttribute(): string
    {
        if ($this->time_from && $this->time_to) {
            return "{$this->time_from}〜{$this->time_to}";
        }
        return '';
    }
}
