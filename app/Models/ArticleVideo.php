<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleVideo extends Model
{
    protected $fillable = [
        'article_id', 'status', 'script', 'sns_caption',
        'audio_path', 'video_job_id', 'video_path', 'error_message',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isProcessing(): bool { return $this->status === 'processing'; }
    public function isDone(): bool       { return $this->status === 'done'; }
    public function isFailed(): bool     { return $this->status === 'failed'; }
}
