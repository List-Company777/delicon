<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GenerateThumbnails extends Command
{
    protected $signature   = 'images:generate-thumbnails {--force : 既存サムネイルも上書き再生成}';
    protected $description = '既存画像から検索結果カード用サムネイル（224×126）を一括生成する';

    public function handle(): int
    {
        $manager = new ImageManager(new Driver());
        $disk    = Storage::disk('public');
        $force   = $this->option('force');

        // JPG ファイル（_thumb を除く）を全件列挙
        $jpgFiles = collect($disk->allFiles('company'))
            ->filter(fn($f) => str_ends_with($f, '.jpg') && !str_contains($f, '_thumb'));

        $this->info("対象: {$jpgFiles->count()} 件");
        $bar = $this->output->createProgressBar($jpgFiles->count());
        $bar->start();

        $generated = 0;
        $skipped   = 0;

        foreach ($jpgFiles as $jpgPath) {
            $thumbWebp = str_replace('.jpg', '_thumb.webp', $jpgPath);
            $thumbJpg  = str_replace('.jpg', '_thumb.jpg',  $jpgPath);

            if (!$force && $disk->exists($thumbWebp)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            try {
                $fullPath = $disk->path($jpgPath);
                $thumb    = $manager->decode($fullPath)->cover(224, 126);
                $disk->put($thumbWebp, (string) $thumb->encode(new WebpEncoder(quality: 75)));
                $disk->put($thumbJpg,  (string) $thumb->encode(new JpegEncoder(quality: 80)));
                $generated++;
            } catch (\Exception $e) {
                $this->warn("\nスキップ: {$jpgPath} — " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("完了: 生成={$generated}件 スキップ={$skipped}件");
        return 0;
    }
}
