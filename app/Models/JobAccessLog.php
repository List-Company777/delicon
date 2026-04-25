<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobAccessLog extends Model
{
    public $timestamps = false; // created_at のみ、updated_at なし

    protected $fillable = [
        'job_id', 'type', 'ip', 'user_agent', 'referrer', 'is_fraud',
    ];

    protected $casts = [
        'is_fraud'   => 'boolean',
        'created_at' => 'datetime',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}
