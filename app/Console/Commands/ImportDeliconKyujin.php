<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportDeliconKyujin extends Command
{
    protected $signature = 'import:delicon-kyujin {--dry-run : データを変更せず確認のみ}';
    protected $description = '旧deliconDBからkyujin（求人）情報を新DBに移行する';

    private string $oldHost = '133.125.148.15';
    private string $oldUser = 'pcnrtbdc';
    private string $oldPass = 'z26muTu9L8';
    private string $oldDb   = 'pcnrtbdc_delicon';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $oldPdo = new \PDO(
            "mysql:host={$this->oldHost};dbname={$this->oldDb};charset=utf8mb4",
            $this->oldUser,
            $this->oldPass,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );

        $rows = $oldPdo->query("
            SELECT
                id AS old_id,
                kyujin_address, kyujin_time, kyujin_person, kyujin_tel,
                kyujin_junre, work_address, near_station, oubo_shikaku,
                work_time, kyujin_model, kyujin_speciality,
                kyujin1_file_name, kyujin2_file_name, kyujin3_file_name,
                kyujin1_text, kyujin2_text, kyujin3_text
            FROM shops
            WHERE (
                kyujin_model IS NOT NULL AND kyujin_model != ''
                OR kyujin_speciality IS NOT NULL AND kyujin_speciality != ''
                OR kyujin_tel IS NOT NULL AND kyujin_tel != ''
            )
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $this->info("旧DBから " . count($rows) . " 件取得");

        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $shop = DB::table('shops')->where('old_id', $row['old_id'])->first();

            if (!$shop) {
                $skipped++;
                continue;
            }

            $data = collect($row)->except('old_id')
                ->map(fn($v) => ($v === '' ? null : $v))
                ->all();

            if (!$dryRun) {
                DB::table('shops')->where('id', $shop->id)->update($data);
            }

            $updated++;
        }

        $this->info($dryRun ? "[dry-run] " : "" . "更新: {$updated} 件 / スキップ(old_id不一致): {$skipped} 件");

        return self::SUCCESS;
    }
}
