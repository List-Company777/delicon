<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CastDeletionNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $castName,
        public readonly string $requesterName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '【デリヘルリスト】削除依頼を受け付けました');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.cast-deletion-notification');
    }
}
