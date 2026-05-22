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
    // 0 = バナーなし (NG)
    // 1 = delicon + up-stage 両方あり（バナープラン適格）
    // 2 = リンクあり・バナー画像切れ
    // 3 = 手動確認済み (スキップ)
    // 4 = deliconのみ（無料上位適格）
    // null = URL無しまたは未チェック

    private const BROKEN_IMG_PATTERNS = [
        'delicon.jp/img/dcbn_',
        'delicon.jp/img/banner',
    ];

    private const PLAN_DOMAINS = [
        4 => ['self'],
        3 => ['self', 'www.up-stage.info'],
    ];

    // 年齢認証ページのURLパターン（パスの末尾セグメントとして検出）
    private const AGE_GATE_SEGMENTS = [
        'ageauth', 'age_auth', 'age-auth',
        'agecheck', 'age_check', 'age-check',
        'ageconfirm', 'age_confirm', 'age-confirm',
        'agegate', 'age_gate', 'age-gate',
        'age', 'adult',
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
                $q->whereNotIn('banner_ok', [3])
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

        $ok = 0; $ng = 0; $broken = 0; $deliconOnly = 0; $both = 0;

        foreach ($shops as $shop) {
            $urlRow = $shop->externalUrls->firstWhere('url_type', 'website');
            if (!$urlRow) {
                $shop->update(['banner_ok' => null, 'banner_checked_at' => now()]);
                $bar->advance();
                continue;
            }

            if ($shop->is_banner_plan) {
                // バナープラン店舗：プランに応じた必要ドメインをチェック
                $domains  = self::PLAN_DOMAINS[$shop->plan] ?? ['self'];
                $required = array_map(fn($d) => $d === 'self' ? $mainDomain : $d, $domains);
                $result   = $this->checkBanner($urlRow->url, $required, $mainDomain);
                $shop->update(['banner_ok' => $result, 'banner_checked_at' => now()]);
                if ($result === 1) $ok++;
                elseif ($result === 2) $broken++;
                else $ng++;
            } else {
                // 非バナープラン店舗：delicon/up-stage 両方を個別に検出
                $result = $this->detectBanners($urlRow->url, $mainDomain);
                $shop->update(['banner_ok' => $result, 'banner_checked_at' => now()]);
                if ($result === 1) $both++;
                elseif ($result === 4) $deliconOnly++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("バナープラン — OK: {$ok}  画像切れ: {$broken}  NG: {$ng}");
        $this->info("非バナープラン — 両方あり(プラン3候補): {$both}件  deliconのみ(プラン4候補): {$deliconOnly}件");
    }

    // ────────────────────────────────────────────
    // 非バナープラン用：delicon と up-stage を個別検出
    // 返り値: 0=なし / 1=両方 / 4=deliconのみ
    // ────────────────────────────────────────────
    private function detectBanners(string $url, string $mainDomain, bool $isRetry = false): int
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])
            ->withOptions(['verify' => false, 'allow_redirects' => ['max' => 5]])
            ->timeout(10)
            ->get($url);

            if (!$response->successful()) return 0;

            $html = preg_replace('/<!--.*?-->/s', '', $response->body());

            // 年齢認証ページ → 本来のコンテンツページでチェック
            if (!$isRetry && $this->isAgeGatePage($url, $html)) {
                $realUrl = $this->resolveRealUrl($url, $html);
                if ($realUrl) return $this->detectBanners($realUrl, $mainDomain, true);
                return 0;
            }

            $hasDelicon  = $this->hasDomainLink($html, $mainDomain);
            $hasUpstage  = $this->hasDomainLink($html, 'www.up-stage.info');

            if ($hasDelicon && $hasUpstage) return 1;
            if ($hasDelicon) return 4;
            return 0;
        } catch (\Exception) {
            return 0;
        }
    }

    // ────────────────────────────────────────────
    // バナープラン用：必要ドメインがすべて揃っているか確認
    // 返り値: 0=NG / 1=OK / 2=画像切れ
    // ────────────────────────────────────────────
    private function checkBanner(string $url, array $requiredDomains, string $mainDomain, bool $isRetry = false): int
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])
            ->withOptions(['verify' => false, 'allow_redirects' => ['max' => 5]])
            ->timeout(10)
            ->get($url);

            if (!$response->successful()) return 0;

            $html = preg_replace('/<!--.*?-->/s', '', $response->body());

            foreach ($requiredDomains as $domain) {
                if (!$this->hasDomainLink($html, $domain)) return 0;
            }

            // 年齢認証ページ → 本来のコンテンツページでチェック
            if (!$isRetry && $this->isAgeGatePage($url, $html)) {
                $realUrl = $this->resolveRealUrl($url, $html);
                if ($realUrl) return $this->checkBanner($realUrl, $requiredDomains, $mainDomain, true);
                return 0;
            }

            foreach (self::BROKEN_IMG_PATTERNS as $pattern) {
                if (stripos($html, $pattern) !== false) {
                    preg_match('/<img\b[^>]*\bsrc=[\"\']([^\"\']*)' . preg_quote($pattern, '/') . '[^\"\']*[\"\'][^>]*>/i', $html, $im);
                    $imgUrl = $im[1] ?? null;
                    if (!$imgUrl) return 2;
                    try {
                        $imgRes = Http::withOptions(['verify' => false, 'allow_redirects' => ['max' => 3]])
                            ->timeout(5)->head($imgUrl);
                        if (!$imgRes->successful()) return 2;
                    } catch (\Exception) {
                        return 2;
                    }
                }
            }

            return 1;
        } catch (\Exception) {
            return 0;
        }
    }

    private function hasDomainLink(string $html, string $domain): bool
    {
        preg_match_all('/<a\s[^>]*href=["\'][^"\']*' . preg_quote($domain, '/') . '[^"\']*["\'][^>]*>/i', $html, $matches);
        foreach ($matches[0] as $tag) {
            if (!preg_match('/rel=["\'][^"\']*nofollow/i', $tag)) {
                return true;
            }
        }
        return false;
    }

    private function isAgeGatePage(string $url, string $html): bool
    {
        $path    = rtrim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        $segment = mb_strtolower(basename($path));
        if (in_array($segment, self::AGE_GATE_SEGMENTS, true)) {
            return true;
        }
        if (preg_match('/<title[^>]*>[^<]*(?:年齢認証|年齢確認|年齢チェック)[^<]*<\/title>/iu', $html)) {
            return true;
        }
        return false;
    }

    private function resolveRealUrl(string $url, string $html): ?string
    {
        if (preg_match('/<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']+)["\'][^>]*>/i', $html, $m) ||
            preg_match('/<link[^>]+href=["\']([^"\']+)["\'][^>]+rel=["\']canonical["\'][^>]*>/i', $html, $m)) {
            $canonical = rtrim($m[1], '/');
            if ($canonical !== rtrim($url, '/')) {
                return $canonical . '/';
            }
        }
        $parts      = parse_url($url);
        $path       = rtrim($parts['path'] ?? '/', '/');
        $parentPath = dirname($path);
        if ($parentPath === '.' || $parentPath === $path) return null;
        return ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '') . rtrim($parentPath, '/') . '/';
    }
}
