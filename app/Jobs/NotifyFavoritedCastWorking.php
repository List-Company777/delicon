<?php
namespace App\Jobs;

use App\Models\Cast;
use App\Models\CastFavorite;
use App\Mail\CastWorkingNoticeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyFavoritedCastWorking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $castId) {}

    public function handle(): void
    {
        $cast = Cast::with(['shop'])->find($this->castId);
        if (!$cast) return;

        CastFavorite::where('cast_id', $this->castId)
            ->with('user')
            ->get()
            ->each(function ($fav) use ($cast) {
                if ($fav->user && $fav->user->notify_working && $fav->user->email) {
                    Mail::to($fav->user->email)->send(new CastWorkingNoticeMail($cast, $fav->user));
                }
            });
    }
}
