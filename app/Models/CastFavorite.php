<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CastFavorite extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'cast_id'];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public function cast(): BelongsTo { return $this->belongsTo(Cast::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
