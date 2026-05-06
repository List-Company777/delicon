<?php
namespace App\Jobs;

use App\Models\Cast;
use App\Models\User;
use App\Mail\NewCastNoticeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyNewCast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $castId) {}

    public function handle(): void
    {
        $cast = Cast::with(['shop'])->find($this->castId);
        if (!$cast) return;

        User::where('notify_new_cast', true)
            ->whereNotNull('email')
            ->cursor()
            ->each(function ($user) use ($cast) {
                Mail::to($user->email)->send(new NewCastNoticeMail($cast, $user));
            });
    }
}
