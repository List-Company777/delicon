<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WebAlertToken extends Model
{
    protected $fillable = ['token', 'gender', 'area_id', 'job_type_id', 'daily_pay_ok', 'inexperienced_ok', 'arubaito', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public static function createFor(string $gender, ?int $areaId, ?int $jobTypeId, bool $dailyPayOk = false, bool $inexperiencedOk = false, bool $arubaito = false): self
    {
        return self::create([
            'token'           => Str::random(32),
            'gender'          => $gender,
            'area_id'         => $areaId,
            'job_type_id'     => $jobTypeId,
            'daily_pay_ok'    => $dailyPayOk,
            'inexperienced_ok'=> $inexperiencedOk,
            'arubaito'        => $arubaito,
            'expires_at'      => now()->addMinutes(30),
        ]);
    }
}
