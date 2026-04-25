<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class InquiryMail extends Mailable
{
    public function __construct(
        public readonly string $senderName,
        public readonly string $senderEmail,
        public readonly string $category,
        public readonly string $body,
        public readonly string $senderIp,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "【お問い合わせ】{$this->category}",
            replyTo: [new Address($this->senderEmail, $this->senderName)],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.inquiry-text',
        );
    }
}
