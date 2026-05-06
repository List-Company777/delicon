<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ShopNews extends Model
{
    protected $fillable = ['shop_id', 'body', 'old_id'];
    public function shop(): BelongsTo { return $this->belongsTo(Shop::class); }
}
