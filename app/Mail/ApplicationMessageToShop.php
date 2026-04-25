<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\ApplicationMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationMessageToShop extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public ApplicationMessage $applicationMessage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【ナイトワークリスト】' . $this->application->applicant_name . 'さんから返信が届きました',
        );
    }

    public function content(): Content
    {
        return new Content(text: 'emails.application-message-to-shop');
    }
}
