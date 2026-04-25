<?php

namespace App\Mail;

use App\Models\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BudgetCredited extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Shop $shop,
        public readonly int  $amount,
        public readonly int  $bidPrice,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "【{$this->shop->name}】有料プランが有効になりました",
        );
    }

    public function content(): Content
    {
        return new Content(text: 'emails.budget-credited');
    }
}
