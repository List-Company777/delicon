<?php

namespace App\Mail;

use App\Models\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShopApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Shop $shop,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "【{$this->shop->name}】掲載が開始されました",
            replyTo: [new Address(config('mail.admin_address'), 'ナイトワークリスト')],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'emails.shop-approved');
    }
}
