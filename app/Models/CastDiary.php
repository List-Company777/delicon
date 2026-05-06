<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CastDiary extends Model
{
    protected $fillable = ['cast_id', 'title', 'body', 'status'];

    public function cast() { return $this->belongsTo(Cast::class); }
    public function images() { return $this->hasMany(CastDiaryImage::class, 'diary_id')->orderBy('sort_order'); }
}
