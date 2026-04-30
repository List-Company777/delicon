<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    protected $fillable = [
        'shop_id', 'job_type_id', 'area_id', 'prefecture_id', 'station_id',
        'title', 'description', 'faq', 'wage_type', 'hourly_wage_min', 'hourly_wage_max', 'working_hours', 'job_benefits', 'insurance', 'preventsmoke', 'holiday', 'employment_type',
        'gender_override', 'search_group', 'image_path', 'status',
        'is_hotlink', 'hotlink_url',
        'xml_source', 'xml_id', 'xml_enabled', 'xml_image_url', 'line_user_id',
        'click_count', 'expires_at', 'published_at',
    ];

    protected $casts = [
        'faq'         => 'array',
        'is_hotlink'  => 'boolean',
        'xml_enabled' => 'boolean',
        'expires_at'  => 'datetime',
        'published_at'=> 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function prefecture(): BelongsTo
    {
        return $this->belongsTo(Prefecture::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * プランに応じた件数上限内の求人のみに絞るスコープ。
     * キャスト（female/both）は無料1件・有料3件、スタッフ（male）は無料1件・有料5件。
     * id昇順で上位N件を残し、それ以降は非表示にする。
     */
    public function scopeWithinPlanLimit(Builder $query): Builder
    {
        // ウィンドウ関数で shop×カテゴリ内の順位を事前計算し JOIN する（コリレーテッドサブクエリ排除）
        return $query->joinSub(
            \DB::table('jobs as j2')
                ->join('shops as s', 's.id', '=', 'j2.shop_id')
                ->where('j2.status', 'active')
                ->selectRaw("
                    j2.id,
                    ROW_NUMBER() OVER (
                        PARTITION BY j2.shop_id,
                            CASE WHEN j2.search_group IN ('female','both') THEN 'cast' ELSE 'staff' END
                        ORDER BY j2.id ASC
                    ) AS rn,
                    CASE WHEN s.budget_balance >= s.bid_price THEN
                        CASE WHEN j2.search_group IN ('female','both') THEN 3 ELSE 5 END
                    ELSE 1 END AS plan_limit
                "),
            'plan_rank',
            'plan_rank.id', '=', 'jobs.id'
        )->whereRaw('plan_rank.rn <= plan_rank.plan_limit');
    }

    // search_groupをjob_type + gender_overrideから自動計算して保存
    public static function resolveSearchGroup(JobType $jobType, ?string $genderOverride): string
    {
        $base = $genderOverride ?? $jobType->target_gender;
        return $base; // male / female / both
    }
}
