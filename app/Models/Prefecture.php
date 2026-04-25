<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prefecture extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order'];

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }
}
