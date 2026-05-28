<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CastDiaryToken extends Model
{
    public $timestamps = false;
    const UPDATED_AT = null;
    const CREATED_AT = 'created_at';

    protected $fillable = ['cast_id', 'token', 'is_email_token', 'expires_at'];
    protected $casts    = [
        'expires_at'     => 'datetime',
        'created_at'     => 'datetime',
        'is_email_token' => 'boolean',
    ];

    public function cast(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Cast::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public static function generateFor(int $castId): self
    {
        self::where('cast_id', $castId)->where('is_email_token', 0)->delete();

        return self::create([
            'cast_id'        => $castId,
            'token'          => bin2hex(random_bytes(32)),
            'is_email_token' => false,
            'expires_at'     => now()->addDays(180),
            'created_at'     => now(),
        ]);
    }

    public static function generateEmailTokenFor(int $castId): self
    {
        return self::firstOrCreate(
            ['cast_id' => $castId, 'is_email_token' => true],
            [
                'token'      => bin2hex(random_bytes(32)),
                'expires_at' => null,
                'created_at' => now(),
            ]
        );
    }
}
