<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【ナイトワークリスト】' . $this->application->shop->name . ' からご連絡',
        );
    }

    public function content(): Content
    {
        return new Content(text: 'emails.application-rejected');
    }
}
