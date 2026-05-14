<?php
namespace App\Mail;

use App\Models\CastReview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewRepliedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly CastReview $review) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '【' . ($this->review->cast?->shop?->name ?? 'デリヘルリスト') . '】口コミへの返信が届きました');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.review-replied');
    }
}
