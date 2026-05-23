<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CastDiary extends Model
{
    protected $fillable = ['cast_id', 'title', 'body', 'status', 'reviewed_at'];
    protected $casts = ['reviewed_at' => 'datetime'];

    public function cast() { return $this->belongsTo(Cast::class); }
    public function images() { return $this->hasMany(CastDiaryImage::class, 'diary_id')->orderBy('sort_order'); }
    public function likes() { return $this->hasMany(\App\Models\DiaryLike::class, 'diary_id'); }
}
