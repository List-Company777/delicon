<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\ArticleGenerationPrompt;
use App\Models\ArticleTopic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class GenerateArticle extends Command
{
    protected $signature = 'articles:generate
                            {--topic= : 記事テーマを直接指定（1件のみ）}
                            {--gender=shop : female/male/business/shop}
                            {--count=5 : 生成する記事数}
                            {--dry-run : DBに保存せず内容を表示するだけ}';

    protected $description = 'Claude APIで記事を自動生成してDraftとして保存する';

    private const TOPICS = [
        ['title' => 'キャバクラの時給相場と稼げるエリアを徹底解説',               'gender' => 'female'],
        ['title' => 'ガールズバーとキャバクラの違い：初心者向け完全ガイド',        'gender' => 'female'],
        ['title' => 'ナイトワーク初心者向け：面接で聞かれること対策',              'gender' => 'female'],
        ['title' => '日払い求人の仕組みと注意点',                                  'gender' => 'female'],
        ['title' => 'キャバクラ面接の服装・持ち物・当日の流れを完全解説',          'gender' => 'female'],
        ['title' => '未経験からキャバクラ勤務：最初の1ヶ月で押さえるべきこと',     'gender' => 'female'],
        ['title' => 'ホストクラブの黒服・ボーイ求人の給与と仕事内容',              'gender' => 'male'],
        ['title' => 'ホスト求人の選び方：月給制と歩合制の違い',                    'gender' => 'male'],
        ['title' => 'ホストクラブ未経験者が最初に選ぶべき店舗の特徴',              'gender' => 'male'],
        ['title' => '新宿歌舞伎町のキャバクラ・夜遊びスポット完全ガイド',          'gender' => 'yoasobi'],
        ['title' => '初めての夜遊び：キャバクラの料金と楽しみ方',                  'gender' => 'yoasobi'],
        ['title' => 'ラウンジとクラブの違い：夜遊びビギナー向け解説',              'gender' => 'yoasobi'],
        ['title' => '池袋のキャバクラ・ガールズバー：エリア別おすすめと相場',      'gender' => 'yoasobi'],
        ['title' => '渋谷・恵比寿のナイトクラブ・ラウンジガイド',                  'gender' => 'yoasobi'],
        ['title' => 'キャバクラ・ラウンジの採用面接で即戦力を見極める方法',          'gender' => 'shop'],
        ['title' => 'ナイトワーク店舗のSNS集客：Instagram・TikTok活用術',          'gender' => 'shop'],
        ['title' => 'スタッフの定着率を上げるための労働環境改善ポイント',           'gender' => 'shop'],
        ['title' => '夜の店舗経営の確定申告：経費計上と節税の基本',                'gender' => 'shop'],
        ['title' => 'キャバクラ開業に必要な許可・届け出と初期費用の目安',           'gender' => 'shop'],
        ['title' => '売上を伸ばす指名制度の設計とインセンティブ設定',               'gender' => 'shop'],
    ];

    public function handle(): int
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            $this->error('ANTHROPIC_API_KEY が設定されていません。');
            return self::FAILURE;
        }

        $topicOpt = $this->option('topic');
        $count    = $topicOpt ? 1 : (int) $this->option('count');
        $saved    = 0;

        $usedThisRun = [];
        for ($i = 0; $i < $count; $i++) {
            if ($topicOpt) {
                [$topicTitle, $gender, $topicId] = [$topicOpt, $this->option('gender'), null];
            } else {
                [$topicTitle, $gender, $topicId] = $this->resolveTopic($usedThisRun);
                if (!$topicTitle) {
                    $this->warn('作成予定テーマがありません。処理を終了します。');
                    break;
                }
            }
            $usedThisRun[] = $topicTitle;

            $this->info('[' . ($i + 1) . "/{$count}] テーマ: {$topicTitle} ({$gender})");

            $text = $this->callClaude($apiKey, $this->buildPrompt($topicTitle, $gender));
            if (!$text) {
                $this->error("  生成失敗 — スキップ");
                continue;
            }

            ['slug' => $slugHint, 'title' => $title, 'lead' => $lead, 'body' => $body, 'faq' => $faq] = $this->parseArticle($text, $topicTitle);

            if ($this->option('dry-run')) {
                $this->line("  TITLE: {$title}");
                $this->line("  LEAD:  " . mb_substr($lead, 0, 80) . '...');
                $this->line("  BODY:  " . mb_substr(strip_tags($body), 0, 200) . '...');
                $this->line('');
                continue;
            }

            $slug = $this->uniqueSlug($slugHint ?: Str::slug($title) ?: Str::slug($topicTitle));
            Article::create([
                'slug'         => $slug,
                'title'        => $title,
                'lead'         => $lead,
                'body'         => $body,
                'faq'          => $faq,
                'gender'       => $gender,
                'is_published' => false,
            ]);

            // 使用済みトピックを削除
            if ($topicId) {
                ArticleTopic::destroy($topicId);
            }
            $this->info("  保存: {$slug}");
            $saved++;
        }

        if (!$this->option('dry-run') && $saved > 0) {
            $this->notifyAdmin($saved);
        }

        return self::SUCCESS;
    }

    private function resolveTopic(array $usedThisRun = []): array
    {
        $topic = ArticleTopic::approved()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->whereNotIn('title', $usedThisRun)
            ->first();

        if ($topic) {
            $result = [$topic->title, $topic->gender, $topic->id];
            return $result;
        }

        return ['', '', null];
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
        $slug  = $this->extractTag($text, 'SLUG');
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
            return [
                'slug'  => $slug,
                'title' => $title,
                'lead'  => $lead,
                'body'  => $body,
                'faq'   => $faq,
            ];
        }

        Log::warning('GenerateArticle parseArticle failed | ' . mb_substr($text, 0, 500));
        return [
            'slug'  => '',
            'title' => $fallbackTitle,
            'lead'  => '',
            'body'  => '',
            'faq'   => null,
        ];
    }

    private function extractTag(string $text, string $tag): string
    {
        if (preg_match('/<' . $tag . '>([\s\S]+?)<\/' . $tag . '>/i', $text, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base ?: 'article-' . now()->format('YmdHis');
        $i    = 0;
        while (Article::where('slug', $slug)->exists()) {
            $slug = $base . '-' . ++$i;
        }
        return $slug;
    }

    private function notifyAdmin(int $count): void
    {
        $adminEmail = config('mail.admin_address');
        if (!$adminEmail) return;

        try {
            Mail::raw(
                "{$count}件の記事の下書きが生成されました。\n\n"
                . "管理画面でレビュー・公開してください:\n"
                . config('app.url') . "/admin/articles/",
                fn($m) => $m->to($adminEmail)->subject("【ナイトワークリスト】新記事下書き{$count}件が生成されました")
            );
        } catch (\Exception $e) {
            Log::warning('記事生成通知メール送信失敗: ' . $e->getMessage());
        }
    }
}
