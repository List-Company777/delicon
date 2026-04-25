<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ReportMail extends Mailable
{
    public function __construct(
        public readonly string $targetType,   // 'shop' or 'job'
        public readonly int    $targetId,
        public readonly string $targetName,
        public readonly int    $shopId,
        public readonly string $shopName,
        public readonly string $reason,
        public readonly string $comment,
        public readonly string $reporterEmail,
        public readonly string $reporterIp,
    ) {}

    public function envelope(): Envelope
    {
        $label = $this->targetType === 'shop' ? '営業' : '求人';
        return new Envelope(
            subject: "【通報】{$label}：{$this->shopName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.report-text',
        );
    }
}
