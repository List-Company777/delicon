<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\JobType;
use App\Models\Shop;
use App\Models\ShopPlanApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PartnerPortalController extends Controller
{
    /** 店舗一覧 */
    public function index(Request $request)
    {
        $partner = auth()->user()->partner;
        abort_if(! $partner, 403);

        $keyword = $request->input('keyword', '');

        $shops = Shop::where('partner_id', $partner->id)
            ->with(['genre', 'area.prefecture', 'detail', 'planApplications'])
            ->when($keyword !== '', fn($q) => $q->where('name', 'like', '%' . $keyword . '%'))
            ->orderByDesc('bid_price')
            ->orderBy('name')
            ->get();

        $totalCount     = $shops->count();
        $activeCount    = $shops->where('status', 'active')->count();
        $nonPublicCount = $totalCount - $activeCount;
        $rankings = $partner->isManagement()
            ? Cache::remember("partner_rankings:{$partner->id}", 1800, fn() => $this->computeShopRankings($shops))
            : collect();

        return view('manage.partner.index', compact(
            'partner', 'shops', 'totalCount', 'activeCount', 'nonPublicCount', 'rankings', 'keyword'
        ));
    }

    /** 代理操作開始 */
    public function actAs(int $shopId)
    {
        $partner = auth()->user()->partner;
        abort_if(! $partner, 403);

        $shop = Shop::where('id', $shopId)
            ->where('partner_id', $partner->id)
            ->firstOrFail();

        session(['acting_shop_id' => $shop->id]);

        return redirect()->route('manage.dashboard')
            ->with('success', "「{$shop->name}」の代理操作を開始しました");
    }

    /** 代理操作終了 */
    public function stopActing()
    {
        session()->forget('acting_shop_id');
        return redirect()->route('manage.partner.index');
    }

    /** 店舗アカウント削除（管理代行代理店のみ） */
    public function destroyShop(int $shopId)
    {
        $partner = auth()->user()->partner;
        abort_if(!$partner || !$partner->isManagement(), 403);

        $shop = Shop::where('id', $shopId)
            ->where('partner_id', $partner->id)
            ->firstOrFail();

        $shopName = $shop->name;
        $owners = $shop->users()->wherePivot('role', 'owner')->get();

        $shop->delete();

        foreach ($owners as $owner) {
            if (!$owner->shops()->exists()) {
                $owner->delete();
            }
        }

        return redirect()->route('manage.partner.index')
            ->with('success', "「{$shopName}」のアカウントを削除しました。");
    }

    /**
     * 各店舗の推定掲載順位を算出。
     * 順位スコア = budget_balance >= bid_price なら bid_price、画像あり→15、なし→5。
     * 関連エリア・都道府県のアクティブ店舗を一括取得して PHP 側でランク計算する。
     */
    private function computeShopRankings(Collection $shops): Collection
    {
        if ($shops->isEmpty()) return collect();

        $areaIds = $shops->pluck('area_id')->filter()->unique();
        $prefIds = $shops->map(fn($s) => $s->area?->prefecture_id)->filter()->unique();

        // エリア内アクティブ店舗を一括取得
        $areaShops = Shop::whereIn('area_id', $areaIds)
            ->where('status', 'active')
            ->select('id', 'bid_price', 'budget_balance', 'main_image', 'area_id', 'genre_id')
            ->get();

        // 都道府県内アクティブ店舗を一括取得
        $prefShops = Shop::whereHas('area', fn($q) => $q->whereIn('prefecture_id', $prefIds))
            ->where('status', 'active')
            ->with('area:id,prefecture_id')
            ->select('id', 'bid_price', 'budget_balance', 'main_image', 'area_id', 'genre_id')
            ->get();

        // スコアを事前計算してキャッシュ
        $areaScores = $areaShops->mapWithKeys(fn($s) => [$s->id => $this->shopScore($s)]);
        $prefScores = $prefShops->mapWithKeys(fn($s) => [$s->id => $this->shopScore($s)]);

        // 職種マップを一括取得（管理店舗 + エリア + 都道府県の全アクティブ求人）
        $allShopIds = $shops->pluck('id')
            ->merge($areaShops->pluck('id'))
            ->merge($prefShops->pluck('id'))
            ->unique();

        $jobMap = DB::table('jobs')
            ->whereIn('shop_id', $allShopIds)
            ->where('status', 'active')
            ->select('shop_id', 'job_type_id')
            ->distinct()
            ->get()
            ->groupBy('shop_id')
            ->map(fn($items) => $items->pluck('job_type_id')->values()->toArray());

        $jobTypeNames = JobType::pluck('name', 'id');

        return $shops->mapWithKeys(function ($shop) use ($areaShops, $prefShops, $areaScores, $prefScores, $jobMap, $jobTypeNames) {
            $score   = $this->shopScore($shop);
            $areaId  = $shop->area_id;
            $prefId  = $shop->area?->prefecture_id;
            $genreId = $shop->genre_id;

            // 小エリア × 全体
            $inArea    = $areaShops->where('area_id', $areaId);
            $areaRank  = $inArea->filter(fn($s) => ($areaScores[$s->id] ?? 0) > $score)->count() + 1;
            $areaTotal = $inArea->count();

            // 都道府県 × 全体
            $inPref    = $prefShops->filter(fn($s) => $s->area?->prefecture_id === $prefId);
            $prefRank  = $inPref->filter(fn($s) => ($prefScores[$s->id] ?? 0) > $score)->count() + 1;
            $prefTotal = $inPref->count();

            // 業種 × 小エリア
            $inGenreArea        = $inArea->where('genre_id', $genreId);
            $genreAreaRank      = $inGenreArea->filter(fn($s) => ($areaScores[$s->id] ?? 0) > $score)->count() + 1;
            $genreAreaTotal     = $inGenreArea->count();
            $topGenreAreaScores = $inGenreArea
                ->map(fn($s) => $areaScores[$s->id] ?? 0)
                ->sortDesc()
                ->take(3)
                ->values();

            // 職種 × エリア / 都道府県
            $myJobTypeIds = $jobMap[$shop->id] ?? [];
            $jobTypeRanks = [];

            foreach ($myJobTypeIds as $jtId) {
                $areaWithJt  = $inArea->filter(fn($s) => in_array($jtId, $jobMap[$s->id] ?? []));
                $jtAreaRank  = $areaWithJt->filter(fn($s) => ($areaScores[$s->id] ?? 0) > $score)->count() + 1;
                $jtAreaTotal = $areaWithJt->count();

                $prefWithJt  = $inPref->filter(fn($s) => in_array($jtId, $jobMap[$s->id] ?? []));
                $jtPrefRank  = $prefWithJt->filter(fn($s) => ($prefScores[$s->id] ?? 0) > $score)->count() + 1;
                $jtPrefTotal = $prefWithJt->count();

                $jobTypeRanks[$jtId] = [
                    'name'       => $jobTypeNames[$jtId] ?? "職種{$jtId}",
                    'area_rank'  => $jtAreaRank,
                    'area_total' => $jtAreaTotal,
                    'pref_rank'  => $jtPrefRank,
                    'pref_total' => $jtPrefTotal,
                ];
            }

            return [$shop->id => [
                'score'             => $score,
                'area_rank'         => $areaRank,
                'area_total'        => $areaTotal,
                'pref_rank'         => $prefRank,
                'pref_total'        => $prefTotal,
                'genre_area_rank'   => $genreAreaRank,
                'genre_area_total'  => $genreAreaTotal,
                'top_scores'        => $topGenreAreaScores,
                'job_type_ranks'    => $jobTypeRanks,
            ]];
        });
    }

    /** プラン申し込み */
    public function applyPlan(Request $request, int $shopId)
    {
        $partner = auth()->user()->partner;
        abort_if(! $partner, 403);

        $shop = Shop::where('id', $shopId)
            ->where('partner_id', $partner->id)
            ->where('status', 'active')
            ->firstOrFail();

        // 申し込み受付は毎月20日以降のみ
        abort_if(now()->day < 20, 422, '申し込み受付は毎月20日以降です');

        $request->validate(['plan' => ['required', 'integer', 'min:1', 'max:4']]);

        abort_if(
            ShopPlanApplication::where('shop_id', $shop->id)->where('status', 'pending')->exists(),
            422,
            '審査中の申し込みがすでにあります'
        );

        $planAmounts    = [1 => 80000, 2 => 40000, 3 => 20000, 4 => 0];
        $plan           = (int) $request->plan;
        $isCurrentlyPaid = in_array($shop->plan, [1, 2, 3]);
        $appType        = $isCurrentlyPaid ? 'renewal' : 'new';

        // 継続：翌月1日から。新規：承認後即時（effective_dateは承認時にセット）
        $nextMonthStart = now()->addMonthNoOverflow()->startOfMonth();
        $nextMonthEnd   = $nextMonthStart->copy()->endOfMonth()->toDateString();

        ShopPlanApplication::create([
            'shop_id'             => $shop->id,
            'partner_id'          => $partner->id,
            'plan'                => $plan,
            'application_type'    => $appType,
            'effective_date'      => $appType === 'renewal' ? $nextMonthStart->toDateString() : null,
            'expires_on'          => $nextMonthEnd,
            'amount'              => $planAmounts[$plan],
            'bid_price_requested' => 0,
            'status'              => 'pending',
        ]);

        $msg = $appType === 'renewal'
            ? "「{$shop->name}」の継続申し込みを送信しました（{$nextMonthStart->format('n月')}〜適用）。"
            : "「{$shop->name}」の有料掲載申し込みを送信しました。管理者承認後すぐに掲載開始されます。";

        return back()->with('success', $msg);
    }

    private function shopScore(Shop $shop): int
    {
        if ((int) $shop->budget_balance >= (int) $shop->bid_price && (int) $shop->bid_price > 0) {
            return (int) $shop->bid_price;
        }
        return $shop->main_image ? 15 : 5;
    }
}
