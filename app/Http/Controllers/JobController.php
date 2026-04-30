<?php

namespace App\Http\Controllers;

use App\Mail\BudgetDepleted;
use App\Models\Job;
use App\Models\JobAccessLog;
use App\Models\XmlFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class JobController extends Controller
{
    public function show(Request $request, int $id)
    {
        $job = Job::with(['shop.genre', 'shop.area', 'shop.detail', 'shop.partner', 'jobType', 'area', 'station'])
            ->where('status', 'active')
            ->findOrFail($id);

        // ホットリンクは直接リダイレクト（課金 + 転送）
        if ($job->is_hotlink && $job->hotlink_url) {
            return $this->hotlinkRedirect($request, $job);
        }

        // 詳細PV記録
        $this->recordAccess($request, $job, 'view');

        $gender = match($job->search_group) {
            'male'  => 'male',
            'both'  => 'both',
            default => 'female',
        };

        // 同じ店舗の他の有効求人
        $sameShopJobs = Job::with('jobType')
            ->where('shop_id', $job->shop_id)
            ->where('id', '!=', $job->id)
            ->where('status', 'active')
            ->orderByDesc('is_hotlink')
            ->limit(4)
            ->get();

        // 無料店舗の求人詳細のみ表示・有料/XML店優先・同職種×同エリア（不足時は同都道府県で補完）
        $relatedJobs = collect();
        if (! $job->shop->hasBudget() && $job->job_type_id) {
            $buildBase = fn() => Job::with(['shop', 'jobType'])
                ->join('shops', 'shops.id', '=', 'jobs.shop_id')
                ->where('jobs.id', '!=', $job->id)
                ->where('jobs.shop_id', '!=', $job->shop_id)
                ->where('jobs.status', 'active')
                ->where('jobs.search_group', $job->search_group)
                ->where('jobs.job_type_id', $job->job_type_id)
                ->where(fn($q) => $q
                    ->whereRaw('shops.budget_balance >= shops.bid_price')
                    ->orWhereNotNull('shops.xml_source')
                )
                ->orderByRaw('(shops.budget_balance >= shops.bid_price) DESC')
                ->orderByDesc('shops.bid_price');

            if ($job->area_id) {
                $relatedJobs = $buildBase()
                    ->where('jobs.area_id', $job->area_id)
                    ->limit(4)
                    ->get(['jobs.*']);
            }

            if ($relatedJobs->count() < 4 && $job->prefecture_id) {
                $additional = $buildBase()
                    ->whereNotIn('jobs.id', $relatedJobs->pluck('id')->push($job->id))
                    ->where('jobs.prefecture_id', $job->prefecture_id)
                    ->limit(4 - $relatedJobs->count())
                    ->get(['jobs.*']);
                $relatedJobs = $relatedJobs->concat($additional);
            }
        }

        return view('job.show', compact('job', 'gender', 'sameShopJobs', 'relatedJobs'));
    }

    public function click(Request $request, int $id)
    {
        $job = Job::with('shop.partner')
            ->where('status', 'active')
            ->where('is_hotlink', true)
            ->findOrFail($id);

        return $this->hotlinkRedirect($request, $job);
    }

    private function hotlinkRedirect(Request $request, Job $job)
    {
        $isDuplicate = JobAccessLog::where('job_id', $job->id)
            ->where('type', 'click')
            ->where('ip', $request->ip())
            ->where('created_at', '>=', now()->subHour())
            ->exists();

        $this->recordAccess($request, $job, 'click');
        $job->increment('click_count');

        if (! $isDuplicate) {
            $reset = $job->shop->consumeBudget();
            if ($reset) {
                $this->notifyBudgetDepleted($job->shop);
            }

            if ($job->xml_source) {
                $feed = XmlFeed::where('slug', $job->xml_source)->first();
                $feed?->consumeBudget($job->shop->bid_price);
            }
        }

        return redirect()->away($job->hotlink_url, 302);
    }

    private function recordAccess(Request $request, Job $job, string $type): void
    {
        JobAccessLog::create([
            'job_id'     => $job->id,
            'type'       => $type,
            'ip'         => $request->ip(),
            'user_agent' => mb_substr($request->userAgent() ?? '', 0, 300),
            'referrer'   => mb_substr($request->headers->get('referer') ?? '', 0, 500),
        ]);
    }

    private function notifyBudgetDepleted(\App\Models\Shop $shop): void
    {
        $owner = $shop->users()->wherePivot('role', 'owner')->first();
        if ($owner) {
            Mail::to($owner->email)->queue(new BudgetDepleted($shop));
        }
        if ($shop->partner) {
            Mail::to($shop->partner->email)->queue(new BudgetDepleted($shop));
        }
    }
}
