<?php

namespace App\Console\Commands;

use App\Models\Cast;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateAgesFromBirthdate extends Command
{
    protected $signature = 'casts:update-ages';
    protected $description = '生年月日が設定されているキャストの年齢を今日の日付で更新する';

    public function handle(): void
    {
        $updated = 0;
        Cast::whereNotNull('date_of_birth')->chunkById(200, function ($casts) use (&$updated) {
            foreach ($casts as $cast) {
                $newAge = Carbon::parse($cast->date_of_birth)->age;
                if ($cast->age !== $newAge) {
                    $cast->update(['age' => $newAge]);
                    $updated++;
                }
            }
        });
        $this->info(年齢更新完了: {}件);
    }
}
