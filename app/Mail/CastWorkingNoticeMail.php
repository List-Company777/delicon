<?php
namespace App\Mail;

use App\Models\Cast;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CastWorkingNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Cast $cast, public User $user) {}

    public function build(): static
    {
        return $this->subject('【デリコン】' . $this->cast->name . 'さんが本日出勤します')
                    ->view('emails.cast-working-notice');
    }
}
