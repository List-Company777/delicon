<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class UserLoginAlertMail extends Mailable
{
    public function __construct(public readonly string $ipAddress) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '【ナイトワークリスト】ログイン通知');
    }

    public function content(): Content
    {
        return new Content(text: 'emails.user-login-alert-text');
    }
}
