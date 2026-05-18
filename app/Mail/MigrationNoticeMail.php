<?php

namespace App\Mail;

use App\Models\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MigrationNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Shop $shop,
        public readonly string $loginEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【重要】デリコンはデリヘルリストにリニューアルしました',
            replyTo: [new Address(config('mail.admin_address'), 'デリヘルリスト')],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'emails.migration-notice');
    }
}
