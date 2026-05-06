<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ShopNews extends Model
{
    protected $fillable = ['shop_id', 'body', 'is_pinned', 'old_id'];
    protected $casts = ['is_pinned' => 'boolean'];
    public function shop(): BelongsTo { return $this->belongsTo(Shop::class); }
}
