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

    public function __construct(private array $jobIds) {}

    public function handle(): void
    {
        $jobs = Job::with(['shop.area', 'jobType', 'area'])
            ->whereIn('id', $this->jobIds)
            ->where('status', 'active')
            ->get();

        if ($jobs->isEmpty()) return;

        $remaining = LineMessageLog::remainingQuota();
        if ($remaining <= 0) {
            Log::warning('SendJobAlerts: LINE月間枠不足のためスキップ');
            return;
        }

        $ownerLineIds = \App\Models\User::whereNotNull('line_user_id')
            ->whereHas('shops', fn($q) => $q->wherePivot('role', 'owner'))
            ->pluck('line_user_id')
            ->flip()
            ->all();

        $alerts = JobAlert::where('is_active', true)->get();
        if ($alerts->isEmpty()) return;

        $nums = ['①', '②', '③'];
        $sent = 0;

        foreach ($alerts as $alert) {
            if ($sent >= $remaining) {
                Log::warning("SendJobAlerts: 残枠到達のため途中打ち切り (sent={$sent})");
                break;
            }

            if (isset($ownerLineIds[$alert->line_user_id])) continue;

            // このユーザーの希望条件に合致する求人だけ絞り込む
            $matched = $jobs->filter(function ($job) use ($alert) {
                // alert.gender='both' は全求人対象、それ以外は求人のsearch_groupと一致するか確認
                if ($alert->gender !== 'both') {
                    $acceptable = match($job->search_group) {
                        'female' => ['female'],
                        'male'   => ['male'],
                        'both'   => ['female', 'male'],
                        default  => [],
                    };
                    if (!in_array($alert->gender, $acceptable, true)) return false;
                }

                $areaId = $job->area_id ?? $job->shop->area_id ?? null;
                if ($alert->area_id !== null && $alert->area_id !== $areaId) return false;

                if ($alert->job_type_id !== null && $alert->job_type_id !== $job->job_type_id) return false;

                if ($alert->daily_pay_ok && !str_contains($job->title, '日払い')) return false;

                if ($alert->inexperienced_ok && !str_contains($job->title, '未経験')) return false;

                if ($alert->arubaito && !($job->employment_type === 'PART_TIME' && $job->wage_type === 'hourly')) return false;

                return true;
            })->values();

            if ($matched->isEmpty()) continue;

            $jobLines = $matched->map(function ($job, $index) use ($nums) {
                $areaName    = $job->area?->name ?? $job->shop->area?->name ?? '';
                $jobTypeName = $job->jobType?->name ?? '';
                $shopName    = $job->shop->name ?? '';
                $url         = route('job.show', $job->id) . '/';
                $num         = $nums[$index] ?? ($index + 1) . '.';

                $line = $num . ' ';
                $line .= $areaName ? "{$areaName}" : '';
                $line .= $jobTypeName ? "｜{$jobTypeName}" : '';
                $line .= "\n　{$shopName}";
                $line .= "\n　{$job->title}";
                $line .= "\n　{$url}";
                return $line;
            })->implode("\n\n");

            $message = "【ナイトワークリスト】新着求人のお知らせ\n\n"
                . $jobLines
                . "\n\n▼ アラート解除\n「解除」と送信してください";

            ShopNotifier::sendLinePush($alert->line_user_id, $message, 'job_alert');
            $sent++;
        }

        Log::info('SendJobAlerts: jobs=[' . implode(',', $this->jobIds) . "] sent={$sent}");
    }
}
