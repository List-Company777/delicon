<?php

namespace App\Console\Commands;

use App\Models\PushSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class SendWeeklyPush extends Command
{
    protected $signature   = 'push:weekly {--dry-run : 実際には送信せず件数のみ表示}';
    protected $description = '毎週木曜日の週末プッシュ通知を全購読者に送信';

    public function handle(): int
    {
        $messages = [
            ['title' => 'さぁ週末だ！🎉',       'body' => '今夜の相手はもう決まった？デリコンで探そう'],
            ['title' => '週末の夜を楽しもう🌙',  'body' => '今週もお疲れ様。今夜は自分へのご褒美を'],
            ['title' => '金曜の夜、何する？✨',   'body' => 'デリコンで今夜の出会いを見つけよう'],
            ['title' => 'もうすぐ週末！🔥',       'body' => '遊ぼうぜ。デリコンで相手を探す'],
        ];

        $msg   = $messages[array_rand($messages)];
        $subs  = PushSubscription::all();
        $count = $subs->count();

        $this->info("Sending to {$count} subscribers: [{$msg['title']}]");

        if ($this->option('dry-run') || $count === 0) {
            $this->info($count === 0 ? 'No subscribers.' : 'Dry-run: skipped.');
            return 0;
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject'    => config('services.vapid.subject'),
                'publicKey'  => config('services.vapid.public_key'),
                'privateKey' => config('services.vapid.private_key'),
            ],
        ]);

        $payload = json_encode(['title' => $msg['title'], 'body' => $msg['body'], 'url' => '/']);

        foreach ($subs as $sub) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint'        => $sub->endpoint,
                    'publicKey'       => $sub->public_key,
                    'authToken'       => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding,
                ]),
                $payload
            );
        }

        $failed = 0;
        foreach ($webPush->flush() as $report) {
            if (! $report->isSuccess()) {
                PushSubscription::where('endpoint', $report->getEndpoint())->delete();
                $failed++;
            }
        }

        $sent = $count - $failed;
        DB::table('push_send_logs')->insert([
            'title'   => $msg['title'],
            'sent'    => $sent,
            'failed'  => $failed,
            'sent_at' => now(),
        ]);

        $this->info("Done. success={$sent} failed/removed={$failed}");
        return 0;
    }
}
