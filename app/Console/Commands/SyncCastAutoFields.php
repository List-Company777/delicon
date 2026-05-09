<?php

namespace App\Console\Commands;

use App\Models\Cast;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncCastAutoFields extends Command
{
    protected $signature   = 'casts:sync-auto-fields {--dry-run : 実行せず件数だけ表示}';
    protected $description = '全キャストのbody_idとcast_tagsをcup/tall/ageから自動再計算';

    private const CUP_TO_BODY = [
        'A'  => 2,
        'E'  => 1,  'F'  => 1,  'G'  => 1,
        'H'  => 16, 'I'  => 16, 'J'  => 16, 'K'  => 16,
        'L'  => 16, 'M'  => 16, 'N'  => 16,
    ];

    public function handle(): void
    {
        $total   = Cast::count();
        $updated = 0;
        $dryRun  = $this->option('dry-run');

        $this->info(($dryRun ? '[DRY-RUN] ' : '') . "対象: {$total}件");

        Cast::chunk(200, function ($casts) use ($dryRun, &$updated) {
            foreach ($casts as $cast) {
                $computed = $this->computeBodyId($cast->cup, $cast->tall);

                $autoTags = [];
                if ($cast->age !== null && $cast->age >= 40) $autoTags[] = 57;
                if ($cast->tall !== null && $cast->tall >= 170) $autoTags[] = 25;

                if (!$dryRun) {
                    // 確定できた場合のみbody_idを更新（既存値を維持するためnullは更新しない）
                    if ($computed !== null) {
                        DB::table('casts')->where('id', $cast->id)->update(['body_id' => $computed]);
                    }

                    $manualTags = DB::table('cast_tags')
                        ->where('cast_id', $cast->id)
                        ->whereNotIn('tag_id', [25, 57])
                        ->pluck('tag_id')
                        ->toArray();

                    $newTags = array_unique(array_merge($manualTags, $autoTags));

                    DB::table('cast_tags')->where('cast_id', $cast->id)->delete();
                    if (!empty($newTags)) {
                        DB::table('cast_tags')->insert(
                            array_map(fn($tid) => ['cast_id' => $cast->id, 'tag_id' => $tid], $newTags)
                        );
                    }
                }
                $updated++;
            }
            $this->output->write('.');
        });

        $this->newLine();
        $this->info("完了: {$updated}件処理しました");
    }

    private function computeBodyId(?string $cup, ?int $tall): ?int
    {
        if ($cup !== null) {
            $cup = strtoupper(trim($cup));
            if (isset(self::CUP_TO_BODY[$cup])) return self::CUP_TO_BODY[$cup];
        }
        if ($tall !== null) {
            if ($tall >= 170) return 3;
            if ($tall <= 150) return 4;
        }
        return null;
    }
}
