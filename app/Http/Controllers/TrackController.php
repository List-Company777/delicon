<?php

namespace App\Http\Controllers;

use App\Mail\BudgetDepleted;
use App\Models\Job;
use App\Models\JobAccessLog;
use App\Models\Shop;
use App\Models\ShopAccessLog;
use App\Models\XmlFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;

class TrackController extends Controller
{
    /** 求人クリック（検索結果 → 求人詳細） */
    public function job(Request $request, int $id)
    {
        $job = Job::with('shop.partner')->where('status', 'active')->findOrFail($id);

        // 同一IPが1時間以内に同じ求人をクリック済みなら課金しない
        $isDuplicate = JobAccessLog::where('job_id', $id)
            ->where('type', 'view')
            ->where('ip', $request->ip())
            ->where('created_at', '>=', now()->subHour())
            ->exists();

        if (! $isDuplicate) {
            JobAccessLog::create([
                'job_id'     => $job->id,
                'type'       => 'view',
                'ip'         => $request->ip(),
                'user_agent' => mb_substr($request->userAgent() ?? '', 0, 300),
                'referrer'   => mb_substr($request->headers->get('referer') ?? '', 0, 500),
            ]);

            $reset = $job->shop->consumeBudget();
            if ($reset) {
                $this->notifyBudgetDepleted($job->shop);
            }

            // ベンダー（XMLフィード）の総額予算を消費
            if ($job->xml_source) {
                $feed = XmlFeed::where('slug', $job->xml_source)->first();
                $feed?->consumeBudget($job->shop->bid_price);
            }
        }

        return redirect()->route('job.show', $id, 302);
    }

    /** 店舗クリック（検索結果 → 店舗詳細 or hotlink） */
    public function shop(Request $request, int $id): RedirectResponse
    {
        $shop = Shop::with('partner', 'detail')->where('status', 'active')->findOrFail($id);

        if ($shop->detail && $shop->detail->is_hotlink && $shop->detail->hotlink_url) {
            return $this->hotlinkShopRedirect($request, $shop);
        }

        // 同一IPが1時間以内に同じ店舗をクリック済みなら課金しない
        $isDuplicate = ShopAccessLog::where('shop_id', $id)
            ->where('ip', $request->ip())
            ->where('created_at', '>=', now()->subHour())
            ->exists();

        if (! $isDuplicate) {
            ShopAccessLog::create([
                'shop_id'    => $shop->id,
                'ip'         => $request->ip(),
                'user_agent' => mb_substr($request->userAgent() ?? '', 0, 300),
                'referrer'   => mb_substr($request->headers->get('referer') ?? '', 0, 500),
            ]);

            $reset = $shop->consumeBudget();
            if ($reset) {
                $this->notifyBudgetDepleted($shop);
            }
        }

        return redirect()->route('shop.show', $id, 302);
    }

    private function hotlinkShopRedirect(Request $request, Shop $shop): RedirectResponse
    {
        $isDuplicate = ShopAccessLog::where('shop_id', $shop->id)
            ->where('ip', $request->ip())
            ->where('created_at', '>=', now()->subHour())
            ->exists();

        ShopAccessLog::create([
            'shop_id'    => $shop->id,
            'ip'         => $request->ip(),
            'user_agent' => mb_substr($request->userAgent() ?? '', 0, 300),
            'referrer'   => mb_substr($request->headers->get('referer') ?? '', 0, 500),
        ]);

        $shop->detail->increment('click_count');

        if (! $isDuplicate) {
            // hotlinkは通常の入札単価＋20円/クリック
            $reset = $shop->consumeBudget(20);
            if ($reset) {
                $this->notifyBudgetDepleted($shop);
            }
            if ($shop->xml_source) {
                $feed = XmlFeed::where('slug', $shop->xml_source)->first();
                $feed?->consumeBudget($shop->bid_price + 20);
            }
        }

        return redirect()->away($shop->detail->hotlink_url, 302);
    }

    private function notifyBudgetDepleted(Shop $shop): void
    {
        // 店舗オーナーへ通知
        $owner = $shop->users()->wherePivot('role', 'owner')->first();
        if ($owner) {
            Mail::to($owner->email)->queue(new BudgetDepleted($shop));
        }

        // 代理店へ通知
        if ($shop->partner) {
            Mail::to($shop->partner->email)->queue(new BudgetDepleted($shop));
        }
    }
}
