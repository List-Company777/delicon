<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XmlFeed extends Model
{
    protected $fillable = [
        'name', 'slug', 'url', 'feed_type', 'is_own_site',
        'allowed_categories', 'category_genre_map',
        'bid_price_xml_field', 'monthly_budget_xml_field',
        'status', 'last_imported_at', 'budget_balance',
    ];

    protected $casts = [
        'is_own_site'          => 'boolean',
        'allowed_categories'   => 'array',
        'category_genre_map'   => 'array',
        'last_imported_at'     => 'datetime',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** null=無制限、残高>0=課金可、残高0=無料扱い（表示は継続） */
    public function consumeBudget(int $amount): void
    {
        if ($this->budget_balance === null) return;
        if ($this->budget_balance < $amount) return; // 1クリック分未満は無料扱い
        $this->decrement('budget_balance', $amount);
    }

    public function hasBudget(): bool
    {
        return $this->budget_balance === null || $this->budget_balance > 0;
    }

    public function feedTypeLabel(): string
    {
        return match ($this->feed_type) {
            'staff_jobs'    => 'スタッフ求人',
            'cast_jobs'     => 'キャスト求人',
            'business_info' => '営業情報',
            default         => $this->feed_type,
        };
    }
}
