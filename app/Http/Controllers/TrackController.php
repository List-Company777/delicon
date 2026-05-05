<?php

namespace App\Http\Controllers;

use App\Mail\BudgetDepleted;
use App\Models\Job;
use App\Models\JobAccessLog;
use App\Models\Shop;
use App\Models\ShopAccessLog;
use App\Models\XmlFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;

class TrackController extends Controller
{
    /** 求人クリック（検索結果 → 求人詳細） */
    public function job(Request $request, int $id)
    {
        $job = Job::with('shop.partner')->where('status', 'active')->findOrFail($id);

        if (! $this->isCrawler($request)) {
            $first = Cache::add("track:job:{$id}:{$request->ip()}", 1, 3600);
            if ($first) {
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

                if ($job->xml_source) {
                    $feed = $this->findXmlFeed($job->xml_source);
                    $feed?->consumeBudget($job->shop->bid_price);
                }
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

        if (! $this->isCrawler($request)) {
            $first = Cache::add("track:shop:{$id}:{$request->ip()}", 1, 3600);
            if ($first) {
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
        }

        return redirect()->route('shop.show', $id, 302);
    }

    private function hotlinkShopRedirect(Request $request, Shop $shop): RedirectResponse
    {
        if (! $this->isCrawler($request)) {
            $first = Cache::add("track:shop:{$shop->id}:{$request->ip()}", 1, 3600);
            if ($first) {
                ShopAccessLog::create([
                    'shop_id'    => $shop->id,
                    'ip'         => $request->ip(),
                    'user_agent' => mb_substr($request->userAgent() ?? '', 0, 300),
                    'referrer'   => mb_substr($request->headers->get('referer') ?? '', 0, 500),
                ]);

                $shop->detail->increment('click_count');

                $reset = $shop->consumeBudget(20);
                if ($reset) {
                    $this->notifyBudgetDepleted($shop);
                }
                if ($shop->xml_source) {
                    $feed = $this->findXmlFeed($shop->xml_source);
                    $feed?->consumeBudget($shop->bid_price + 20);
                }
            }
        }

        return redirect()->away($shop->detail->hotlink_url, 302);
    }

    private function findXmlFeed(string $slug): ?XmlFeed
    {
        $id = Cache::remember("xml_feed_id:{$slug}", 3600, fn() => XmlFeed::where('slug', $slug)->value('id'));
        return $id ? XmlFeed::find($id) : null;
    }

    private function isCrawler(Request $request): bool
    {
        $ua = strtolower($request->userAgent() ?? '');
        if ($ua === '') {
            return true;
        }

        $patterns = [
            'bot', 'crawl', 'spider', 'slurp', 'mediapartners',
            'facebookexternalhit', 'twitterbot', 'linkedinbot',
            'whatsapp', 'applebot', 'pinterest', 'semrush',
            'ahrefsbot', 'mj12bot', 'dotbot', 'bingpreview',
            'yandex', 'baiduspider', 'duckduckbot',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($ua, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function notifyBudgetDepleted(Shop $shop): void
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
