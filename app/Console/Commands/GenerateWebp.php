<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateWebp extends Command
{
    protected $signature   = 'images:generate-webp {--dry-run : 変換せずに対象件数だけ表示}';
    protected $description = 'WebPが存在しないJPG画像からWebPを一括生成する';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $total  = 0;
        $errors = 0;

        // ① storage/app/public 以下のすべての .jpg（shop main / banner / thumb / job）
        $storageBase = storage_path('app/public');
        $jpgFiles    = $this->findJpg($storageBase);

        foreach ($jpgFiles as $jpgPath) {
            $webpPath = $jpgPath . '.webp'; // main.jpg → main.jpg.webp は使わない
            // ImageService の規則: main.jpg → main.webp
            $webpPath = preg_replace('/\.jpg$/i', '.webp', $jpgPath);

            if (file_exists($webpPath)) continue;

            $total++;
            $rel = str_replace($storageBase . '/', '', $jpgPath);

            if ($dryRun) {
                $this->line("  [未生成] {$rel}");
                continue;
            }

            if ($this->convertToWebp($jpgPath, $webpPath, $this->qualityFor($jpgPath))) {
                $this->line("  OK: {$rel}");
            } else {
                $this->warn("  NG: {$rel}");
                $errors++;
            }
        }

        // ② public/img/girl 以下の big.jpg → big.jpg.webp
        $girlBase = public_path('img/girl');
        $bigFiles = $this->findBigJpg($girlBase);

        foreach ($bigFiles as $jpgPath) {
            $webpPath = $jpgPath . '.webp'; // big.jpg → big.jpg.webp（Cast モデルの規則）

            if (file_exists($webpPath)) continue;

            $total++;
            $rel = str_replace(public_path() . '/', '', $jpgPath);

            if ($dryRun) {
                $this->line("  [未生成] {$rel}");
                continue;
            }

            if ($this->convertToWebp($jpgPath, $webpPath, 80)) {
                $this->line("  OK: {$rel}");
            } else {
                $this->warn("  NG: {$rel}");
                $errors++;
            }
        }

        if ($dryRun) {
            $this->info("対象: {$total} 件（dry-run のため変換しません）");
        } else {
            $done = $total - $errors;
            $this->info("完了: {$done}/{$total} 件 生成しました（失敗: {$errors} 件）");
        }

        return self::SUCCESS;
    }

    private function findJpg(string $dir): array
    {
        $result = [];
        if (! is_dir($dir)) return $result;
        $rit = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($rit as $file) {
            if ($file->isFile() && preg_match('/\.jpg$/i', $file->getFilename())) {
                $result[] = $file->getPathname();
            }
        }
        return $result;
    }

    private function findBigJpg(string $dir): array
    {
        $result = [];
        if (! is_dir($dir)) return $result;
        $rit = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($rit as $file) {
            if ($file->isFile() && preg_match('/big\.jpg$/i', $file->getFilename())) {
                $result[] = $file->getPathname();
            }
        }
        return $result;
    }

    private function qualityFor(string $path): int
    {
        if (str_contains($path, '_thumb')) return 75;
        return 80;
    }

    private function convertToWebp(string $src, string $dst, int $quality): bool
    {
        try {
            $data = @file_get_contents($src); if (!$data) return false; $img = @imagecreatefromstring($data);
            if (! $img) return false;
            imagepalettetotruecolor($img);
            $ok = imagewebp($img, $dst, $quality);
            imagedestroy($img);
            if ($ok && filesize($dst) === 0) { unlink($dst); return false; }
            return $ok;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
