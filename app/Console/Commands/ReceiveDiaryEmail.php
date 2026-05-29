<?php

namespace App\Console\Commands;

use App\Models\CastDiary;
use App\Models\CastDiaryImage;
use App\Models\CastDiaryToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReceiveDiaryEmail extends Command
{
    protected $signature   = 'receive:diary-email';
    protected $description = 'メール受信で写メ日記を自動投稿する（Postfix pipe用）';

    private const DIARY_DOMAIN = 'd.delicon.jp';

    public function handle(): int
    {
        try {
            $raw = file_get_contents('php://stdin');
            if (empty($raw)) {
                Log::warning('ReceiveDiaryEmail: empty input');
                return 0;
            }

            // ヘッダーとボディを分離
            [$headerStr, $body] = array_pad(preg_split('/\r?\n\r?\n/', $raw, 2), 2, '');

            // To: からトークンを抽出
            preg_match('/^To:.*?diary-([a-zA-Z0-9]+)@' . preg_quote(self::DIARY_DOMAIN, '/') . '/im', $headerStr, $tm);
            $token = $tm[1] ?? null;

            if (!$token) {
                Log::warning('ReceiveDiaryEmail: token not found', ['header' => mb_substr($headerStr, 0, 300)]);
                return 0;
            }

            $diaryToken = CastDiaryToken::with('cast')
                ->where('token', $token)
                ->where('is_email_token', 1)
                ->first();

            if (!$diaryToken) {
                Log::warning('ReceiveDiaryEmail: token not registered', ['token' => $token]);
                return 0;
            }

            $cast = $diaryToken->cast;

            // Subject → タイトル
            preg_match('/^Subject:\s*(.+?)$/im', $headerStr, $sm);
            $title = isset($sm[1]) ? mb_decode_mimeheader(trim($sm[1])) : null;

            // Content-Type
            preg_match('/^Content-Type:\s*([^\r\n;]+)/im', $headerStr, $ctm);
            $contentType = strtolower(trim($ctm[1] ?? 'text/plain'));

            $bodyText = null;
            $images   = [];

            if (str_contains($contentType, 'multipart')) {
                preg_match('/boundary="?([^"\r\n;]+)"?/i', $headerStr, $bm);
                if (isset($bm[1])) {
                    [$bodyText, $images] = $this->parseMultipart($body, trim($bm[1]));
                }
            } else {
                preg_match('/^Content-Transfer-Encoding:\s*(.+?)$/im', $headerStr, $ctem);
                $bodyText = $this->decodeContent($body, strtolower(trim($ctem[1] ?? '')));
                $bodyText = $this->convertCharset($bodyText, $headerStr);
            }

            $diary = CastDiary::create([
                'cast_id' => $cast->id,
                'title'   => $title ?: null,
                'body'    => $bodyText ? trim(strip_tags($bodyText)) : null,
                'status'  => 'published',
            ]);

            foreach ($images as $i => [$imgData, $ext]) {
                $path = 'diaries/' . $cast->id . '/' . $diary->id . '_' . $i . '.' . $ext;
                Storage::disk('public')->put($path, $imgData);

                // webp生成
                try {
                    $fullPath = Storage::disk('public')->path($path);
                    $img = match($ext) {
                        'jpg'  => @imagecreatefromjpeg($fullPath),
                        'png'  => @imagecreatefrompng($fullPath),
                        'gif'  => @imagecreatefromgif($fullPath),
                        default => null,
                    };
                    if ($img) {
                        $webpPath = Storage::disk('public')->path(
                            'diaries/' . $cast->id . '/' . $diary->id . '_' . $i . '.webp'
                        );
                        imagewebp($img, $webpPath, 80);
                        imagedestroy($img);
                    }
                } catch (\Throwable $e) {}

                CastDiaryImage::create([
                    'diary_id'   => $diary->id,
                    'img_path'   => $path,
                    'sort_order' => $i,
                    'created_at' => now(),
                ]);
            }

            Log::info('ReceiveDiaryEmail: 投稿完了', [
                'cast_id'  => $cast->id,
                'diary_id' => $diary->id,
                'images'   => count($images),
                'title'    => $title,
            ]);

        } catch (\Throwable $e) {
            Log::error('ReceiveDiaryEmail: 例外', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        return 0;
    }

    private function parseMultipart(string $body, string $boundary): array
    {
        $bodyText = null;
        $images   = [];

        $delimiter = '--' . $boundary;
        $parts = explode($delimiter, $body);

        foreach ($parts as $part) {
            $part = ltrim($part, "\r\n");
            if (!$part || $part === '--' || $part === "--\r\n") continue;

            [$partHeaders, $partContent] = array_pad(preg_split('/\r?\n\r?\n/', $part, 2), 2, '');

            preg_match('/^Content-Type:\s*([^\r\n;]+)/im', $partHeaders, $ctm);
            $ct = strtolower(trim($ctm[1] ?? ''));

            preg_match('/^Content-Transfer-Encoding:\s*(.+?)$/im', $partHeaders, $ctem);
            $enc = strtolower(trim($ctem[1] ?? ''));

            if (str_contains($ct, 'multipart')) {
                // ネストしたmultipart
                preg_match('/boundary="?([^"\r\n;]+)"?/i', $partHeaders, $bm);
                if (isset($bm[1])) {
                    [$nestedText, $nestedImages] = $this->parseMultipart($partContent, trim($bm[1]));
                    $bodyText ??= $nestedText;
                    $images = array_merge($images, $nestedImages);
                }
            } elseif (str_contains($ct, 'text/plain') && $bodyText === null) {
                $decoded  = $this->decodeContent($partContent, $enc);
                $bodyText = $this->convertCharset($decoded, $partHeaders);
            } elseif (preg_match('/^image\/(jpeg|jpg|png|gif|webp)/i', $ct, $extm)) {
                $ext     = strtolower($extm[1]) === 'jpeg' ? 'jpg' : strtolower($extm[1]);
                $imgData = $this->decodeContent($partContent, $enc);
                if ($imgData && strlen($imgData) > 100) {
                    $images[] = [$imgData, $ext];
                }
            }
        }

        return [$bodyText, $images];
    }

    private function decodeContent(string $content, string $encoding): string
    {
        $content = rtrim($content, "\r\n-");
        return match ($encoding) {
            'base64'           => (string) base64_decode(preg_replace('/\s+/', '', $content)),
            'quoted-printable' => quoted_printable_decode($content),
            default            => $content,
        };
    }

    private function convertCharset(string $text, string $headers): string
    {
        preg_match('/charset="?([^"\r\n;]+)"?/i', $headers, $csm);
        $charset = strtolower(trim($csm[1] ?? 'utf-8'));
        if ($charset !== 'utf-8' && $charset !== 'utf8') {
            $text = mb_convert_encoding($text, 'UTF-8', strtoupper($charset));
        }
        return $text;
    }
}
