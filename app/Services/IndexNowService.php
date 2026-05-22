<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class IndexNowService
{
    public static function ping(array|string $urls): void
    {
        $key = config('services.indexnow.key');
        if (!$key) return;

        $urls = array_values(array_filter((array) $urls));
        if (empty($urls)) return;

        try {
            Http::timeout(5)->post('https://api.indexnow.org/indexnow', [
                'host'    => parse_url(config('app.url'), PHP_URL_HOST),
                'key'     => $key,
                'urlList' => $urls,
            ]);
        } catch (\Exception) {
            // サイレント失敗（本体処理に影響させない）
        }
    }
}
