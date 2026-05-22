<?php

namespace App\Observers;

use App\Models\Cast;
use App\Services\IndexNowService;

class CastObserver
{
    private const CUP_TO_BODY = [
        'A'  => 2,                                               // 貧乳・ちっぱい
        'E'  => 1,  'F'  => 1,  'G'  => 1,                     // 巨乳
        'H'  => 16, 'I'  => 16, 'J'  => 16, 'K'  => 16,
        'L'  => 16, 'M'  => 16, 'N'  => 16,                    // 爆乳
    ];

    private const AUTO_TAG_JUKUJO = 57;  // 熟女系
    private const AUTO_TAG_TALL   = 25;  // 長身

    public function updated(Cast $cast): void
    {
        if ($cast->wasChanged('status') && $cast->status === 'active') {
            IndexNowService::ping(route('cast.show', $cast->id));
        }
    }

    public function saving(Cast $cast): void
    {
        $computed = $this->computeBodyId($cast->cup, $cast->tall);
        // cup/tallから確定できた場合のみ上書き。不明な場合は既存値を維持。
        if ($computed !== null) {
            $cast->body_id = $computed;
        }
    }

    public function saved(Cast $cast): void
    {
        $autoTags = [];
        if ($cast->age !== null && $cast->age >= 40) {
            $autoTags[] = self::AUTO_TAG_JUKUJO;
        }
        if ($cast->tall !== null && $cast->tall >= 170) {
            $autoTags[] = self::AUTO_TAG_TALL;
        }

        $manualTags = $cast->tags()
            ->wherePivotNotIn('tag_id', [self::AUTO_TAG_JUKUJO, self::AUTO_TAG_TALL])
            ->pluck('cast_tag_masters.id')
            ->toArray();

        $cast->tags()->sync(array_merge($manualTags, $autoTags));
    }

    private function computeBodyId(?string $cup, ?int $tall): ?int
    {
        if ($cup !== null) {
            $cup = strtoupper(trim($cup));
            if (isset(self::CUP_TO_BODY[$cup])) {
                return self::CUP_TO_BODY[$cup];
            }
        }
        if ($tall !== null) {
            if ($tall >= 170) return 3;  // 長身
            if ($tall <= 150) return 4;  // 小柄
        }
        return null;
    }
}
