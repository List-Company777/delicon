<?php

namespace App\Http\Controllers;

use App\Mail\ReportMail;
use App\Models\Job;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class ReportController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'target_type'    => ['required', 'in:shop,job'],
            'target_id'      => ['required', 'integer', 'min:1'],
            'reason'         => ['required', 'in:closed,false_info,inappropriate,other'],
            'comment'        => ['nullable', 'string', 'max:300'],
            'reporter_email' => ['nullable', 'email', 'max:200'],
        ]);

        $ip = $request->ip();
        $cacheKey = "report_{$ip}_{$validated['target_type']}_{$validated['target_id']}";

        if (Cache::has($cacheKey)) {
            return back()->with('report_error', '短時間に同じ通報を複数送信することはできません。');
        }

        // ターゲット情報を取得
        if ($validated['target_type'] === 'shop') {
            $shop = Shop::findOrFail($validated['target_id']);
            $targetName = $shop->name;
            $shopId     = $shop->id;
            $shopName   = $shop->name;
        } else {
            $job = Job::with('shop')->findOrFail($validated['target_id']);
            $targetName = $job->title;
            $shopId     = $job->shop->id;
            $shopName   = $job->shop->name;
        }

        $reasonLabels = [
            'closed'        => '閉店している',
            'false_info'    => '虚偽・誇大情報',
            'inappropriate' => '不適切なコンテンツ',
            'other'         => 'その他',
        ];

        Mail::to(config('mail.admin_address'))
            ->send(new ReportMail(
                targetType:    $validated['target_type'],
                targetId:      (int) $validated['target_id'],
                targetName:    $targetName,
                shopId:        $shopId,
                shopName:      $shopName,
                reason:        $reasonLabels[$validated['reason']],
                comment:       $validated['comment'] ?? '',
                reporterEmail: $validated['reporter_email'] ?? '',
                reporterIp:    $ip,
            ));

        Cache::put($cacheKey, true, now()->addHour());

        return back()->with('report_sent', true);
    }
}
