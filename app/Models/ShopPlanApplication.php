<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopPlanApplication extends Model
{
    protected $fillable = [
        'shop_id', 'partner_id', 'amount', 'bid_price_requested', 'status', 'note', 'approved_at', 'plan_name',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
