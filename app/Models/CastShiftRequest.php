<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CastShiftRequest extends Model
{
    protected $fillable = ['cast_id', 'work_date', 'start_time', 'end_time', 'note', 'status', 'approved_at'];
    protected $casts = ['work_date' => 'date', 'approved_at' => 'datetime'];

    public function cast(): BelongsTo { return $this->belongsTo(Cast::class); }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
}
