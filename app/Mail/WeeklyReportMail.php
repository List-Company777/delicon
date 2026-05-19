<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WeeklyReportMail extends Mailable
{
    public function __construct(public readonly array $stats) {}

    public function envelope(): Envelope
    {
        $date = now()->subDay()->format('Y/m/d');
        return new Envelope(subject: "【週次レポート】{$date} デリコン");
    }

    public function content(): Content
    {
        return new Content(text: 'emails.weekly-report');
    }
}
