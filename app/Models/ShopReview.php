<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopReview extends Model
{
    protected $fillable = ['shop_id','user_id','cast_id','rating','title','body','status'];

    public function shop(): BelongsTo { return $this->belongsTo(Shop::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function cast(): BelongsTo { return $this->belongsTo(Cast::class); }
}
