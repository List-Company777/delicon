<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopExtensionPrice extends Model
{
    protected $fillable = ['plan_id', 'label', 'price', 'sort_order'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ShopPricePlan::class, 'plan_id');
    }
}
