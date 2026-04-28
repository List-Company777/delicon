<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleGenerationPrompt;
use App\Models\ArticleTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArticleTopicController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title'  => 'required|string|max:255',
            'gender' => 'required|in:female,male,yoasobi,shop',
        ]);

        ArticleTopic::create([
            'title'      => $request->title,
            'gender'     => $request->gender,
            'status'     => 'pending',
            'source'     => 'admin',
            'sort_order' => ArticleTopic::max('sort_order') + 1,
        ]);

        return back()->with('topic_success', 'テーマを審査待ちに追加しました。');
    }

    public function approve(ArticleTopic $topic)
    {
        $topic->update(['status' => 'approved']);
        return back()->with('topic_success', '「' . $topic->title . '」を作成予定に追加しました。');
    }

    public function reject(ArticleTopic $topic)
    {
        $topic->delete();
        return back()->with('topic_success', 'テーマを却下しました。');
    }

    public function destroy(ArticleTopic $topic)
    {
        $topic->delete();
        return back()->with('topic_success', 'テーマを削除しました。');
    }

    public function suggest(Request $request)
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            return back()->with('topic_error', 'ANTHROPIC_API_KEY が設定されていません。');
        }

        $existingTitles  = Article::pluck('title')->merge(ArticleTopic::pluck('title'))->unique()->values();
        $prompts         = ArticleGenerationPrompt::pluck('instruction', 'gender');
        $currentYear     = now()->year;

        $existingList = $existingTitles->isNotEmpty()
            ? $existingTitles->map(fn($t) => '- ' . $t)->implode("\n")
            : '（なし）';

        $promptInstructions = collect(['female', 'male', 'yoasobi', 'shop'])
            ->map(fn($g) => "- {$g}: " . ($prompts[$g] ?? $g))
            ->implode("\n");

        $prompt = <<<PROMPT
あなたはナイトワーク・夜遊び情報サイト「ナイトワークリスト」のコンテンツプランナーです。
記事テーマ案を合計20件（gender別に各5件ずつ）提案してください。

現在の年: {$currentYear}年

## 対象読者別の設定
{$promptInstructions}

## 既存記事・検討中テーマ（重複・類似禁止）
{$existingList}

## 要件
- SEO価値が高く、具体的な検索キーワードを含むタイトル（30〜60文字）
- 「{$currentYear}年最新」「{$currentYear}年版」など年度を含めるとCTRが上がる
- female/male/business/shop 各5件、計20件を均等に提案する
- 風俗・性的なサービスには一切触れない
- JSON配列のみ出力し、それ以外のテキストは一切出力しないこと

## 出力形式
```json
[
  {"title": "記事タイトル", "gender": "female", "reason": "提案理由（40字以内）"},
  ...
]
```
PROMPT;

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 2048,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]);

            if (!$response->successful()) {
                Log::error('ArticleTopic suggest API error: ' . $response->body());
                return back()->with('topic_error', 'API呼び出しに失敗しました。');
            }

            $text = $response->json('content.0.text', '');
        } catch (\Exception $e) {
            Log::error('ArticleTopic suggest exception: ' . $e->getMessage());
            return back()->with('topic_error', 'API呼び出し中にエラーが発生しました。');
        }

        if (!preg_match('/```json\s*([\s\S]+?)\s*```/', $text, $m)) {
            Log::error('ArticleTopic suggest parse error: no json block. text=' . mb_substr($text, 0, 500));
            return back()->with('topic_error', 'AIの返答を解析できませんでした。');
        }

        $items = json_decode(trim($m[1]), true);
        if (!is_array($items)) {
            return back()->with('topic_error', 'AIの返答をJSONとして解析できませんでした。');
        }

        $sortBase = ArticleTopic::max('sort_order') + 1;
        $saved    = 0;
        foreach ($items as $i => $item) {
            $title  = trim($item['title'] ?? '');
            $gender = $item['gender'] ?? '';
            if (!$title || !in_array($gender, ['female', 'male', 'yoasobi', 'shop'])) continue;

            ArticleTopic::create([
                'title'      => $title,
                'gender'     => $gender,
                'status'     => 'pending',
                'source'     => 'ai',
                'ai_reason'  => mb_substr($item['reason'] ?? '', 0, 100),
                'sort_order' => $sortBase + $i,
            ]);
            $saved++;
        }

        return back()->with('topic_success', "AIが{$saved}件のテーマ案を審査待ちに追加しました。");
    }
}
