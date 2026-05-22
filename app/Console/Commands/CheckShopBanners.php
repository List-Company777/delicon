<?php
namespace App\Console\Commands;

use App\Models\Shop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckShopBanners extends Command
{
    protected $signature   = 'shops:check-banners {--banner-only : バナープラン店舗のみチェック}';
    protected $description = 'バナーリンク設置状況を全店舗チェック（逆漏れ・画像切れ検出含む）';

    // banner_ok 値:
    // 0 = リンクなし (NG)
    // 1 = 自動チェックOK
    // 2 = リンクあり・バナー画像切れ
    // 3 = 手動確認済み (スキップ)
    // null = URL無しまたは未チェック

    private const BROKEN_IMG_PATTERNS = [
        'delicon.jp/img/dcbn_',
        'delicon.jp/img/banner',
    ];

    private const PLAN_DOMAINS = [
        4 => ['self'],
        3 => ['self', 'www.up-stage.info'],
    ];

    public function handle(): void
    {
        $mainDomain = parse_url(config('app.url'), PHP_URL_HOST);
        $bannerOnly = $this->option('banner-only');

        $sixMonthsAgo = now()->subMonths(6);

        $query = Shop::where('status', '!=', 'inactive')
            ->with('externalUrls')
            ->whereHas('externalUrls', fn($q) => $q->where('url_type', 'website'))
            ->where(function ($q) use ($sixMonthsAgo) {
                // 手動確認済み(3)は6ヶ月経過したもののみ再チェック対象
                $q->where('banner_ok', '!=', 3)
                  ->orWhereNull('banner_ok')
                  ->orWhere(function ($q2) use ($sixMonthsAgo) {
                      $q2->where('banner_ok', 3)
                         ->where('banner_checked_at', '<', $sixMonthsAgo);
                  });
            });

        if ($bannerOnly) {
            $query->where('is_banner_plan', 1);
        }

        $shops = $query->get();
        $total = $shops->count();
        $this->info("チェック対象: {$total}件（手動確認済みはスキップ） / ドメイン: {$mainDomain}");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $ok = 0; $ng = 0; $broken = 0; $found = 0;

        foreach ($shops as $shop) {
            $urlRow = $shop->externalUrls->firstWhere('url_type', 'website');
            if (!$urlRow) {
                $shop->update(['banner_ok' => null, 'banner_checked_at' => now()]);
                $bar->advance();
                continue;
            }

            if ($shop->is_banner_plan) {
                $domains  = self::PLAN_DOMAINS[$shop->plan] ?? ['self'];
                $required = array_map(fn($d) => $d === 'self' ? $mainDomain : $d, $domains);
                $result   = $this->checkBanner($urlRow->url, $required, $mainDomain);
                $shop->update(['banner_ok' => $result, 'banner_checked_at' => now()]);
                if ($result === 1) $ok++;
                elseif ($result === 2) $broken++;
                else $ng++;
            } else {
                $result = $this->checkBanner($urlRow->url, [$mainDomain], $mainDomain);
                $shop->update(['banner_ok' => $result, 'banner_checked_at' => now()]);
                if ($result >= 1) $found++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("バナープラン — OK: {$ok}  画像切れ: {$broken}  NG: {$ng}");
        if (!$bannerOnly) {
            $this->info("非バナープランでリンクあり（未適用）: {$found}件");
        }
    }

    private function checkBanner(string $url, array $requiredDomains, string $mainDomain): int
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])
            ->withOptions(['verify' => false, 'allow_redirects' => ['max' => 5]])
            ->timeout(10)
            ->get($url);

            if (!$response->successful()) return 0;

            $html = $response->body();

            foreach ($requiredDomains as $domain) {
                preg_match_all('/<a\s[^>]*href=["\'][^"\']*' . preg_quote($domain, '/') . '[^"\']*["\'][^>]*>/i', $html, $matches);
                $domainFound = false;
                foreach ($matches[0] as $tag) {
                    if (!preg_match('/rel=["\'][^"\']*nofollow/i', $tag)) {
                        $domainFound = true;
                        break;
                    }
                }
                if (!$domainFound) return 0;
            }

            foreach (self::BROKEN_IMG_PATTERNS as $pattern) {
                if (stripos($html, $pattern) !== false) return 2;
            }

            return 1;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
