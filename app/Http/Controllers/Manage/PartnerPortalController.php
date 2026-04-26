<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\JobType;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PartnerPortalController extends Controller
{
    /** 店舗一覧 */
    public function index()
    {
        $partner = auth()->user()->partner;
        abort_if(! $partner, 403);

        $shops = Shop::where('partner_id', $partner->id)
            ->with(['genre', 'area.prefecture', 'detail'])
            ->orderByDesc('bid_price')
            ->orderBy('name')
            ->get();

        $totalCount     = $shops->count();
        $activeCount    = $shops->where('status', 'active')->count();
        $nonPublicCount = $totalCount - $activeCount;
        $rankings       = $partner->isManagement() ? $this->computeShopRankings($shops) : collect();

        return view('manage.partner.index', compact(
            'partner', 'shops', 'totalCount', 'activeCount', 'nonPublicCount', 'rankings'
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

    private function shopScore(Shop $shop): int
    {
        if ((int) $shop->budget_balance >= (int) $shop->bid_price && (int) $shop->bid_price > 0) {
            return (int) $shop->bid_price;
        }
        return $shop->main_image ? 15 : 5;
    }
}
