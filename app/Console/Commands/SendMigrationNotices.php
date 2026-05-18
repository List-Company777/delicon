<?php

namespace App\Console\Commands;

use App\Mail\MigrationNoticeMail;
use App\Models\Shop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMigrationNotices extends Command
{
    protected $signature = 'notify:migration
                            {--dry-run : 送信せずに対象一覧を表示}
                            {--limit=0 : 送信件数上限（0=全件）}
                            {--shop-id= : 特定店舗IDのみ送信（テスト用）}';

    protected $description = '旧デリコンから移転した全店舗に移転案内メールを送信';

    public function handle(): void
    {
        $query = Shop::with('users')
            ->whereHas('users')
            ->orderBy('id');

        if ($shopId = $this->option('shop-id')) {
            $query->where('id', $shopId);
        }

        if ($limit = (int) $this->option('limit')) {
            $query->limit($limit);
        }

        $shops = $query->get();

        $this->info("対象: {$shops->count()} 件");

        if ($this->option('dry-run')) {
            foreach ($shops as $shop) {
                $email = $shop->users->first()->email;
                $this->line("[{$shop->id}] {$shop->name} → {$email}");
            }
            return;
        }

        if (!$this->confirm('上記件数に移転案内メールを送信しますか？')) {
            $this->info('キャンセルしました。');
            return;
        }

        $sent = 0;
        $failed = 0;

        foreach ($shops as $shop) {
            $user = $shop->users->first();
            try {
                Mail::to($user->email)->send(new MigrationNoticeMail($shop, $user->email));
                $this->line("✓ [{$shop->id}] {$shop->name} → {$user->email}");
                $sent++;
                usleep(200000); // 0.2秒待機（レート制限対策）
            } catch (\Exception $e) {
                $this->error("✗ [{$shop->id}] {$shop->name}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("完了: 送信 {$sent} 件 / 失敗 {$failed} 件");
    }
}
