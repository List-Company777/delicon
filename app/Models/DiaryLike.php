<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaryLike extends Model
{
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    protected $fillable = ['diary_id', 'user_id'];

    public function diary() { return $this->belongsTo(CastDiary::class, 'diary_id'); }
    public function user()  { return $this->belongsTo(User::class); }
}
