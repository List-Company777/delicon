<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CastDiaryImage extends Model
{
    public $timestamps = false;
    const UPDATED_AT = null;
    const CREATED_AT = 'created_at';

    protected $fillable = ['diary_id', 'img_path', 'sort_order'];
    protected $casts = ['created_at' => 'datetime'];

    public function diary() { return $this->belongsTo(CastDiary::class); }
}
