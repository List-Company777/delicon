<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ArticleTopic extends Model
{
    protected $fillable = ['title', 'gender', 'sort_order', 'status', 'source', 'ai_reason'];

    public function scopePending(Builder $q): Builder   { return $q->where('status', 'pending'); }
    public function scopeApproved(Builder $q): Builder  { return $q->where('status', 'approved'); }

    public function getGenderLabelAttribute(): string
    {
        return match ($this->gender) {
            'female'   => '女性',
            'male'     => '男性',
            'business' => '夜遊び',
            'shop'     => '店舗運営者',
            default    => '共通',
        };
    }

    public function getGenderColorAttribute(): string
    {
        return match ($this->gender) {
            'female'   => 'pink',
            'male'     => 'blue',
            'business' => 'purple',
            'shop'     => 'green',
            default    => 'gray',
        };
    }
}
