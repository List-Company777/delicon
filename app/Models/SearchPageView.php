<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchPageView extends Model
{
    public $timestamps = false;

    protected $fillable = ['gender', 'area_slug', 'job_slug', 'source', 'date', 'count'];

    protected $casts = ['date' => 'date'];

    public static function record(string $gender, string $area_slug, string $job_slug): void
    {
        if (self::isBot(request()->userAgent() ?? '')) {
            return;
        }

        $source = self::resolveSource(request()->header('referer') ?? '');

        static::upsert(
            [['gender' => $gender, 'area_slug' => $area_slug, 'job_slug' => $job_slug, 'source' => $source, 'date' => today()->toDateString(), 'count' => 1]],
            ['gender', 'area_slug', 'job_slug', 'source', 'date'],
            ['count' => \DB::raw('count + 1')]
        );
    }

    private static function resolveSource(string $referrer): string
    {
        if ($referrer === '') {
            return 'direct';
        }

        $host = strtolower(parse_url($referrer, PHP_URL_HOST) ?? '');

        if ($host === '') {
            return 'direct';
        }

        // 自サイト内遷移
        $appHost = strtolower(parse_url(config('app.url'), PHP_URL_HOST) ?? '');
        if ($host === $appHost || str_ends_with($host, '.' . $appHost)) {
            return 'internal';
        }

        // 主要検索エンジン
        if (preg_match('/google\.|bing\.com|yahoo\.|duckduckgo\.com|baidu\.com|yandex\.|naver\.com|daum\.net/', $host)) {
            return 'organic';
        }

        return 'other';
    }

    private static function isBot(string $ua): bool
    {
        if ($ua === '') {
            return true;
        }

        return (bool) preg_match(
            '/bot|crawl|spider|slurp|mediapartners|facebookexternalhit|Twitterbot|LinkedInBot|Slack|Discordbot|WhatsApp|TelegramBot|DuckDuckBot|YandexBot|Baiduspider|Sogou|Exabot|AhrefsBot|SemrushBot|MJ12bot|DotBot|BLEXBot|PetalBot/i',
            $ua
        );
    }
}
