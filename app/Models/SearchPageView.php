<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchPageView extends Model
{
    public $timestamps = false;

    protected $fillable = ['gender', 'area_slug', 'job_slug', 'date', 'count'];

    protected $casts = ['date' => 'date'];

    public static function record(string $gender, string $area_slug, string $job_slug): void
    {
        static::upsert(
            [['gender' => $gender, 'area_slug' => $area_slug, 'job_slug' => $job_slug, 'date' => today()->toDateString(), 'count' => 1]],
            ['gender', 'area_slug', 'job_slug', 'date'],
            ['count' => \DB::raw('count + 1')]
        );
    }
}
