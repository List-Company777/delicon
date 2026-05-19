<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExtractCastCharms extends Command
{
    protected $signature = 'casts:extract-charms
                            {--limit=0      : 処理件数上限（0=全件）}
                            {--dry-run      : DBへの書き込みを行わない}
                            {--reprocess    : 処理済みも再処理する}
                            {--keyword-only : キーワードマッチングのみ（AI使用なし）}
                            {--min-length=30: PRテキストの最小文字数}';

    protected $description = 'キャストのPRテキストから特徴（cast_charms）をキーワード+AIで自動抽出する';

    // cast_charm_types.id => キーワードパターン
    // 否定検出のため単純な正規表現リストにしている
    private const KEYWORD_PATTERNS = [
        2  => ['pattern' => '微乳|貧乳',           'negative' => true],
        3  => ['pattern' => '巨乳|爆乳|[FGHIJKfghijk]カップ', 'negative' => true],
        4  => ['pattern' => 'ショートヘア|ベリーショート',   'negative' => true],
        5  => ['pattern' => 'パイパン',             'negative' => true],
        6  => ['pattern' => 'メガネ|眼鏡|めがね',   'negative' => true],
        7  => ['pattern' => '黒髪',                 'negative' => true],
        9  => ['pattern' => '聞き上手',             'negative' => false],
        10 => ['pattern' => '美尻',                 'negative' => true],
        11 => ['pattern' => '小尻',                 'negative' => true],
        12 => ['pattern' => '巨尻',                 'negative' => true],
        13 => ['pattern' => '色白|白肌',            'negative' => true],
        14 => ['pattern' => '小麦色|日焼け',        'negative' => true],
        15 => ['pattern' => '美肌',                 'negative' => true],
        16 => ['pattern' => '美脚',                 'negative' => true],
        17 => ['pattern' => 'くびれ',               'negative' => true],
        18 => ['pattern' => '非喫煙',               'negative' => false],
        22 => ['pattern' => '八重歯',               'negative' => false],
    ];

    // キーワード直後 8文字以内にこれがあれば「否定」と判定してスキップ
    private const NEGATION_PATTERN = 'ではない|じゃない|でない|ではありません|じゃありません|ではなく|じゃなく';

    private bool $dryRun  = false;
    private int  $charmsAdded = 0;
    private int  $aiCalls = 0;
    private int  $errors  = 0;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        $limit        = (int)  $this->option('limit');
        $reprocess    = (bool) $this->option('reprocess');
        $keywordOnly  = (bool) $this->option('keyword-only');
        $minLength    = (int)  $this->option('min-length');

        if ($this->dryRun) {
            $this->warn('[DRY-RUN] DBへの書き込みは行いません');
        }

        $query = DB::table('casts')
            ->where('status', 'active')
            ->whereRaw('(LENGTH(COALESCE(comment,"")) + LENGTH(COALESCE(message,""))) >= ?', [$minLength])
            ->select('id', 'comment', 'message');

        if (!$reprocess) {
            $query->whereNull('charm_ai_processed_at');
        }

        $total = $query->count();
        if ($limit > 0) {
            $total = min($total, $limit);
        }

        $this->info("処理対象: {$total}件" . ($keywordOnly ? '（キーワードのみ）' : '（キーワード+AI）'));

        $processed = 0;
        $stopLoop  = false;

        $query->orderBy('id')->chunk(200, function ($rows) use (
            &$processed, $total, $limit, $keywordOnly, $reprocess, &$stopLoop
        ) {
            foreach ($rows as $row) {
                if ($limit > 0 && $processed >= $limit) {
                    $stopLoop = true;
                    break;
                }

                $this->processOne($row, $keywordOnly, $reprocess);
                $processed++;

                if ($processed % 500 === 0) {
                    $this->info("  {$processed}/{$total} 完了（AI: {$this->aiCalls}回 追加: {$this->charmsAdded}件）");
                }
            }
            return !$stopLoop;
        });

        $this->info("完了: {$processed}件処理 / チャーム追加: {$this->charmsAdded}件 / AI呼び出し: {$this->aiCalls}回 / エラー: {$this->errors}件");
        return self::SUCCESS;
    }

    private function processOne(object $row, bool $keywordOnly, bool $reprocess): void
    {
        $text = trim(implode(' ', array_filter([
            $row->comment ?? '',
            $row->message ?? '',
        ])));

        // ── キーワードフェーズ ──────────────────────────────────────
        $foundIds = [];
        foreach (self::KEYWORD_PATTERNS as $charmId => $cfg) {
            if (!preg_match('/' . $cfg['pattern'] . '/u', $text)) {
                continue;
            }
            // 否定チェック: マッチ箇所の直後8文字に否定語があればスキップ
            if ($cfg['negative']) {
                preg_match_all('/' . $cfg['pattern'] . '/u', $text, $matches, PREG_OFFSET_CAPTURE);
                $negated = false;
                foreach ($matches[0] as $match) {
                    $after = mb_substr($text, $match[1] + mb_strlen($match[0]), 8);
                    if (preg_match('/' . self::NEGATION_PATTERN . '/u', $after)) {
                        $negated = true;
                        break;
                    }
                }
                if ($negated) {
                    continue;
                }
            }
            $foundIds[] = $charmId;
        }

        // ── AIフェーズ ─────────────────────────────────────────────
        if (!$keywordOnly) {
            $aiIds = $this->callAi($text);
            if ($aiIds !== null) {
                $foundIds = array_values(array_unique(array_merge($foundIds, $aiIds)));
                $this->aiCalls++;
            }
        }

        if (!$this->dryRun) {
            $existing = DB::table('cast_charms')
                ->where('cast_id', $row->id)
                ->pluck('charm_type_id')
                ->all();

            $newIds = array_diff($foundIds, $existing);
            if (!empty($newIds)) {
                $inserts = array_map(fn($id) => ['cast_id' => $row->id, 'charm_type_id' => $id], $newIds);
                DB::table('cast_charms')->insertOrIgnore($inserts);
                $this->charmsAdded += count($newIds);
            }

            DB::table('casts')->where('id', $row->id)->update(['charm_ai_processed_at' => now()]);
        }
    }

    private function callAi(string $text): ?array
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            return null;
        }

        $charmNames = $this->getCharmNames();
        $charmList  = implode(' ', array_map(fn($id, $name) => "{$id}:{$name}", array_keys($charmNames), array_values($charmNames)));

        $prompt = <<<PROMPT
以下は風俗店キャストのPRテキストです。テキストに明確に記載または読み取れる特徴だけをJSON配列で返してください。否定されている特徴や推測は含めないでください。

特徴リスト: {$charmList}

PRテキスト:
{$text}

JSON配列のみ返してください（例:[1,6,13]）。該当なしは[]。
PROMPT;

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'x-api-key'         => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => 'claude-haiku-4-5-20251001',
                    'max_tokens' => 64,
                    'messages'   => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('ExtractCastCharms AI error: status=' . $response->status());
                $this->errors++;
                return null;
            }

            $content = $response->json('content.0.text', '');
            if (preg_match('/\[[\d,\s]*\]/', $content, $m)) {
                $ids      = json_decode($m[0], true);
                $validIds = array_keys($charmNames);
                if (is_array($ids)) {
                    return array_values(array_filter($ids, fn($id) => in_array((int)$id, $validIds)));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('ExtractCastCharms AI exception: ' . $e->getMessage());
            $this->errors++;
        }

        return null;
    }

    private function getCharmNames(): array
    {
        static $names = null;
        if ($names === null) {
            $names = DB::table('cast_charm_types')->pluck('name', 'id')->all();
        }
        return $names;
    }
}
