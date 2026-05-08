<?php
namespace App\Console\Commands;

use App\Models\CastSchedule;
use App\Mail\CastScheduleNoticeMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendScheduleNotifications extends Command
{
    protected $signature   = 'notify:schedules';
    protected $description = 'お気に入りキャストの新着シフトをユーザーにメール通知する（日次）';

    public function handle(): void
    {
        // 過去24時間以内に登録 & 今日〜3日後のシフト
        $schedules = CastSchedule::with(['cast.shop'])
            ->where('created_at', '>=', now()->subDay())
            ->whereBetween('work_date', [today(), today()->addDays(3)])
            ->whereHas('cast', fn($q) => $q->where('status', 'active'))
            ->get();

        if ($schedules->isEmpty()) {
            $this->info('新着シフトなし。');
            return;
        }

        // キャストごとにシフト日をまとめる: [cast_id => ['cast' => Cast, 'dates' => [...]]]
        $castMap = [];
        foreach ($schedules as $schedule) {
            $id = $schedule->cast_id;
            if (!isset($castMap[$id])) {
                $castMap[$id] = ['cast' => $schedule->cast, 'dates' => []];
            }
            $castMap[$id]['dates'][] = $schedule->work_date->format('m/d');
        }

        $castIds = array_keys($castMap);

        // お気に入り登録 & notify_working=true のユーザーをキャストIDで取得
        $favorites = \App\Models\CastFavorite::whereIn('cast_id', $castIds)
            ->with('user')
            ->get()
            ->filter(fn($f) => $f->user && $f->user->notify_working && $f->user->email)
            ->groupBy('user_id');

        $sent = 0;
        foreach ($favorites as $userId => $userFavorites) {
            $user = $userFavorites->first()->user;

            // そのユーザーがお気に入りしているキャストのシフト情報（最大10件）
            $items = $userFavorites
                ->map(fn($f) => $castMap[$f->cast_id] ?? null)
                ->filter()
                ->take(10)
                ->values();

            if ($items->isEmpty()) continue;

            Mail::to($user->email)->send(new CastScheduleNoticeMail($user, $items));
            $sent++;
        }

        $this->info("送信完了: {$sent}件");
    }
}
