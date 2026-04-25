<?php

namespace App\Jobs;

use App\Models\Job;
use App\Models\JobAlert;
use App\Models\LineMessageLog;
use App\Services\ShopNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendJobAlerts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $jobId) {}

    public function handle(): void
    {
        $job = Job::with(['shop.area', 'jobType', 'area'])->find($this->jobId);
        if (!$job || $job->status !== 'active') {
            return;
        }

        $searchGroups = match($job->search_group) {
            'female' => ['female'],
            'male'   => ['male'],
            'both'   => ['female', 'male'],
            default  => [],
        };
        if (empty($searchGroups)) return;

        // 求人が属するエリアID（jobs.area_id または shop.area_id）
        $areaId = $job->area_id ?? $job->shop->area_id ?? null;

        $alerts = JobAlert::where('is_active', true)
            ->whereIn('gender', $searchGroups)
            ->where(fn($q) =>
                // area_id が null（全国）または求人のエリアが一致
                $q->whereNull('area_id')
                  ->orWhere('area_id', $areaId)
            )
            ->where(fn($q) =>
                // job_type_id が null（なんでも）または一致
                $q->whereNull('job_type_id')
                  ->orWhere('job_type_id', $job->job_type_id)
            )
            ->get();

        if ($alerts->isEmpty()) return;

        // 送信前に残枠確認（15通バッファ確保）
        $remaining = LineMessageLog::remainingQuota();
        if ($remaining <= 0) {
            Log::warning("SendJobAlerts: LINE月間枠不足のためスキップ (job_id={$this->jobId})");
            return;
        }

        // 店舗オーナーの line_user_id を取得して除外リスト作成
        $ownerLineIds = \App\Models\User::whereNotNull('line_user_id')
            ->whereHas('shops', fn($q) => $q->wherePivot('role', 'owner'))
            ->pluck('line_user_id')
            ->flip()
            ->all();

        $areaName    = $job->area?->name ?? $job->shop->area?->name ?? '';
        $jobTypeName = $job->jobType?->name ?? '';
        $shopName    = $job->shop->name ?? '';
        $url         = route('job.show', $job->id) . '/';

        $message = "【ナイトワークリスト】新着求人のお知らせ\n\n"
            . ($areaName    ? "エリア：{$areaName}\n" : '')
            . ($jobTypeName ? "職種　：{$jobTypeName}\n" : '')
            . "店舗　：{$shopName}\n"
            . "{$job->title}\n\n"
            . $url
            . "\n\n▼ アラート解除\n「解除」と送信してください";

        $sent = 0;
        foreach ($alerts as $alert) {
            // 枠チェック（ループ内でも毎回確認）
            if ($sent >= $remaining) {
                Log::warning("SendJobAlerts: 残枠到達のため途中打ち切り (job_id={$this->jobId}, sent={$sent})");
                break;
            }

            // 店舗オーナーを除外
            if (isset($ownerLineIds[$alert->line_user_id])) {
                continue;
            }

            ShopNotifier::sendLinePush($alert->line_user_id, $message, 'job_alert');
            $sent++;
        }

        Log::info("SendJobAlerts: job_id={$this->jobId} sent={$sent}");
    }
}
