<?php

namespace App\Mail;

use App\Models\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlanApplicationRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Shop    $shop,
        public readonly int     $amount,
        public readonly int     $bidPrice,
        public readonly ?string $note = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "【{$this->shop->name}】有料プランの申し込みについて",
            replyTo: [new Address(config('mail.admin_address'), 'ナイトワークリスト')],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'emails.plan-application-rejected');
    }
}
