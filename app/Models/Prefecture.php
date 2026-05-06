<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prefecture extends Model
{
    protected $fillable = ["prefecture", "slug", "parent_slug", "block", "ticker"];
    public $timestamps = false;

    // DB column は 'prefecture'、他クラスとの統一のため name アクセサを提供
    public function getNameAttribute(): string
    {
        return $this->prefecture ?? '';
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }
}
