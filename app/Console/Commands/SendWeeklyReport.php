<?php

namespace App\Console\Commands;

use App\Mail\WeeklyReportMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendWeeklyReport extends Command
{
    protected $signature   = 'report:weekly-push';
    protected $description = '毎週金曜日にプッシュ通知・サイト指標の週次レポートをadminへ送信';

    public function handle(): int
    {
        $weekAgo = now()->subDays(7);

        $shopUserIds = DB::table('shop_users')->distinct()->pluck('user_id');

        // 木曜日の直近プッシュ送信ログ
        $pushLog = DB::table('push_send_logs')
            ->where('sent_at', '>=', $weekAgo)
            ->orderByDesc('sent_at')
            ->first();

        $stats = [
            'users_total'       => DB::table('users')->whereNotIn('id', $shopUserIds)->count(),
            'users_week'        => DB::table('users')->whereNotIn('id', $shopUserIds)->where('created_at', '>=', $weekAgo)->count(),
            'shop_accounts'     => $shopUserIds->count(),
            'push_subs'         => DB::table('push_subscriptions')->count(),
            'push_sent'         => $pushLog?->sent,
            'push_failed'       => $pushLog?->failed,
            'active_shops'      => DB::table('shops')->where('status', 'active')->count(),
            'active_casts'      => DB::table('casts')->where('status', 'active')->count(),
            'new_shops_week'    => DB::table('shops')->where('created_at', '>=', $weekAgo)->count(),
            'cast_views_week'   => DB::table('cast_views')->where('viewed_at', '>=', $weekAgo)->count(),
            'favorites_week'    => DB::table('cast_favorites')->where('created_at', '>=', $weekAgo)->count(),
            'favorites_total'   => DB::table('cast_favorites')->count(),
            'shop_notify_total' => DB::table('shop_notifications')->count(),
            'reviews_week'      => DB::table('cast_reviews')->where('created_at', '>=', $weekAgo)->count(),
            'plan_apps_week'    => DB::table('shop_plan_applications')->where('created_at', '>=', $weekAgo)->count(),
            'plan_apps_pending' => DB::table('shop_plan_applications')->where('status', 'pending')->count(),
        ];

        Mail::to('webmaster@delicon.jp')->send(new WeeklyReportMail($stats));

        $this->info('Weekly report sent.');
        return self::SUCCESS;
    }
}
