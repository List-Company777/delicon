<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportSangyoText extends Command
{
    protected $signature = 'import:sangyo-text';
    protected $description = '旧DBからsangyo_text1/2/3を新DBにインポート';

    public function handle(): void
    {
        $oldDb = [
            'host' => '133.125.148.15',
            'port' => 3306,
            'database' => 'pcnrtbdc_delicon',
            'username' => 'pcnrtbdc',
            'password' => 'z26muTu9L8',
        ];

        config(['database.connections.old_delicon' => array_merge([
            'driver' => 'mysql', 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '', 'strict' => false,
        ], $oldDb)]);

        $rows = DB::connection('old_delicon')
            ->table('shops')
            ->whereNotNull('sangyo_text1')
            ->where('sangyo_text1', '!=', '')
            ->select('id', 'sangyo_text1', 'sangyo_text2', 'sangyo_text3')
            ->get();

        $this->info("対象: {$rows->count()} 件");

        $updated = 0;
        foreach ($rows as $row) {
            $affected = DB::table('shops')
                ->where('old_id', $row->id)
                ->update([
                    'sangyo_text1' => mb_substr($row->sangyo_text1 ?? '', 0, 30),
                    'sangyo_text2' => mb_substr($row->sangyo_text2 ?? '', 0, 30),
                    'sangyo_text3' => mb_substr($row->sangyo_text3 ?? '', 0, 30),
                ]);
            $updated += $affected;
        }

        $this->info("完了: {$updated} 件更新");
    }
}
