<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CastReview extends Model
{
    protected $fillable = ['cast_id', 'shop_id', 'nickname', 'rating', 'body', 'is_approved', 'ip_address'];
    protected $casts = ['is_approved' => 'boolean'];
    public function cast(): BelongsTo { return $this->belongsTo(Cast::class); }
    public function shop(): BelongsTo { return $this->belongsTo(Shop::class); }
}
