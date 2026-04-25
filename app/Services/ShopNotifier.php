<?php

namespace App\Services;

use App\Mail\AliveCheck;
use App\Mail\ShopInactivated;
use App\Models\LineMessageLog;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ShopNotifier
{
    /**
     * 生存確認メール（+ 将来: LINE通知）
     */
    public static function sendAliveCheck(Shop $shop, User $owner): void
    {
        // メール
        try {
            Mail::to($owner->email, $owner->name)->send(new AliveCheck($shop));
        } catch (\Exception $e) {
            Log::error("AliveCheck mail failed: shop={$shop->id} - {$e->getMessage()}");
        }

        // LINE
        if ($owner->line_user_id && config('services.line.messaging_token')) {
            static::sendLinePush(
                $owner->line_user_id,
                "【ナイトワークリスト】掲載継続の確認\n"
                . "以下のリンクから継続手続きをお願いします（2週間以内）\n"
                . route('manage.alive', $shop->alive_check_token),
                'alive_check'
            );
        }
    }

    /**
     * 非公開通知（+ 将来: LINE通知）
     */
    public static function sendInactivated(Shop $shop, User $owner): void
    {
        // メール
        try {
            Mail::to($owner->email, $owner->name)->send(new ShopInactivated($shop));
        } catch (\Exception $e) {
            Log::error("ShopInactivated mail failed: shop={$shop->id} - {$e->getMessage()}");
        }

        // LINE
        if ($owner->line_user_id && config('services.line.messaging_token')) {
            static::sendLinePush(
                $owner->line_user_id,
                "【ナイトワークリスト】{$shop->name} の掲載を一時停止しました。\n"
                . "再開は管理画面よりログインしてお手続きください。\n"
                . route('login'),
                'inactivated'
            );
        }
    }

    public static function sendLinePush(string $lineUserId, string $message, string $type = 'shop_notify'): void
    {
        try {
            $res = Http::withToken(config('services.line.messaging_token'))
                ->post('https://api.line.me/v2/bot/message/push', [
                    'to'       => $lineUserId,
                    'messages' => [['type' => 'text', 'text' => $message]],
                ]);

            if ($res->successful()) {
                LineMessageLog::create(['type' => $type, 'line_user_id' => $lineUserId]);
            }
        } catch (\Exception $e) {
            Log::error("LINE push failed: userId={$lineUserId} - {$e->getMessage()}");
        }
    }
}
