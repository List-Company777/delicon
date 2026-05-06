<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CastDeletionRequest extends Model
{
    protected $fillable = ['cast_id', 'requester_name', 'requester_email', 'reason', 'status', 'processed_at'];

    protected $casts = ['processed_at' => 'datetime'];

    public function cast()
    {
        return $this->belongsTo(Cast::class);
    }
}
