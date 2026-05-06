<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CastImage extends Model
{
    protected $fillable = ['cast_id', 'img_path', 'sort_order', 'is_main'];
    protected $casts = ['is_main' => 'boolean'];
    public function cast(): BelongsTo { return $this->belongsTo(Cast::class); }
}
