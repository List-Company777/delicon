<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotice extends Model
{
    protected $fillable = ['title', 'body', 'target', 'status', 'sent_count', 'sent_at'];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function targetLabel(): string
    {
        return match($this->target) {
            'active'   => '掲載中店舗のオーナー',
            'inactive' => '非公開店舗のオーナー',
            default    => '全店舗オーナー',
        };
    }
}
