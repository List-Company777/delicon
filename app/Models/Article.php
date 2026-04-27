<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Article extends Model
{
    protected $fillable = [
        'slug', 'title', 'lead', 'body', 'hero_image',
        'gender', 'is_published', 'published_at', 'updated_at_manual',
    ];

    protected $casts = [
        'is_published'      => 'boolean',
        'published_at'      => 'datetime',
        'updated_at_manual' => 'date',
    ];

    public function video(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\ArticleVideo::class)->latestOfMany();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ArticleCategory::class, 'article_article_category');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ArticleTag::class, 'article_article_tag');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->where('published_at', '<=', now());
    }

    public function isVisible(): bool
    {
        return $this->is_published && $this->published_at?->lte(now());
    }
}
