<?php
namespace App\Mail;
use App\Models\DiscountCoupon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DiscountCouponMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DiscountCoupon $coupon) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '【' . $this->coupon->shop->name . '】割引クーポンのお知らせ');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.discount-coupon');
    }
}
