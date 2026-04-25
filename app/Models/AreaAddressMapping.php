<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AreaAddressMapping extends Model
{
    protected $fillable = ['keyword', 'example_address', 'area_id'];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
