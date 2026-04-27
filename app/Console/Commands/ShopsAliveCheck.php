<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Services\ShopNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ShopsAliveCheck extends Command
{
    protected $signature   = 'shops:alive-check {--dry-run : メール送信・ステータス変更を行わず対象店舗のみ表示}';
    protected $description = '無料プランで3ヶ月ログインなしの店舗に生存確認メールを送り、2週間未確認の店舗を非公開にする';

    // 設定値（変更しやすいよう定数化）
    const INACTIVE_MONTHS  = 3;   // 最終ログインからの判定期間（月）
    const GRACE_DAYS       = 14;  // メール送信後の猶予日数
    const RESEND_DAYS      = 90;  // 確認済みから次回チェックまでの間隔（日）

    public function handle(): void
    {
        if ($this->option('dry-run')) {
            $this->warn('[DRY-RUN] メール送信・ステータス変更は行いません');
        }

        $this->inactivateExpired();
        $this->sendCheckMails();

        $this->info('done.');
    }

    /**
     * 猶予期限切れ（メール送信から GRACE_DAYS 以上経過 + 未確認）→ 非公開
     */
    private function inactivateExpired(): void
    {
        $shops = Shop::where('status', 'active')
            ->where('xml_enabled', false)
            ->whereNotNull('alive_check_sent_at')
            ->where('alive_check_sent_at', '<=', now()->subDays(self::GRACE_DAYS))
            ->where(function ($q) {
                // 一度も確認していない、またはメール送信より前に確認している
                $q->whereNull('alive_confirmed_at')
                  ->orWhereColumn('alive_confirmed_at', '<', 'alive_check_sent_at');
            })
            ->get();

        foreach ($shops as $shop) {
            $owner = $shop->users()->wherePivot('role', 'owner')->first();

            if ($this->option('dry-run')) {
                $this->line("[DRY-RUN] 非公開予定: [{$shop->id}] {$shop->name} → {$owner?->email}");
                continue;
            }

            $shop->update([
                'status'            => 'inactive',
                'alive_check_token' => null,
            ]);

            if ($owner) {
                ShopNotifier::sendInactivated($shop, $owner);
            }

            $this->line("非公開: [{$shop->id}] {$shop->name}");
        }
    }

    /**
     * 3ヶ月ログインなし・無料プランの店舗に確認メールを送信
     */
    private function sendCheckMails(): void
    {
        $shops = Shop::where('status', 'active')
            // 無料プランのみ（残高が入札単価を下回っている＝hasBudget()=false）
            ->whereColumn('budget_balance', '<', 'bid_price')
            // まだメールを送っていない、または前回確認済みから RESEND_DAYS 経過
            ->where(function ($q) {
                $q->whereNull('alive_check_sent_at')
                  ->orWhere(function ($q2) {
                      // 確認済みかつ RESEND_DAYS 経過したら再送対象
                      $q2->whereNotNull('alive_confirmed_at')
                         ->whereColumn('alive_confirmed_at', '>=', 'alive_check_sent_at')
                         ->where('alive_confirmed_at', '<=', now()->subDays(self::RESEND_DAYS));
                  });
            })
            ->with(['users' => fn($q) => $q->wherePivot('role', 'owner')])
            ->get();

        foreach ($shops as $shop) {
            $owner = $shop->users->first();
            if (! $owner) {
                continue;
            }

            // XML連携が有効な間はアクティブとみなす
            if ($shop->xml_enabled) {
                continue;
            }

            // XML連携で自動作成されてパスワードリセット未実施（一度もログインしていない）は対象外
            if (! $owner->last_login_at) {
                continue;
            }

            // 「実質的なアクティブ日時」= max(最終ログイン日時, XML連携終了日時)
            // XML終了後に3ヶ月のカウントを開始するため、xml_disabled_at を優先する
            $effectiveLastActive = $owner->last_login_at;
            if ($shop->xml_disabled_at && $shop->xml_disabled_at->gt($effectiveLastActive)) {
                $effectiveLastActive = $shop->xml_disabled_at;
            }

            if ($effectiveLastActive->gt(now()->subMonths(self::INACTIVE_MONTHS))) {
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("[DRY-RUN] メール送信予定: [{$shop->id}] {$shop->name} → {$owner->email}");
                continue;
            }

            $token = Str::random(64);
            $shop->update([
                'alive_check_token'   => $token,
                'alive_check_sent_at' => now(),
            ]);

            ShopNotifier::sendAliveCheck($shop, $owner);
            $this->line("送信: [{$shop->id}] {$shop->name} → {$owner->email}");
        }
    }
}
