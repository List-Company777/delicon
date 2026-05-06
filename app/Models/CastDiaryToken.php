<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CastDiaryToken extends Model
{
    public $timestamps = false;
    const UPDATED_AT = null;
    const CREATED_AT = 'created_at';

    protected $fillable = ['cast_id', 'token', 'expires_at'];
    protected $casts    = ['expires_at' => 'datetime', 'created_at' => 'datetime'];

    public function cast(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Cast::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public static function generateFor(int $castId): self
    {
        // 既存トークンを削除して再発行
        self::where('cast_id', $castId)->delete();

        return self::create([
            'cast_id'    => $castId,
            'token'      => bin2hex(random_bytes(32)),
            'expires_at' => now()->addDays(90),
            'created_at' => now(),
        ]);
    }
}
