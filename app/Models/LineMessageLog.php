<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LineMessageLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['type', 'line_user_id', 'sent_at'];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /** 当月の送信総数 */
    public static function monthlyCount(): int
    {
        return static::query()
            ->whereYear('sent_at', now()->year)
            ->whereMonth('sent_at', now()->month)
            ->count();
    }

    /** 残り送信可能数（求人アラート用に15通バッファを確保） */
    public static function remainingQuota(int $buffer = 15, int $limit = 200): int
    {
        return max(0, $limit - static::monthlyCount() - $buffer);
    }
}
