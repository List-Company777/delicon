<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopExternalUrl extends Model
{
    protected $fillable = ['shop_id', 'url_type', 'url', 'sort_order'];

    public const TYPES = [
        'website'   => '公式サイト',
        'instagram' => 'Instagram',
        'tiktok'    => 'TikTok',
        'x'         => 'X（旧Twitter）',
        'line'      => 'LINE',
        'youtube'   => 'YouTube',
        'other'     => 'その他',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function getLabelAttribute(): string
    {
        return self::TYPES[$this->url_type] ?? 'リンク';
    }
}
