<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LineCheckQuota extends Command
{
    protected $signature = 'line:check-quota';
    protected $description = 'LINE Messaging API の当月送信数を確認し、上限に近づいたら管理者に通知する';

    private const ALERT_THRESHOLD = 180;

    public function handle(): int
    {
        $token = config('services.line.messaging_token');

        if (!$token) {
            $this->warn('LINE_MESSAGING_CHANNEL_ACCESS_TOKEN が未設定です。');
            return self::SUCCESS;
        }

        try {
            $quotaRes      = Http::withToken($token)->get('https://api.line.me/v2/bot/message/quota');
            $consumptionRes = Http::withToken($token)->get('https://api.line.me/v2/bot/message/quota/consumption');

            $limit       = $quotaRes->json('value') ?? 0;
            $consumption = $consumptionRes->json('totalUsage') ?? 0;

            Log::info("LINE quota: {$consumption}/{$limit}");
            $this->info("LINE quota: {$consumption}/{$limit}");

            if ($consumption >= self::ALERT_THRESHOLD) {
                $this->sendAlert($consumption, $limit);
            }
        } catch (\Exception $e) {
            Log::error("LineCheckQuota failed: {$e->getMessage()}");
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function sendAlert(int $consumption, int $limit): void
    {
        $adminEmail = 'line@up-stage.info';
        $subject    = "【警告】LINE通知の送信数が上限に近づいています ({$consumption}/{$limit})";
        $body       = implode("\n", [
            "LINE Messaging API の当月送信数が上限に近づいています。",
            "",
            "送信済み : {$consumption} 通",
            "月間上限 : {$limit} 通",
            "残り     : " . ($limit - $consumption) . " 通",
            "",
            "上限を超えると通知が送信されなくなります。",
            "LINE Developersコンソールでプランのアップグレードをご検討ください。",
            "https://developers.line.biz/console/",
        ]);

        Mail::raw($body, function ($message) use ($adminEmail, $subject) {
            $message->to($adminEmail)->subject($subject);
        });

        Log::warning("LINE quota alert sent: {$consumption}/{$limit}");
        $this->warn("アラートメール送信済み: {$consumption}/{$limit}");
    }
}
