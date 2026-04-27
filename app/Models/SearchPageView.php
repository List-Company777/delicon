<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchPageView extends Model
{
    public $timestamps = false;

    protected $fillable = ['gender', 'area_slug', 'job_slug', 'date', 'count'];

    protected $casts = ['date' => 'date'];

    public static function record(string $gender, string $area_slug, string $job_slug): void
    {
        if (self::isBot(request()->userAgent() ?? '')) {
            return;
        }

        static::upsert(
            [['gender' => $gender, 'area_slug' => $area_slug, 'job_slug' => $job_slug, 'date' => today()->toDateString(), 'count' => 1]],
            ['gender', 'area_slug', 'job_slug', 'date'],
            ['count' => \DB::raw('count + 1')]
        );
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
