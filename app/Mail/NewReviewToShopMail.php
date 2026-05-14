<?php
namespace App\Mail;

use App\Models\CastReview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewReviewToShopMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly CastReview $review) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '【口コミ通知】' . ($this->review->cast?->name ?? '女性') . ' に新しい口コミが投稿されました');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.new-review-to-shop');
    }
}
