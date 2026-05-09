<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Genre;
use App\Models\JobType;
use App\Models\KeywordNormalization;
use App\Models\Prefecture;
use App\Models\SearchKeyword;
use Illuminate\Support\Facades\DB;

class LpNormalizationService
{
    private int $created = 0;
    private int $skipped = 0;

    public function generateForArea(Area $area): int
    {
        $this->created = 0;
        $this->skipped = 0;

        $this->generateForAreaInternal($area);

        return $this->created;
    }

    private function generateForAreaInternal(Area $area): void
    {
        $pref      = $area->prefecture ?? Prefecture::find($area->prefecture_id);
        $jobTypes  = JobType::orderBy('sort_order')->get();
        $genres    = Genre::orderBy('sort_order')->get();
        $genders   = ['male', 'female', 'yoasobi'];

        // ① エリア名 × [male, female, yoasobi]
        foreach ($genders as $gender) {
            $this->upsert($area->name, $gender, ['area_id' => $area->id]);
        }

        // ② 都道府県名 × [male, female, yoasobi]
        if ($pref) {
            foreach ($genders as $gender) {
                $this->upsert($pref->name, $gender, ['prefecture_id' => $pref->id]);
            }
        }

        // ③ エリア名 + 職種名 × male/female のみ（yoasobiは職種不要）
        foreach ($jobTypes as $jt) {
            if ($jt->keyword_filter) continue; // filter_slug系は別途処理
            $targets = match ($jt->target_gender) {
                'male'   => ['male'],
                'female' => ['female'],
                default  => ['male', 'female'],
            };
            foreach ($targets as $gender) {
                $this->upsert(
                    $area->name . ' ' . $jt->name,
                    $gender,
                    ['area_id' => $area->id, 'job_type_id' => $jt->id]
                );
            }
        }

        // ④ エリア名 + 業種名 × [male, female, yoasobi]
        foreach ($genres as $genre) {
            foreach (['male', 'female', 'yoasobi'] as $gender) {
                $this->upsert(
                    $area->name . ' ' . $genre->name,
                    $gender,
                    ['area_id' => $area->id, 'genre_id' => $genre->id]
                );
            }
        }

        // ⑤ エリア名 + filter_slug系JobType（日払い・未経験・カラオケ等）
        foreach ($jobTypes as $jt) {
            if (!$jt->keyword_filter) continue;
            foreach ($this->filterGenders($jt) as $gender) {
                $this->upsert(
                    $area->name . ' ' . $jt->name,
                    $gender,
                    ['area_id' => $area->id, 'filter_slug' => $jt->slug]
                );
            }
        }

        // ⑥ エリア名 + girl_type名（年齢・タイプ系 → girl-list/type/ LP）
        $girlTypes = DB::table('girl_types')->get(['id', 'name']);
        foreach ($girlTypes as $gt) {
            $this->upsert(
                $area->name . ' ' . $gt->name,
                'female',
                ['area_id' => $area->id, 'girl_type_id' => $gt->id]
            );
        }

    }

    public function generateAll(bool $newOnly = false): array
    {
        $this->created = 0;
        $this->skipped = 0;
        $addedPrefIds  = [];

        $areas    = Area::with('prefecture')->get();
        $jobTypes = JobType::orderBy('sort_order')->get();
        $genres   = Genre::orderBy('sort_order')->get();
        $genders  = ['male', 'female', 'yoasobi'];

        foreach ($areas as $area) {
            if ($newOnly && KeywordNormalization::where('area_id', $area->id)->exists()) {
                continue;
            }

            $this->generateForAreaInternal($area);

            // 都道府県（重複防止）
            $pref = $area->prefecture;
            if ($pref && !in_array($pref->id, $addedPrefIds)) {
                foreach ($genders as $gender) {
                    $this->upsert($pref->name, $gender, ['prefecture_id' => $pref->id]);
                }
                $addedPrefIds[] = $pref->id;
            }
        }

        return ['created' => $this->created, 'skipped' => $this->skipped];
    }

    public function getCreated(): int { return $this->created; }
    public function getSkipped(): int { return $this->skipped; }

    private function filterGenders(JobType $jt): array
    {
        // shop: プレフィックスのfilter（カラオケ等の設備系）→ yoasobi のみ
        if (str_starts_with($jt->keyword_filter, 'shop:')) {
            return ['yoasobi'];
        }
        // それ以外（日払い・未経験等のスタッフ系）→ male/female のみ
        return match ($jt->target_gender) {
            'male'   => ['male'],
            'female' => ['female'],
            default  => ['male', 'female'],
        };
    }

    private function upsert(string $keyword, string $gender, array $fields): void
    {
        $norm = KeywordNormalization::firstOrCreate(
            ['keyword' => $keyword, 'gender' => $gender],
            array_merge(['is_active' => true], $fields)
        );

        if (!$norm->wasRecentlyCreated) {
            $this->skipped++;
            return;
        }

        SearchKeyword::firstOrCreate(
            ['keyword' => $keyword, 'gender' => $gender],
            ['search_count' => 0, 'normalization_status' => 'confirmed']
        );

        $this->created++;
    }
}
