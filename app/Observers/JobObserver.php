<?php

namespace App\Observers;

use App\Models\Job;
use App\Services\IndexNowService;

class JobObserver
{
    public function updated(Job $job): void
    {
        // active になった求人を未送信としてマーク。実際の送信は毎日15時の line:send-daily-job-alerts が行う
        if ($job->wasChanged('status') && $job->status === 'active') {
            $job->updateQuietly(['alert_sent_at' => null]);
            IndexNowService::ping(route('job.show', $job->id));
        }
    }
}
