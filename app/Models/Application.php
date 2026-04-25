<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Application extends Model
{
    protected $fillable = [
        'job_id', 'shop_id', 'applicant_name', 'applicant_age',
        'applicant_email', 'applicant_tel', 'message', 'status',
        'reply_token', 'xml_source', 'api_sent_at',
    ];

    protected $casts = [
        'api_sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $application) {
            $application->reply_token ??= (string) Str::uuid();
        });
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ApplicationMessage::class)->orderBy('created_at');
    }

    /** 店舗側の未読メッセージ数（応募者から来たメッセージで read_at = null） */
    public function unreadByShopCount(): int
    {
        return $this->messages()
            ->where('sender', 'applicant')
            ->whereNull('read_at')
            ->count();
    }
}
