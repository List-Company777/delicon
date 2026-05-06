<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CastSchedule extends Model
{
    protected $fillable = ['cast_id', 'work_date', 'start_time', 'end_time', 'note'];
    protected $casts = ['work_date' => 'date'];
    public function cast(): BelongsTo { return $this->belongsTo(Cast::class); }
}
