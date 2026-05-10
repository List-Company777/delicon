<?php
namespace App\Console\Commands;

use App\Models\Cast;
use App\Models\ShopNotification;
use App\Mail\NewCastNoticeMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendNewCastNotifications extends Command
{
    protected $signature   = 'notify:new-casts';
    protected $description = '新人キャスト登録をショップ通知登録ユーザーにメール送信する（日次）';

    public function handle(): void
    {
        // 過去24時間以内に登録かつ is_new フラグありのキャスト
        $newCasts = Cast::with(['shop'])
            ->where('is_new', true)
            ->where('created_at', '>=', now()->subDay())
            ->whereHas('shop', fn($q) => $q->where('status', 'active'))
            ->where('status', 'active')
            ->get();

        if ($newCasts->isEmpty()) {
            $this->info('新人キャストなし。');
            return;
        }

        $sent = 0;

        foreach ($newCasts as $cast) {
            if (!$cast->shop_id) continue;

            // このショップの通知登録ユーザー（notify_new_cast=true）
            $subscribers = ShopNotification::where('shop_id', $cast->shop_id)
                ->with('user')
                ->get()
                ->filter(fn($n) => $n->user && $n->user->notify_new_cast && $n->user->email)
                ->map(fn($n) => $n->user)
                ->unique('id');

            foreach ($subscribers as $user) {
                Mail::to($user->email)->send(new NewCastNoticeMail($cast, $user));
                $sent++;
            }
        }

        $this->info("送信完了: {$sent}件（新人キャスト {$newCasts->count()} 名）");
    }
}
