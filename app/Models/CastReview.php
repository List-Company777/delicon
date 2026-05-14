<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CastReview extends Model
{
    protected $fillable = [
        'cast_id', 'shop_id', 'user_id', 'nickname', 'rating', 'body',
        'is_approved', 'ip_address',
        'deletion_requested_at',
        'shop_reply', 'shop_replied_at',
        'coupon_sent',
    ];
    protected $casts = [
        'is_approved'           => 'boolean',
        'deletion_requested_at' => 'datetime',
        'shop_replied_at'       => 'datetime',
        'coupon_sent'           => 'boolean',
    ];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function cast(): BelongsTo { return $this->belongsTo(Cast::class); }
    public function shop(): BelongsTo { return $this->belongsTo(Shop::class); }
}
