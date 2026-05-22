<?php
namespace App\Console\Commands;

use App\Models\ShopExternalUrl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckShopUrls extends Command
{
    protected $signature   = 'shops:check-urls {--limit=0 : 件数制限 (0=全件)}';
    protected $description = '店舗公式URLの死活チェック';

    // /top /index.html など、トップページとみなしてルートURLに正規化するパス
    private const TOP_PATHS = ['/top', '/index.html', '/index.htm', '/index.php', '/home'];

    public function handle(): void
    {
        $query = ShopExternalUrl::where('url_type', 'website')
            ->whereHas('shop', fn($q) => $q->where('status', '!=', 'inactive'))
            ->orderBy('url_checked_at');

        $limit = (int) $this->option('limit');
        if ($limit > 0) $query->limit($limit);

        $rows  = $query->get();
        $total = $rows->count();
        $this->info("チェック対象: {$total}件");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($rows as $row) {
            $url    = $this->normalizeUrl($row->url);
            $status = $this->checkUrl($url);
            $row->update(['url_status' => $status, 'url_checked_at' => now()]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $errors = ShopExternalUrl::where('url_type', 'website')
            ->whereNotNull('url_status')
            ->where(function ($q) {
                $q->where('url_status', 0)
                  ->orWhere('url_status', '>=', 400);
            })->count();

        $this->info("完了。エラー検出: {$errors}件");
    }

    private function normalizeUrl(string $url): string
    {
        $parsed = parse_url($url);
        if (!$parsed) return $url;

        $path = $parsed['path'] ?? '/';

        foreach (self::TOP_PATHS as $topPath) {
            if (rtrim($path, '/') === $topPath || $path === $topPath . '/') {
                // /top → ルートURLに置き換え
                $scheme = $parsed['scheme'] ?? 'https';
                $host   = $parsed['host'] ?? '';
                $port   = isset($parsed['port']) ? ':' . $parsed['port'] : '';
                return "{$scheme}://{$host}{$port}/";
            }
        }

        return $url;
    }

    private function checkUrl(string $url): int
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])
            ->withOptions(['verify' => false, 'allow_redirects' => ['max' => 5]])
            ->timeout(10)
            ->get($url);

            return $response->status();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
