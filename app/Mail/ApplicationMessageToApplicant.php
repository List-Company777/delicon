<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\ApplicationMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationMessageToApplicant extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public ApplicationMessage $applicationMessage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【デリヘルリスト】' . $this->application->shop->name . 'からメッセージ（下記リンクから返信）',
        );
    }

    public function content(): Content
    {
        return new Content(text: 'emails.application-message-to-applicant');
    }
}
