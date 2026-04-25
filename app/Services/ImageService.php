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
     * 店舗メイン画像を保存（JPG + WebP）
     * @return string  storage/app/public/ からの相対パス（main_image カラムに保存する値）
     */
    public function saveShopMainImage(UploadedFile $file, int $shopId): string
    {
        $dir  = "company/{$shopId}";
        $base = "{$dir}/main";

        Storage::disk('public')->makeDirectory($dir);

        $jpgBytes  = $this->manager->decode($file->getPathname())->encode(new JpegEncoder(quality: 85));
        $webpBytes = $this->manager->decode($file->getPathname())->encode(new WebpEncoder(quality: 80));

        Storage::disk('public')->put("{$base}.jpg",  (string) $jpgBytes);
        Storage::disk('public')->put("{$base}.webp", (string) $webpBytes);

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
     * 求人画像を保存（JPG + WebP）
     * @return string  storage/app/public/ からの相対パス（image_path カラムに保存する値）
     */
    public function saveJobImage(UploadedFile $file, int $shopId, int $jobId): string
    {
        $dir  = "company/{$shopId}/jobs";
        $base = "{$dir}/{$jobId}";

        Storage::disk('public')->makeDirectory($dir);

        $jpgBytes  = $this->manager->decode($file->getPathname())->encode(new JpegEncoder(quality: 85));
        $webpBytes = $this->manager->decode($file->getPathname())->encode(new WebpEncoder(quality: 80));

        Storage::disk('public')->put("{$base}.jpg",  (string) $jpgBytes);
        Storage::disk('public')->put("{$base}.webp", (string) $webpBytes);

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
            $jpgBytes  = $this->manager->decodeBinary($response->body())->encode(new JpegEncoder(quality: 85));
            $webpBytes = $this->manager->decodeBinary($response->body())->encode(new WebpEncoder(quality: 80));

            Storage::disk('public')->put("{$base}.jpg",  (string) $jpgBytes);
            Storage::disk('public')->put("{$base}.webp", (string) $webpBytes);
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
            $jpgBytes  = $this->manager->decodeBinary($response->body())->encode(new JpegEncoder(quality: 85));
            $webpBytes = $this->manager->decodeBinary($response->body())->encode(new WebpEncoder(quality: 80));

            Storage::disk('public')->put("{$base}.jpg",  (string) $jpgBytes);
            Storage::disk('public')->put("{$base}.webp", (string) $webpBytes);
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
}
