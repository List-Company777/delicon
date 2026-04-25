<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ArticleCategory extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order'];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_article_category');
    }
}
