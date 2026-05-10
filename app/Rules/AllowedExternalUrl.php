<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllowedExternalUrl implements ValidationRule
{
    // 短縮URLサービス
    private static array $shorteners = [
        'bit.ly', 'tinyurl.com', 't.co', 'ow.ly', 'is.gd', 'buff.ly',
        'goo.gl', 'short.io', 'rb.gy', 'tiny.cc', 'shorte.st', 'adf.ly',
        'lnkd.in', 'dl.v.gd', 'cutt.ly', 'trib.al', 'ift.tt',
    ];

    // 他社媒体ドメイン（競合ポータルサイト）
    private static array $competitors = [
        'cityheaven.net',
        'dto.jp',
        'fuzoku.jp',
        'purelovers.com',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $host = parse_url($value, PHP_URL_HOST);
        if (! $host) {
            $fail('有効なURLを入力してください。');
            return;
        }

        $host = strtolower(ltrim($host, 'www.'));

        foreach (self::$shorteners as $domain) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                $fail('短縮URLは登録できません。正式なURLを入力してください。');
                return;
            }
        }

        foreach (self::$competitors as $domain) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                $fail('他社媒体サイトのURLは登録できません。');
                return;
            }
        }
    }
}
