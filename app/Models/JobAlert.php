<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobAlert extends Model
{
    protected $fillable = [
        'line_user_id', 'gender', 'area_id', 'job_type_id',
        'daily_pay_ok', 'inexperienced_ok', 'arubaito', 'is_active',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'daily_pay_ok'    => 'boolean',
        'inexperienced_ok'=> 'boolean',
        'arubaito'        => 'boolean',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class);
    }
}
