<?php
namespace App\Mail;

use App\Models\Cast;
use App\Models\CastShiftRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShiftRequestSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Cast $cast,
        public readonly CastShiftRequest $shiftRequest,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "【シフト申請】{$this->cast->name} から新しいシフト申請が届きました",
            replyTo: [new Address(config('mail.admin_address'), 'デリヘルリスト')],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'emails.shift-request-submitted');
    }
}
