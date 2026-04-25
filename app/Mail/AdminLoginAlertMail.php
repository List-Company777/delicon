<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AdminLoginAlertMail extends Mailable
{
    public function __construct(public readonly string $ipAddress) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '【ナイトワークリスト】管理者ログイン通知');
    }

    public function content(): Content
    {
        return new Content(text: 'emails.admin-login-alert-text');
    }
}
