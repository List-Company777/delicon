<?php

namespace App\Console\Commands;

use App\Services\LpNormalizationService;
use Illuminate\Console\Command;

class SeedLpNormalizations extends Command
{
    protected $signature = 'normalize:seed-lp
                            {--new-only : 既存の正規化がないエリアのみ処理}
                            {--dry-run : DBへの書き込みを行わない（件数確認のみ）}';

    protected $description = 'エリア・都道府県・職種・業種・filter_slug × male/female/yoasobi の正規化を一括生成する';

    public function handle(): int
    {
        if ($this->option('dry-run')) {
            $this->warn('【DRY-RUN】DBへの書き込みは行いません');
            $this->dryRun();
            return self::SUCCESS;
        }

        $newOnly = (bool) $this->option('new-only');
        $service = new LpNormalizationService;
        $result  = $service->generateAll($newOnly);

        $this->info("生成: {$result['created']} 件 / 既存スキップ: {$result['skipped']} 件");

        return self::SUCCESS;
    }

    private function dryRun(): void
    {
        $newOnly = (bool) $this->option('new-only');

        // サービスと同じロジックで件数だけカウント（DB書き込みなし）
        $areas    = \App\Models\Area::with('prefecture')->get();
        $jobTypes = \App\Models\JobType::orderBy('sort_order')->get();
        $genres   = \App\Models\Genre::orderBy('sort_order')->get();
        $genders  = ['male', 'female', 'yoasobi'];

        $would   = 0;
        $skipped = 0;
        $addedPrefIds = [];

        $exists = fn(string $kw, string $g) => \App\Models\KeywordNormalization::where('keyword', $kw)->where('gender', $g)->exists();

        foreach ($areas as $area) {
            if ($newOnly && \App\Models\KeywordNormalization::where('area_id', $area->id)->exists()) continue;

            foreach ($genders as $g) {
                $exists($area->name, $g) ? $skipped++ : $would++;
            }

            $pref = $area->prefecture;
            if ($pref && !in_array($pref->id, $addedPrefIds)) {
                foreach ($genders as $g) {
                    $exists($pref->name, $g) ? $skipped++ : $would++;
                }
                $addedPrefIds[] = $pref->id;
            }

            foreach ($jobTypes as $jt) {
                if ($jt->keyword_filter) continue;
                $targets = match ($jt->target_gender) {
                    'male' => ['male'], 'female' => ['female'], default => ['male', 'female'],
                };
                foreach ($targets as $g) {
                    $exists($area->name . ' ' . $jt->name, $g) ? $skipped++ : $would++;
                }
            }

            foreach ($genres as $genre) {
                foreach (['male', 'female', 'yoasobi'] as $g) {
                    $exists($area->name . ' ' . $genre->name, $g) ? $skipped++ : $would++;
                }
            }

            foreach ($jobTypes as $jt) {
                if (!$jt->keyword_filter) continue;
                $filterGenders = str_starts_with($jt->keyword_filter, 'shop:')
                    ? ['yoasobi']
                    : match ($jt->target_gender) {
                        'male' => ['male'], 'female' => ['female'], default => ['male', 'female'],
                    };
                foreach ($filterGenders as $g) {
                    $exists($area->name . ' ' . $jt->name, $g) ? $skipped++ : $would++;
                }
            }
        }

        $this->info("生成予定: {$would} 件 / 既存スキップ: {$skipped} 件");
    }
}
