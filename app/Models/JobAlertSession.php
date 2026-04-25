<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobAlertSession extends Model
{
    protected $fillable = [
        'line_user_id', 'step', 'gender', 'area_id', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
