<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanOrphanedImages extends Command
{
    protected $signature = 'images:clean-orphaned
                            {--dry-run : 削除せずに対象ファイル/ディレクトリを表示のみ}';

    protected $description = '削除済み店舗・キャストの孤立画像ファイルを掃除する';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('[DRY-RUN] ファイルは削除しません');
        }

        $deleted = 0;

        // 1. 店舗ディレクトリ: storage/app/public/company/{shopId}/
        $this->info('--- 店舗ディレクトリ確認中 ---');
        $existingShopIds = DB::table('shops')->pluck('id')->flip()->all();
        $dirs = Storage::disk('public')->directories('company');
        foreach ($dirs as $dir) {
            $shopId = (int) basename($dir);
            if (!isset($existingShopIds[$shopId])) {
                $this->line("  削除: {$dir}/");
                if (!$dryRun) {
                    Storage::disk('public')->deleteDirectory($dir);
                }
                $deleted++;
            }
        }

        // 2. キャスト日記ディレクトリ: storage/app/public/casts/{castId}/
        $this->info('--- キャスト日記ディレクトリ確認中 ---');
        $existingCastIds = DB::table('casts')->pluck('id')->flip()->all();
        $castDirs = Storage::disk('public')->directories('casts');
        foreach ($castDirs as $dir) {
            $castId = (int) basename($dir);
            if (!isset($existingCastIds[$castId])) {
                $this->line("  削除: {$dir}/");
                if (!$dryRun) {
                    Storage::disk('public')->deleteDirectory($dir);
                }
                $deleted++;
            }
        }

        // 3. キャストプロフィール画像: public/img/girl/uploads/{base}big.jpg(.webp)
        $this->info('--- キャストプロフィール画像確認中 ---');
        $uploadsDir = public_path('img/girl/uploads');
        if (is_dir($uploadsDir)) {
            // DBに存在するimg_file_nameのベース部分を収集
            $validBases = DB::table('casts')
                ->where('img_file_name', 'like', '/img/girl/uploads/%')
                ->pluck('img_file_name')
                ->map(fn($v) => basename($v))
                ->flip()
                ->all();

            $files = glob("{$uploadsDir}/*big.jpg") ?: [];
            foreach ($files as $file) {
                $filename = basename($file);
                // "{base}big.jpg" → "{base}"
                $base = substr($filename, 0, -strlen('big.jpg'));
                if (!isset($validBases[$base])) {
                    $webp = $file . '.webp';
                    $this->line("  削除: img/girl/uploads/{$filename}");
                    if (!$dryRun) {
                        @unlink($file);
                        if (file_exists($webp)) @unlink($webp);
                    }
                    $deleted++;
                }
            }
        }

        $label = $dryRun ? '対象' : '削除済み';
        $this->info("{$label}: {$deleted} 件");
        return 0;
    }
}
