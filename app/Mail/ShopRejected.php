<?php

namespace App\Mail;

use App\Models\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShopRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Shop    $shop,
        public readonly ?string $note = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "【{$this->shop->name}】掲載申請の審査結果について",
            replyTo: [new Address(config('mail.admin_address'), 'デリヘルリスト')],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'emails.shop-rejected');
    }
}
