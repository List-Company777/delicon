<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanSlotLimit extends Model
{
    protected $fillable = ['prefecture_id', 'max_slots'];
}
