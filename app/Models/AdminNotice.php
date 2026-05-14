<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotice extends Model
{
    protected $fillable = ['title', 'body', 'target', 'filter_pref_id', 'filter_plan', 'status', 'sent_count', 'sent_at'];

    protected $casts = [
        'sent_at'        => 'datetime',
        'filter_pref_id' => 'integer',
        'filter_plan'    => 'integer',
    ];

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function targetLabel(): string
    {
        $parts = [];

        $parts[] = match($this->target) {
            'active'   => '掲載中店舗',
            'inactive' => '非公開店舗',
            default    => '全店舗',
        };

        if ($this->filter_pref_id) {
            $pref = Prefecture::find($this->filter_pref_id);
            if ($pref) $parts[] = $pref->prefecture;
        }

        $planLabels = [1 => 'VIP', 2 => 'ミドル', 3 => 'ベーシック', 4 => '無料上位', 5 => '無料'];
        if ($this->filter_plan !== null && isset($planLabels[$this->filter_plan])) {
            $parts[] = $planLabels[$this->filter_plan] . 'プラン';
        }

        return implode(' / ', $parts) . 'のオーナー';
    }
}
