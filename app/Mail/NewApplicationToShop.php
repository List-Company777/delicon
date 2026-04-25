<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewApplicationToShop extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public Job $job,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【ナイトワークリスト】新しい応募が届きました',
        );
    }

    public function content(): Content
    {
        return new Content(text: 'emails.new-application-to-shop');
    }
}
