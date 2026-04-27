<?php

namespace App\Jobs;

use App\Models\ArticleVideo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateArticleVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries   = 1;

    public function __construct(private ArticleVideo $video) {}

    public function handle(): void
    {
        try {
            $this->video->update(['status' => 'processing']);

            $article = $this->video->article;

            // 1. GPT-4o-miniで記事を200〜270字に要約（約45秒想定）
            $script = $this->summarize($article->title, $article->body ?? $article->lead ?? $article->title);
            $this->video->update(['script' => $script]);

            // 2. SNS投稿文生成
            $caption = $this->generateSnsCaption($article->title, $script);
            $this->video->update(['sns_caption' => $caption]);

            // 4. HeyGen動画生成リクエスト
            $videoId = $this->createHeygenVideo($script);
            $this->video->update(['video_job_id' => $videoId]);

            // 5. HeyGen完了待ち（最大8分）
            $videoUrl = $this->waitForHeygen($videoId);

            // 6. 動画ダウンロード保存
            $videoPath = $this->downloadVideo($videoUrl, $article->id);
            $this->video->update(['status' => 'done', 'video_path' => $videoPath]);

        } catch (\Throwable $e) {
            Log::error('ArticleVideo generation failed', ['id' => $this->video->id, 'error' => $e->getMessage()]);
            $this->video->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }
    }

    private function summarize(string $title, string $body): string
    {
        $text = mb_substr(strip_tags($body), 0, 3000);

        $res = Http::withToken(config('services.openai.key'))
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => '記事の内容を200〜270文字の日本語で要約してください。ナレーション読み上げ用なので、体言止めや箇条書きは使わず、自然な話し言葉にしてください。'],
                    ['role' => 'user',   'content' => "タイトル：{$title}\n\n{$text}"],
                ],
                'max_tokens' => 400,
                'temperature' => 0.7,
            ]);

        if ($res->failed()) {
            throw new \RuntimeException('OpenAI summary failed: ' . $res->body());
        }

        return trim($res->json('choices.0.message.content'));
    }

    private function generateSnsCaption(string $title, string $script): string
    {
        $res = Http::withToken(config('services.openai.key'))
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => <<<PROMPT
SNS（Instagram・TikTok・YouTube Shorts）用の投稿文を作成してください。

【形式】
1行目：動画の最後まで見たくなるような、興味を引くキャプション（30〜50字）。「実は〜」「知らないと損する〜」「〜の真実」など好奇心を刺激する表現を使うこと。体言止めOK。
2行目：空行
3行目：#ナイトワーク #夜職 に続けて、記事テーマに合った効果的なハッシュタグを2つ追加（合計4タグ）。

出力は上記3行のみ。説明文不要。
PROMPT],
                    ['role' => 'user', 'content' => "記事タイトル：{$title}\n\nナレーション：{$script}"],
                ],
                'max_tokens' => 200,
                'temperature' => 0.8,
            ]);

        if ($res->failed()) {
            throw new \RuntimeException('OpenAI caption failed: ' . $res->body());
        }

        return trim($res->json('choices.0.message.content'));
    }

    private function createHeygenVideo(string $script): string
    {
        $apiKey     = config('services.heygen.key');
        $avatarId   = config('services.heygen.avatar_id');
        $avatarType = config('services.heygen.avatar_type');
        $voiceId    = config('services.heygen.voice_id');

        $character = match ($avatarType) {
            'avatar' => [
                'type'      => 'avatar',
                'avatar_id' => $avatarId,
            ],
            default => [
                'type'             => 'talking_photo',
                'talking_photo_id' => $avatarId,
            ],
        };

        $res = Http::withHeaders([
                'X-Api-Key'    => $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(60)
            ->post('https://api.heygen.com/v2/video/generate', [
                'video_inputs' => [
                    [
                        'character'  => $character,
                        'voice'      => [
                            'type'       => 'text',
                            'input_text' => $script,
                            'voice_id'   => $voiceId,
                        ],
                        'background' => [
                            'type'  => 'color',
                            'value' => '#1a1a2e',
                        ],
                    ],
                ],
                'aspect_ratio' => '9:16',
                'test'         => false,
            ]);

        if ($res->failed()) {
            throw new \RuntimeException('HeyGen create video failed: ' . $res->body());
        }

        $videoId = $res->json('data.video_id');
        if (empty($videoId)) {
            throw new \RuntimeException('HeyGen response missing video_id: ' . $res->body());
        }

        return $videoId;
    }

    private function waitForHeygen(string $videoId): string
    {
        $apiKey  = config('services.heygen.key');
        $timeout = 480;
        $waited  = 0;

        while ($waited < $timeout) {
            sleep(10);
            $waited += 10;

            $res = Http::withHeaders(['X-Api-Key' => $apiKey])
                ->timeout(30)
                ->get('https://api.heygen.com/v1/video_status.get', ['video_id' => $videoId]);

            if ($res->failed()) continue;

            $status = $res->json('data.status');

            if ($status === 'completed') {
                return $res->json('data.video_url');
            }

            if ($status === 'failed') {
                throw new \RuntimeException('HeyGen video failed: ' . json_encode($res->json('data.error')));
            }
        }

        throw new \RuntimeException('HeyGen video timeout after ' . $timeout . 's');
    }

    private function downloadVideo(string $url, int $articleId): string
    {
        $content  = Http::timeout(120)->get($url)->body();
        $filename = 'article-videos/' . $articleId . '_' . Str::uuid() . '.mp4';
        Storage::disk('public')->put($filename, $content);

        return $filename;
    }
}
