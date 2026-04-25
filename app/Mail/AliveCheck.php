<?php

namespace App\Mail;

use App\Models\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AliveCheck extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Shop $shop) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【ナイトワークリスト】掲載継続の確認（リンクから継続手続きをお願いします）',
        );
    }

    public function content(): Content
    {
        return new Content(text: 'emails.alive-check');
    }
}
