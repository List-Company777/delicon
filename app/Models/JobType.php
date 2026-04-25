<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobType extends Model
{
    protected $fillable = ['name', 'slug', 'target_gender', 'group_slug', 'keyword_filter', 'sort_order', 'role_type'];

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }
}
