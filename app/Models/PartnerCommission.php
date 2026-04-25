<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerCommission extends Model
{
    protected $fillable = [
        'partner_id', 'shop_id', 'base_amount', 'rate',
        'commission_amount', 'description', 'status',
        'period_start', 'period_end', 'paid_at',
    ];

    protected $casts = [
        'rate'         => 'decimal:4',
        'period_start' => 'date',
        'period_end'   => 'date',
        'paid_at'      => 'datetime',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
