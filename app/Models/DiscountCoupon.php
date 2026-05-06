<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DiscountCoupon extends Model
{
    protected $fillable = ['shop_id','user_id','code','discount_amount','min_order_amount','conditions','message','expires_at','used_at','sent_at'];
    protected $casts = ['expires_at'=>'date','used_at'=>'datetime','sent_at'=>'datetime'];

    public function shop(): BelongsTo { return $this->belongsTo(Shop::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public static function generateCode(): string
    {
        do { $code = strtoupper(Str::random(12)); }
        while (static::where('code', $code)->exists());
        return $code;
    }

    public function getIsExpiredAttribute(): bool { return $this->expires_at->isPast(); }
    public function getIsUsedAttribute(): bool    { return $this->used_at !== null; }
}
