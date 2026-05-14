<?php
namespace App\Console\Commands;

use App\Mail\CouponExpiryReminderMail;
use App\Models\DiscountCoupon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendCouponExpiryReminders extends Command
{
    protected $signature = 'coupons:send-expiry-reminders';
    protected $description = '有効期限3日前のクーポンをユーザーへリマインド';

    public function handle(): void
    {
        $target = now()->addDays(3)->toDateString();

        DiscountCoupon::whereDate('expires_at', $target)
            ->whereNull('used_at')
            ->whereNotNull('user_id')
            ->with(['user', 'shop'])
            ->cursor()
            ->each(function (DiscountCoupon $coupon) {
                if (!$coupon->user?->email) return;
                try {
                    Mail::to($coupon->user->email)->queue(new CouponExpiryReminderMail($coupon));
                } catch (\Throwable $e) {
                    \Log::warning('CouponExpiryReminderMail failed: ' . $e->getMessage());
                }
            });

        $this->info('クーポン期限リマインダー送信完了');
    }
}
