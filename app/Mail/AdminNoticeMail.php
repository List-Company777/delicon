<?php

namespace App\Mail;

use App\Models\AdminNotice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AdminNotice $notice) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【デリヘルリスト】' . $this->notice->title,
            replyTo: [new Address(config('mail.admin_address'), 'デリヘルリスト')],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'emails.admin-notice');
    }
}
