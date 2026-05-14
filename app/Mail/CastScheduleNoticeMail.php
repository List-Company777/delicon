<?php
namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CastScheduleNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Collection $items  // [['cast' => Cast, 'dates' => ['05/10', '05/11']], ...]
    ) {}

    public function build(): static
    {
        $count = $this->items->count();
        $subject = $count === 1
            ? '【デリヘルリスト】' . $this->items->first()['cast']->name . 'さんのシフトが入りました'
            : "【デリヘルリスト】お気に入りの{$count}名のシフトが入りました";

        return $this->subject($subject)->view('emails.cast-schedule-notice');
    }
}
