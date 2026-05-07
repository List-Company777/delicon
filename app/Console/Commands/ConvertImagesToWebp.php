<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ConvertImagesToWebp extends Command
{
    protected $signature = 'image:convert-webp
                            {--limit=0 : Max files to convert (0=all)}
                            {--dry-run : Count only, no conversion}';

    protected $description = 'Convert existing JPG images to WebP (big.jpg -> big.jpg.webp)';

    public function handle(): int
    {
        $limit  = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');
        $dir    = public_path('img/girl');

        if (! is_dir($dir)) {
            $this->error("Directory not found: {$dir}");
            return 1;
        }

        $this->info("Scanning: {$dir}");
        $files = $this->findJpgFiles($dir, $limit);
        $total = count($files);

        $this->info("Target: {$total} files");

        if ($dryRun) {
            $this->info('--dry-run: no conversion performed');
            return 0;
        }

        $converted = $skipped = $failed = 0;
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($files as $jpg) {
            $webp = $jpg . '.webp';
            if (file_exists($webp)) {
                $skipped++;
                $bar->advance();
                continue;
            }
            if ($this->toWebp($jpg, $webp)) {
                $converted++;
            } else {
                $failed++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done: converted={$converted} skipped={$skipped} failed={$failed}");
        return 0;
    }

    private function findJpgFiles(string $dir, int $limit): array
    {
        $files = [];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $f) {
            if ($f->isFile() && str_ends_with($f->getFilename(), 'big.jpg')) {
                $files[] = $f->getPathname();
                if ($limit > 0 && count($files) >= $limit) {
                    break;
                }
            }
        }
        return $files;
    }

    private function toWebp(string $src, string $dst): bool
    {
        $img = @imagecreatefromjpeg($src);
        if (! $img) {
            return false;
        }
        $ok = imagewebp($img, $dst, 80);
        imagedestroy($img);
        return $ok;
    }
}
