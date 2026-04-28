<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\ArticleGenerationPrompt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RegenerateArticles extends Command
{
    protected $signature = 'articles:regenerate
                            {--gender= : female/male/business/shop（指定時はそのgenderのみ）}
                            {--dry-run : DBに保存せず内容を表示するだけ}
                            {--limit= : 処理件数の上限（省略時は全件）}
                            {--max-chars= : 本文（タグ除去後）がこの文字数以上の記事はスキップ}';

    protected $description = '下書き記事（is_published=false）をSonnetで再生成して上書きする';

    public function handle(): int
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            $this->error('ANTHROPIC_API_KEY が設定されていません。');
            return self::FAILURE;
        }

        $query = Article::where('is_published', false);
        if ($gender = $this->option('gender')) {
            $query->where('gender', $gender);
        }
        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $articles = $query->orderBy('id')->get();
        $this->info("対象: {$articles->count()} 件");

        $maxChars = $this->option('max-chars') !== null ? (int) $this->option('max-chars') : null;

        $updated = 0;
        foreach ($articles as $i => $article) {
            $num = $i + 1;
            $this->info("[{$num}/{$articles->count()}] {$article->title} ({$article->gender})");

            if ($maxChars !== null && mb_strlen(strip_tags($article->body ?? '')) >= $maxChars) {
                $this->line("  スキップ（{$maxChars}文字以上）");
                continue;
            }

            $text = $this->callClaude($apiKey, $this->buildPrompt($article->title, $article->gender));
            if (!$text) {
                $this->error("  生成失敗 — スキップ");
                continue;
            }

            ['title' => $title, 'lead' => $lead, 'body' => $body, 'faq' => $faq] = $this->parseArticle($text, $article->title);
            if (!$body) {
                $this->error("  パース失敗 — スキップ");
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("  TITLE: {$title}");
                $this->line("  LEAD:  " . mb_substr($lead, 0, 80) . '...');
                $this->line("  BODY:  " . mb_substr(strip_tags($body), 0, 200) . '...');
                $this->line("  FAQ:   " . ($faq ? count($faq) . '件' : 'なし'));
                $this->line('');
                continue;
            }

            $article->update(['title' => $title, 'lead' => $lead, 'body' => $body, 'faq' => $faq]);
            $this->info("  更新: {$article->slug}");
            $updated++;
        }

        if (!$this->option('dry-run')) {
            $this->info("完了: {$updated} 件を更新しました。");
        }

        return self::SUCCESS;
    }

    private function buildPrompt(string $topic, string $gender): string
    {
        $dbPrompt = ArticleGenerationPrompt::where('gender', $gender)->value('instruction');

        $genderNote = $dbPrompt ?: match ($gender) {
            'female'   => '女性向けナイトワーク（キャスト・ガールズバー等）',
            'male'     => '男性向けナイトワーク（ホスト・黒服・ボーイ等）',
            'yoasobi' => '夜遊び・ナイトクラブ・キャバクラ情報（お客様向け）',
            'shop'     => '夜遊び業態の店舗オーナー・経営者向け',
            default    => 'ナイトワーク全般',
        };

        $currentYear = now()->year;

        return <<<PROMPT
あなたはナイトワーク・夜遊び情報サイト「ナイトワークリスト」の編集者です。
以下のテーマでSEOを意識した高品質なコラム記事を日本語で書いてください。

テーマ: {$topic}
対象読者: {$genderNote}
現在の年: {$currentYear}年

## 品質要件（必ず守ること）
- タイトルに具体的な数字・年・地名を入れてクリック率を上げる（例: 「{$currentYear}年最新」「東京・大阪」など）
- 年度を使う場合は必ず {$currentYear} 年を基準にすること（過去の年を使わない）
- リード文は読者の悩みに直接刺さる一文から始め、100〜200文字で記事全体を要約する
- h2を3〜5個、各h2の下にh3を1〜2個設ける
- 各節は最低300文字以上（1〜2行で終わる薄い節は禁止）
- 時給・月収・勤務時間などは「〜が多い」と濁さず具体的な数値範囲で記載する
- pタグで段落、ul/olタグでリストを積極活用する
- 本文（タグ除去後）は2000〜4000文字
- 最後に「まとめ」のh2で締める
- 風俗・性的なサービスには一切触れない（健全な求人・夜遊び情報のみ）
- 本文の最後に読者がよく疑問に思う3〜5件のQ&Aを作成する

## 出力形式（厳守）
以下のタグ形式のみ出力すること。タグの外に説明文を書かないこと。

<SLUG>英数字とハイフンのみのURL用スラッグ（例：cabakura-hourly-wage-guide）</SLUG>
<TITLE>記事タイトル（30〜60文字）</TITLE>
<LEAD>リード文（100〜200文字）</LEAD>
<BODY>
本文HTML（h2・h3・p・ul・ol タグのみ使用。HTMLの属性値はダブルクォートを使ってよい）
</BODY>
<FAQ>
[{"q":"質問1","a":"回答1"},{"q":"質問2","a":"回答2"},{"q":"質問3","a":"回答3"}]
</FAQ>
PROMPT;
    }

    private function callClaude(string $apiKey, string $prompt): ?string
    {
        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(120)->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-sonnet-4-6',
                'max_tokens' => 8192,
                'messages'   => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            if (!$response->successful()) {
                Log::error('Claude API error: ' . $response->body());
                return null;
            }

            return $response->json('content.0.text');
        } catch (\Exception $e) {
            Log::error('Claude API exception: ' . $e->getMessage());
            return null;
        }
    }

    private function parseArticle(string $text, string $fallbackTitle): array
    {
        $title = $this->extractTag($text, 'TITLE');
        $lead  = $this->extractTag($text, 'LEAD');
        $body  = $this->extractTag($text, 'BODY');

        $faqRaw = $this->extractTag($text, 'FAQ');
        $faq    = null;
        if ($faqRaw) {
            $decoded = json_decode($faqRaw, true);
            $faq = is_array($decoded) ? $decoded : null;
        }

        if ($title && $body) {
            return ['title' => $title, 'lead' => $lead, 'body' => $body, 'faq' => $faq];
        }

        Log::warning('RegenerateArticles parseArticle failed | ' . mb_substr($text, 0, 500));
        return ['title' => $fallbackTitle, 'lead' => '', 'body' => '', 'faq' => null];
    }

    private function extractTag(string $text, string $tag): string
    {
        if (preg_match('/<' . $tag . '>([\s\S]+?)<\/' . $tag . '>/i', $text, $m)) {
            return trim($m[1]);
        }
        return '';
    }
}
