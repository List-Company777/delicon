<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CastView extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id','session_id','cast_id','viewed_at'];
    protected $casts = ['viewed_at' => 'datetime'];

    public function cast(): BelongsTo { return $this->belongsTo(Cast::class); }
}
