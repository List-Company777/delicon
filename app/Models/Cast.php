<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cast extends Model
{
    protected $table = 'casts';

    protected $fillable = [
        'shop_id', 'name', 'age', 'tall', 'bust', 'cup', 'west', 'hip',
        'img_file_name', 'type_id', 'body_id',
        'comment', 'message', 'blood', 'country',
        'hatsutaiken', 'seikantai', 'tokuiwaza', 'sukinatype',
        'shumi', 'zenshoku', 'tabacco', 'seiza', 'likeeat', 'osake',
        'yuumeijin', 'shiofuki', 'zitaku',
        'twitter_account', 'official_url',
        'price_on', 'is_recommended', 'sort_order', 'ranking_count', 'status',
    ];

    protected $casts = [
        'is_recommended' => 'boolean',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function castType(): BelongsTo
    {
        return $this->belongsTo(CastType::class, 'type_id');
    }

    public function bodyType(): BelongsTo
    {
        return $this->belongsTo(CastBodyType::class, 'body_id');
    }

    public function charms(): BelongsToMany
    {
        return $this->belongsToMany(CastCharmType::class, 'cast_charms', 'cast_id', 'charm_type_id');
    }

    public function plays(): BelongsToMany
    {
        return $this->belongsToMany(CastPlayType::class, 'cast_plays', 'cast_id', 'play_type_id');
    }

    public function personalities(): BelongsToMany
    {
        return $this->belongsToMany(CastPersonalityType::class, 'cast_personalities', 'cast_id', 'personality_type_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(CastTagMaster::class, 'cast_tags', 'cast_id', 'tag_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(CastImage::class)->orderBy('sort_order');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(CastSchedule::class)->orderBy('work_date');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CastReview::class)->where('is_approved', true)->latest();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getImgUrlAttribute(): string
    {
        if ($this->img_file_name && !str_starts_with($this->img_file_name, '/img/common/')) {
            return $this->img_file_name . 'big.jpg';
        }
        return '/img/no-cast.jpg';
    }
}
