<?php
namespace App\Mail;

use App\Models\Cast;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewCastNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Cast $cast, public User $user) {}

    public function build(): static
    {
        return $this->subject('【デリコン】新人キャスト「' . $this->cast->name . '」が登録されました')
                    ->view('emails.new-cast-notice');
    }
}
