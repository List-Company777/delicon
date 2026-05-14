<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportShopAccounts extends Command
{
    protected $signature = 'import:shop-accounts
                            {--dry-run : DBへの書き込みを行わない}
                            {--limit=0 : 取得件数上限（0=全件）}';

    protected $description = '旧CakePHPサイト(delicon_old)からshopアカウント(users/shop_users)をインポートする';

    private bool $dryRun   = false;
    private int  $created  = 0;
    private int  $linked   = 0;
    private int  $skipped  = 0;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        $limit        = (int) $this->option('limit');

        if ($this->dryRun) {
            $this->warn('[DRY-RUN] DBへの書き込みは行いません');
        }

        // 新DBのshopsのうち old_id があるものと旧DBをJOINしてemail等を取得
        $newShops = DB::table('shops')
            ->whereNotNull('old_id')
            ->select('id', 'name', 'old_id')
            ->orderBy('id')
            ->when($limit > 0, fn($q) => $q->limit($limit))
            ->get();

        $total = $newShops->count();
        $this->info("対象shop件数: {$total}件");

        // 旧DB shops を old_id -> row でキャッシュ
        $oldShopIds = $newShops->pluck('old_id')->all();
        $oldShops   = DB::connection('mysql_old')
            ->table('shops')
            ->whereIn('id', $oldShopIds)
            ->select('id', 'email', 'password')
            ->get()
            ->keyBy('id');

        // 既に shop_users に紐づいている shop_id をキャッシュ
        $linkedShopIds = DB::table('shop_users')
            ->whereIn('shop_id', $newShops->pluck('id')->all())
            ->pluck('shop_id')
            ->flip()
            ->all();

        foreach ($newShops as $shop) {
            $oldShop = $oldShops[$shop->old_id] ?? null;

            // emailなし
            if (!$oldShop || empty(trim((string) $oldShop->email))) {
                $this->line("  - {$shop->name} emailなし");
                $this->skipped++;
                continue;
            }

            // 既に紐づき済み
            if (array_key_exists($shop->id, $linkedShopIds)) {
                $this->line("  スキップ [{$shop->name}] 既にshop_users紐づき済み");
                $this->skipped++;
                continue;
            }

            $email = trim($oldShop->email);

            // 既存ユーザー確認
            $existingUser = DB::table('users')->where('email', $email)->first();

            if ($existingUser) {
                // 既存ユーザーを紐づけ
                $this->line("  ~ {$email} -> 既存user ID {$existingUser->id} で紐づけ");
                if (!$this->dryRun) {
                    DB::table('shop_users')->insert([
                        'shop_id'    => $shop->id,
                        'user_id'    => $existingUser->id,
                        'role'       => 'owner',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $this->linked++;
            } else {
                // 新規ユーザー作成
                $this->line("  + {$shop->name} ({$email})");
                if (!$this->dryRun) {
                    $userId = DB::table('users')->insertGetId([
                        'name'              => $shop->name,
                        'email'             => $email,
                        'password'          => Hash::make((string) ($oldShop->password ?? '')),
                        'role'              => 'company',
                        'email_verified_at' => now(),
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);

                    DB::table('shop_users')->insert([
                        'shop_id'    => $shop->id,
                        'user_id'    => $userId,
                        'role'       => 'owner',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $this->created++;
            }
        }

        $this->info('');
        $this->info("完了: 作成{$this->created}件 / 紐づけ{$this->linked}件 / スキップ{$this->skipped}件");

        return self::SUCCESS;
    }
}
