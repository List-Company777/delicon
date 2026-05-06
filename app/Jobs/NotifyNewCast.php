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

        $castTypeId = $cast->type_id;
        $areaId     = $cast->shop?->area_id;

        User::where('notify_new_cast', true)
            ->whereNotNull('email')
            ->cursor()
            ->each(function (User $user) use ($cast, $castTypeId, $areaId) {
                // タイプ設定がある場合: キャストのタイプが含まれているか確認
                $typePrefs = $user->pref_cast_type_ids ?? [];
                if (!empty($typePrefs) && $castTypeId && !in_array($castTypeId, $typePrefs)) {
                    return;
                }

                // エリア設定がある場合: キャストの店舗エリアが含まれているか確認
                $areaPrefs = $user->pref_area_ids ?? [];
                if (!empty($areaPrefs) && $areaId && !in_array($areaId, $areaPrefs)) {
                    return;
                }

                Mail::to($user->email)->send(new NewCastNoticeMail($cast, $user));
            });
    }
}
