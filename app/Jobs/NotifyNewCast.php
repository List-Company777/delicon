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
        $bodyId     = $cast->body_id;
        $age        = $cast->age;

        User::where('notify_new_cast', true)
            ->whereNotNull('email')
            ->cursor()
            ->each(function (User $user) use ($cast, $castTypeId, $areaId, $bodyId, $age) {
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

                // 体型設定がある場合: キャストの体型が含まれているか確認
                $bodyPrefs = $user->pref_body_type_ids ?? [];
                if (!empty($bodyPrefs) && $bodyId && !in_array($bodyId, $bodyPrefs)) {
                    return;
                }

                // 年齢設定がある場合: キャストの年齢が範囲内か確認
                if ($user->pref_age_min !== null && $age !== null && $age < $user->pref_age_min) {
                    return;
                }
                if ($user->pref_age_max !== null && $age !== null && $age > $user->pref_age_max) {
                    return;
                }

                Mail::to($user->email)->send(new NewCastNoticeMail($cast, $user));
            });
    }
}
