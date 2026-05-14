<?php
namespace App\Mail;

use App\Models\DiscountCoupon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CouponExpiryReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly DiscountCoupon $coupon) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '【期限まであと3日】' . ($this->coupon->shop?->name ?? '') . ' の割引クーポンをお忘れなく');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.coupon-expiry-reminder');
    }
}
