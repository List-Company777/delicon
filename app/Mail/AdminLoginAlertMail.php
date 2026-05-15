<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AdminLoginAlertMail extends Mailable
{
    public function __construct(
        public readonly string $ipAddress,
        public readonly bool $isFailed = false,
        public readonly bool $isUnknownIp = false,
    ) {}

    public function envelope(): Envelope
    {
        $tag = $this->isFailed ? '不正ログイン試行' : '許可外IPアクセス';
        return new Envelope(subject: "【警告:{$tag}】【デリコン】管理者ログイン通知");
    }

    public function content(): Content
    {
        return new Content(text: 'emails.admin-login-alert-text');
    }
}
