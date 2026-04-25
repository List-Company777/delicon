<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopAccessLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'shop_id', 'ip', 'user_agent', 'referrer', 'is_fraud',
    ];

    protected $casts = [
        'is_fraud'   => 'boolean',
        'created_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
