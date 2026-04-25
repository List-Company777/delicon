<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ShopContactMail extends Mailable
{
    public function __construct(
        public readonly string $shopName,
        public readonly string $senderName,
        public readonly string $senderEmail,
        public readonly string $category,
        public readonly string $contactSubject,
        public readonly string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->senderEmail, $this->senderName)],
            subject: "[{$this->shopName}] {$this->contactSubject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.shop-contact',
        );
    }
}
