<?php

namespace App\Http\Controllers;

use App\Mail\NewApplicationToShop;
use App\Models\Job;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApplicationController extends Controller
{
    public function create(int $jobId)
    {
        $job = Job::with(['shop', 'jobType', 'area'])
            ->where('status', 'active')
            ->findOrFail($jobId);

        $gender = match($job->search_group) {
            'male'  => 'male',
            'both'  => 'male',
            default => 'female',
        };

        return view('apply.create', compact('job', 'gender'));
    }

    public function store(Request $request, int $jobId)
    {
        $job = Job::where('status', 'active')->findOrFail($jobId);

        $validated = $request->validate([
            'applicant_name'  => 'required|string|max:50',
            'applicant_age'   => 'nullable|integer|min:18|max:99',
            'applicant_email' => 'required|email|max:255',
            'applicant_tel'   => 'required|string|max:20',
            'message'         => 'nullable|string|max:1000',
        ], [
            'applicant_name.required'  => 'お名前を入力してください',
            'applicant_age.min'        => '18歳以上の方のみご応募いただけます',
            'applicant_email.required' => 'メールアドレスを入力してください',
            'applicant_email.email'    => '正しいメールアドレスを入力してください',
            'applicant_tel.required'   => '電話番号を入力してください',
        ]);

        session(['apply_data_' . $jobId => $validated]);

        return redirect()->route('apply.confirm', $jobId);
    }

    public function confirm(int $jobId)
    {
        $job = Job::with(['shop', 'jobType', 'area'])
            ->where('status', 'active')
            ->findOrFail($jobId);

        $data = session('apply_data_' . $jobId);
        if (!$data) {
            return redirect()->route('apply.create', $jobId);
        }

        $gender = match($job->search_group) {
            'male'  => 'male',
            'both'  => 'male',
            default => 'female',
        };

        return view('apply.confirm', compact('job', 'data', 'gender'));
    }

    public function finalStore(Request $request, int $jobId)
    {
        $job = Job::with(['shop'])->where('status', 'active')->findOrFail($jobId);

        $validated = session('apply_data_' . $jobId);
        if (!$validated) {
            return redirect()->route('apply.create', $jobId);
        }

        $application = Application::create([
            ...$validated,
            'job_id'  => $job->id,
            'shop_id' => $job->shop_id,
            'status'  => 'new',
        ]);

        // セッション削除
        session()->forget('apply_data_' . $jobId);

        // 応募者への確認メール
        try {
            $shop = $job->shop;
            $shopInfo = "━━ 店舗情報 ━━━━━━━━━━━━━━━\n";
            $shopInfo .= "求人名　：{$job->title}\n";
            $shopInfo .= "店舗名　：{$shop->name}\n";
            if ($shop->full_address) {
                $shopInfo .= "住所　　：{$shop->full_address}\n";
                $shopInfo .= "地図　　：https://maps.google.com/?q=" . urlencode($shop->full_address) . "\n";
            }
            if ($shop->nearest_station_name) {
                $station = ($shop->nearest_line ? $shop->nearest_line . ' ' : '')
                    . $shop->nearest_station_name . '駅'
                    . ($shop->nearest_station_walk ? ' 徒歩' . $shop->nearest_station_walk . '分' : '');
                $shopInfo .= "最寄り駅：{$station}\n";
            }
            if ($shop->detail?->opening_hours) {
                $shopInfo .= "営業開始：{$shop->detail->opening_hours}\n";
            }
            $shopInfo .= "━━━━━━━━━━━━━━━━━━━━━━━";

            Mail::raw(
                "【デリヘルリスト】応募を受け付けました\n\n"
                . "{$validated['applicant_name']} 様\n\n"
                . "以下の求人にご応募いただきました。\n\n"
                . $shopInfo
                . "\n\n店舗より連絡があるまでしばらくお待ちください。\n\n"
                . "デリヘルリスト\nhttps://delicon.jp/",
                fn($m) => $m
                    ->to($validated['applicant_email'], $validated['applicant_name'])
                    ->subject('【デリヘルリスト】応募を受け付けました')
            );
        } catch (\Exception $e) {
            Log::warning(__CLASS__ . ': メール送信失敗: ' . $e->getMessage());
        }

        // 店舗オーナーへの通知（メール＋LINE）
        $owner = $job->shop->users()->wherePivot('role', 'owner')->first();
        if ($owner) {
            try {
                // CC: admin + 管理代行パートナー
                $cc = [config('mail.admin_address')];
                $partner = $job->shop->partner;
                if ($partner && $partner->type === 'management' && $partner->email) {
                    $cc[] = $partner->email;
                }

                Mail::to($owner->email, $owner->name)
                    ->cc($cc)
                    ->send(new NewApplicationToShop($application, $job));
            } catch (\Exception $e) {
                Log::error("NewApplicationToShop mail failed: job={$job->id} - {$e->getMessage()}");
            }

            // LINE通知：有料店またはXML連携店のみ送信
            // 求人単位XML ID → 管理画面登録Notify ID → オーナーLINEログインIDの優先順
            $lineUserId = $job->line_user_id
                ?? $job->shop->line_notify_user_id
                ?? $owner->line_user_id;
            $canNotify = $job->shop->hasBudget() || $job->shop->isXmlActive();
            if ($canNotify && $lineUserId && config('services.line.messaging_token')) {
                try {
                    Http::withToken(config('services.line.messaging_token'))
                        ->post('https://api.line.me/v2/bot/message/push', [
                            'to'       => $lineUserId,
                            'messages' => [[
                                'type' => 'text',
                                'text' => "【デリヘルリスト】新しい応募が届きました\n\n"
                                    . "求人：{$job->title}\n"
                                    . "応募者：{$validated['applicant_name']}\n\n"
                                    . "管理画面で確認してください。\n"
                                    . route('manage.applications.show', $application->id) . "/",
                            ]],
                        ]);
                } catch (\Exception $e) {
                    Log::error("NewApplication LINE push failed: job={$job->id} - {$e->getMessage()}");
                }
            }
        }

        return redirect()
            ->route('apply.complete', $job->id)
            ->with('application_id', $application->id);
    }

    public function complete(int $jobId)
    {
        $job = Job::with(['shop', 'jobType', 'area'])->findOrFail($jobId);

        // 全店舗の応募完了で関連求人を表示・有料/XML店優先・同職種×同エリア（不足時は同都道府県で補完）
        $relatedJobs = collect();
        if ($job->job_type_id) {
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
                $relatedJobs = $relatedJobs->merge($additional);
            }
        }

        return view('apply.complete', compact('job', 'relatedJobs'));
    }
}
