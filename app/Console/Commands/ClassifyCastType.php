<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClassifyCastType extends Command
{
    protected $signature = 'casts:classify-type
                            {--limit=0   : 処理件数上限（0=全件）}
                            {--dry-run   : DBへの書き込みを行わない}
                            {--reprocess : 処理済みも再処理する}';

    protected $description = '画像+テキストからキャスト分類（ai_type_id）をClaude Visionで自動判定する';

    private bool $dryRun     = false;
    private int  $classified = 0;
    private int  $skipped    = 0;
    private int  $errors     = 0;

    public function handle(): int
    {
        $this->dryRun  = (bool) $this->option('dry-run');
        $limit         = (int)  $this->option('limit');
        $reprocess     = (bool) $this->option('reprocess');

        if ($this->dryRun) {
            $this->warn('[DRY-RUN] DBへの書き込みは行いません');
        }

        $typeMap = DB::table('cast_types')->pluck('name', 'id')->all();

        $query = DB::table('casts')
            ->where('status', 'active')
            ->whereNotNull('img_file_name')
            ->where('img_file_name', '!=', '')
            ->select('id', 'img_file_name', 'comment', 'message', 'type_id');

        if (!$reprocess) {
            $query->whereNull('type_ai_processed_at');
        }

        $total = $query->count();
        if ($limit > 0) {
            $total = min($total, $limit);
        }

        $this->info("処理対象: {$total}件");

        $processed = 0;
        $stopLoop  = false;

        $query->orderBy('id')->chunk(100, function ($rows) use (
            &$processed, $total, $limit, $typeMap, &$stopLoop
        ) {
            foreach ($rows as $row) {
                if ($limit > 0 && $processed >= $limit) {
                    $stopLoop = true;
                    break;
                }

                $this->processOne($row, $typeMap);
                $processed++;

                if ($processed % 200 === 0) {
                    $this->info("  {$processed}/{$total} 完了（判定: {$this->classified}件 スキップ: {$this->skipped}件）");
                }
            }
            return !$stopLoop;
        });

        $this->info("完了: {$processed}件処理 / 判定: {$this->classified}件 / スキップ: {$this->skipped}件 / エラー: {$this->errors}件");
        return self::SUCCESS;
    }

    private function processOne(object $row, array $typeMap): void
    {
        $imagePath = public_path($row->img_file_name . 'big.jpg');

        if (!file_exists($imagePath)) {
            $this->skipped++;
            return;
        }

        $imageData = base64_encode(file_get_contents($imagePath));

        $typeList = implode("\n", array_map(
            fn($id, $name) => "{$id}: {$name}",
            array_keys($typeMap),
            array_values($typeMap)
        ));

        $text = trim(implode(' ', array_filter([
            $row->comment ?? '',
            $row->message ?? '',
        ])));

        $userContent = [
            [
                'type'   => 'image',
                'source' => [
                    'type'       => 'base64',
                    'media_type' => 'image/jpeg',
                    'data'       => $imageData,
                ],
            ],
            [
                'type' => 'text',
                'text' => $this->buildPrompt($typeList, $text),
            ],
        ];

        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            $this->errors++;
            return;
        }

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'x-api-key'         => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => 'claude-haiku-4-5-20251001',
                    'max_tokens' => 16,
                    'messages'   => [
                        ['role' => 'user', 'content' => $userContent],
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('ClassifyCastType AI error: status=' . $response->status());
                $this->errors++;
                return;
            }

            $content = trim($response->json('content.0.text', ''));

            // 人物なし・noimg判定
            if (strtolower($content) === 'null' || $content === '') {
                $this->skipped++;
                return;
            }

            $typeId = (int) $content;
            if (!isset($typeMap[$typeId])) {
                $this->skipped++;
                return;
            }

            if (!$this->dryRun) {
                DB::table('casts')->where('id', $row->id)->update([
                    'ai_type_id'          => $typeId,
                    'type_ai_processed_at' => now(),
                ]);
            }

            $this->classified++;

        } catch (\Throwable $e) {
            Log::warning('ClassifyCastType exception: ' . $e->getMessage());
            $this->errors++;
        }
    }

    private function buildPrompt(string $typeList, string $text): string
    {
        $textSection = $text !== ''
            ? "\nPRテキスト:\n{$text}"
            : '';

        return <<<PROMPT
この画像はデリヘルキャストのプロフィール写真です。

以下のルールに従って分類IDを1つだけ返してください:
- 画像に人物が写っていない場合（テキストのみ・準備中・プレースホルダー等）は「null」を返す
- 人物が写っている場合は、画像の外見とPRテキストを総合的に判断して最も近い分類IDの数字のみを返す

分類リスト:
{$typeList}{$textSection}

数字またはnullのみ返してください。説明不要。
PROMPT;
    }
}
