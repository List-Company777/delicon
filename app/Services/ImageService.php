<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * 店舗メイン画像を保存（JPG + WebP + サムネイル）
     * @return string  storage/app/public/ からの相対パス（main_image カラムに保存する値）
     */
    public function saveShopMainImage(UploadedFile $file, int $shopId): string
    {
        $dir  = "company/{$shopId}";
        $base = "{$dir}/main";

        Storage::disk('public')->makeDirectory($dir);

        $img = $this->manager->decode($file->getPathname());
        Storage::disk('public')->put("{$base}.jpg",  (string) $img->encode(new JpegEncoder(quality: 85)));
        Storage::disk('public')->put("{$base}.webp", (string) $img->encode(new WebpEncoder(quality: 80)));

        // バナー（900×360px crop、5:2 比率・店舗詳細ページヘッダー用）
        $banner = $this->manager->decode($file->getPathname())->cover(900, 360);
        Storage::disk('public')->put("{$base}_banner.jpg",  (string) $banner->encode(new JpegEncoder(quality: 85)));
        Storage::disk('public')->put("{$base}_banner.webp", (string) $banner->encode(new WebpEncoder(quality: 80)));

        // サムネイル（224×126px crop、検索結果カード用）
        $thumb = $this->manager->decode($file->getPathname())->cover(224, 126);
        Storage::disk('public')->put("{$base}_thumb.webp", (string) $thumb->encode(new WebpEncoder(quality: 75)));
        Storage::disk('public')->put("{$base}_thumb.jpg",  (string) $thumb->encode(new JpegEncoder(quality: 80)));

        return "{$base}.jpg";
    }

    /**
     * 店舗メイン画像を削除（JPG + WebP）
     */
    public function deleteShopMainImage(int $shopId): void
    {
        $dir = "company/{$shopId}";
        Storage::disk('public')->delete(["{$dir}/main.jpg", "{$dir}/main.webp"]);
    }

    /**
     * 求人画像を保存（JPG + WebP + サムネイル）
     * @return string  storage/app/public/ からの相対パス（image_path カラムに保存する値）
     */
    public function saveJobImage(UploadedFile $file, int $shopId, int $jobId): string
    {
        $dir  = "company/{$shopId}/jobs";
        $base = "{$dir}/{$jobId}";

        Storage::disk('public')->makeDirectory($dir);

        $img = $this->manager->decode($file->getPathname());
        Storage::disk('public')->put("{$base}.jpg",  (string) $img->encode(new JpegEncoder(quality: 85)));
        Storage::disk('public')->put("{$base}.webp", (string) $img->encode(new WebpEncoder(quality: 80)));

        // サムネイル（224×126px crop、検索結果カード用）
        $thumb = $this->manager->decode($file->getPathname())->cover(224, 126);
        Storage::disk('public')->put("{$base}_thumb.webp", (string) $thumb->encode(new WebpEncoder(quality: 75)));
        Storage::disk('public')->put("{$base}_thumb.jpg",  (string) $thumb->encode(new JpegEncoder(quality: 80)));

        return "{$base}.jpg";
    }

    /**
     * URLから求人画像を保存（JPG + WebP）
     * 取得失敗時は null を返す（呼び出し元で握り潰して継続）
     * @return string|null  storage/app/public/ からの相対パス
     */
    private function isSafeImageUrl(string $url): bool
    {
        $parsed = parse_url($url);
        if (!in_array($parsed['scheme'] ?? '', ['http', 'https'], true)) {
            return false;
        }
        $host = $parsed['host'] ?? '';
        // プライベートアドレス・ループバックへのリクエストを禁止
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false
            && filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return false;
        }
        return $host !== '' && $host !== 'localhost';
    }

    public function saveShopMainImageFromUrl(string $url, int $shopId): ?string
    {
        if (!$this->isSafeImageUrl($url)) {
            Log::warning("ImageService: 不正なURL url={$url}");
            return null;
        }
        try {
            $response = Http::timeout(15)->get($url);
            if (! $response->ok()) {
                Log::warning("ImageService: 店舗画像取得失敗 HTTP {$response->status()} url={$url}");
                return null;
            }
        } catch (\Exception $e) {
            Log::warning("ImageService: 店舗画像取得エラー url={$url} " . $e->getMessage());
            return null;
        }

        $dir  = "company/{$shopId}";
        $base = "{$dir}/main";

        Storage::disk('public')->makeDirectory($dir);

        try {
            $body = $response->body();
            $img  = $this->manager->decodeBinary($body);
            Storage::disk('public')->put("{$base}.jpg",  (string) $img->encode(new JpegEncoder(quality: 85)));
            Storage::disk('public')->put("{$base}.webp", (string) $img->encode(new WebpEncoder(quality: 80)));

            $thumb = $this->manager->decodeBinary($body)->cover(224, 126);
            Storage::disk('public')->put("{$base}_thumb.webp", (string) $thumb->encode(new WebpEncoder(quality: 75)));
            Storage::disk('public')->put("{$base}_thumb.jpg",  (string) $thumb->encode(new JpegEncoder(quality: 80)));
        } catch (\Exception $e) {
            Log::warning("ImageService: 店舗画像変換エラー url={$url} " . $e->getMessage());
            return null;
        }

        return "{$base}.jpg";
    }

    public function saveJobImageFromUrl(string $url, int $shopId, int $jobId): ?string
    {
        if (!$this->isSafeImageUrl($url)) {
            Log::warning("ImageService: 不正なURL url={$url}");
            return null;
        }
        try {
            $response = Http::timeout(15)->get($url);
            if (! $response->ok()) {
                Log::warning("ImageService: 画像取得失敗 HTTP {$response->status()} url={$url}");
                return null;
            }
        } catch (\Exception $e) {
            Log::warning("ImageService: 画像取得エラー url={$url} " . $e->getMessage());
            return null;
        }

        $dir  = "company/{$shopId}/jobs";
        $base = "{$dir}/{$jobId}";

        Storage::disk('public')->makeDirectory($dir);

        try {
            $body = $response->body();
            $img  = $this->manager->decodeBinary($body);
            Storage::disk('public')->put("{$base}.jpg",  (string) $img->encode(new JpegEncoder(quality: 85)));
            Storage::disk('public')->put("{$base}.webp", (string) $img->encode(new WebpEncoder(quality: 80)));

            $thumb = $this->manager->decodeBinary($body)->cover(224, 126);
            Storage::disk('public')->put("{$base}_thumb.webp", (string) $thumb->encode(new WebpEncoder(quality: 75)));
            Storage::disk('public')->put("{$base}_thumb.jpg",  (string) $thumb->encode(new JpegEncoder(quality: 80)));
        } catch (\Exception $e) {
            Log::warning("ImageService: 画像変換エラー url={$url} " . $e->getMessage());
            return null;
        }

        return "{$base}.jpg";
    }

    /**
     * 求人画像を削除（JPG + WebP）
     */
    public function deleteJobImage(int $shopId, int $jobId): void
    {
        $dir = "company/{$shopId}/jobs";
        Storage::disk('public')->delete(["{$dir}/{$jobId}.jpg", "{$dir}/{$jobId}.webp"]);
    }

    /**
     * WebP パスを返す（JPGパスから計算）
     */
    public static function webpPath(string $jpgPath): string
    {
        return str_replace('.jpg', '.webp', $jpgPath);
    }

    /**
     * サムネイル WebP パスを返す（JPGパスから計算）
     * ファイルが存在しない場合は通常の WebP パスにフォールバック
     */
    public static function thumbWebpPath(string $jpgPath): string
    {
        $thumb = str_replace('.jpg', '_thumb.webp', $jpgPath);
        return Storage::disk('public')->exists($thumb) ? $thumb : str_replace('.jpg', '.webp', $jpgPath);
    }

    /**
     * サムネイル JPG パスを返す（フォールバック付き）
     */
    public static function thumbJpgPath(string $jpgPath): string
    {
        $thumb = str_replace('.jpg', '_thumb.jpg', $jpgPath);
        return Storage::disk('public')->exists($thumb) ? $thumb : $jpgPath;
    }

    public function saveDiaryImage(\Illuminate\Http\UploadedFile $file, int $castId, int $diaryId, int $index): string
    {
        $dir  = "casts/{$castId}/diary";
        $base = "{$dir}/d{$diaryId}_{$index}";
        Storage::disk('public')->makeDirectory($dir);
        $img = $this->manager->decode($file->getPathname())->scaleDown(width: 900);
        Storage::disk('public')->put("{$base}.jpg",  (string) $img->encode(new JpegEncoder(quality: 85)));
        Storage::disk('public')->put("{$base}.webp", (string) $img->encode(new WebpEncoder(quality: 80)));
        return "{$base}.jpg";
    }

}
