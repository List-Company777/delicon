<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ShopApproved;
use App\Mail\ShopRejected;
use App\Models\Area;
use App\Models\Genre;
use App\Models\Partner;
use App\Models\Prefecture;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ShopReviewController extends Controller
{
    public function index()
    {
        $status   = request('status', 'pending');
        $keyword  = request('keyword', '');
        $noArea   = request()->boolean('no_area');
        $prefId   = request('pref_id', '');
        $plan     = request('plan', '');

        $shops = Shop::with(['genre', 'area', 'users' => fn($q) => $q->wherePivot('role', 'owner')])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($keyword !== '', fn($q) => $q->where('name', 'like', '%' . $keyword . '%'))
            ->when($noArea, fn($q) => $q->whereNull('area_id'))
            ->when($prefId !== '', fn($q) => $q->where('prefecture_id', $prefId))
            ->when($plan === 'paid',  fn($q) => $q->where('plan', '<=', 3))
            ->when($plan === 'free',  fn($q) => $q->where('plan', 5))
            ->when(is_numeric($plan), fn($q) => $q->where('plan', (int)$plan))
            ->orderByRaw("FIELD(status, 'pending', 'inactive', 'active')")
            ->orderByDesc('updated_at')
            ->paginate(50);

        $counts = [
            'pending'  => Shop::where('status', 'pending')->count(),
            'active'   => Shop::where('status', 'active')->count(),
            'inactive' => Shop::where('status', 'inactive')->count(),
            'all'      => Shop::count(),
        ];
        $noAreaCount = Shop::whereNull('area_id')->count();
        $prefectures = Prefecture::orderBy('id')->get();
        $genres      = Genre::orderBy('id')->get();

        return view('admin.shops.index', compact(
            'shops', 'status', 'counts', 'keyword', 'noArea', 'noAreaCount',
            'prefectures', 'prefId', 'plan', 'genres'
        ));
    }

    public function show(int $id)
    {
        $shop = Shop::with([
            'genre', 'area', 'prefecture', 'partner',
            'detail', 'setPrices', 'externalUrls',
            'jobs.jobType',
            'users' => fn($q) => $q->wherePivot('role', 'owner'),
        ])->findOrFail($id);

        $partners = Partner::where('status', 'active')
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'type']);

        $areas = Area::with('prefecture')
            ->orderBy('prefecture_id')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'prefecture_id']);

        return view('admin.shops.show', compact('shop', 'partners', 'areas'));
    }

    public function approve(int $id)
    {
        $shop = Shop::findOrFail($id);
        $shop->update(['status' => 'active']);

        $owner = $shop->users()->wherePivot('role', 'owner')->first();
        if ($owner) {
            Mail::to($owner->email)->queue(new ShopApproved($shop));
        }

        return back()->with('success', "「{$shop->name}」を承認しました");
    }

    public function reject(int $id)
    {
        $shop = Shop::findOrFail($id);
        $shop->update(['status' => 'inactive']);

        $owner = $shop->users()->wherePivot('role', 'owner')->first();
        if ($owner) {
            Mail::to($owner->email)->queue(new ShopRejected($shop));
        }

        return back()->with('success', "「{$shop->name}」を非公開にしました");
    }

    public function updateBidPrice(Request $request, int $id)
    {
        $request->validate(['bid_price' => ['required', 'integer', 'min:10', 'max:9990']]);
        Shop::findOrFail($id)->update(['bid_price' => $request->bid_price]);
        return back()->with('success', '入札単価を更新しました');
    }

    public function updatePartner(Request $request, int $id)
    {
        $request->validate(['partner_id' => ['nullable', 'exists:partners,id']]);
        Shop::findOrFail($id)->update(['partner_id' => $request->partner_id ?: null]);
        return back()->with('success', '代理店を更新しました');
    }

    public function updateArea(Request $request, int $id)
    {
        $request->validate(['area_id' => ['nullable', 'exists:areas,id']]);
        $shop = Shop::findOrFail($id);
        $area = $request->area_id ? Area::find($request->area_id) : null;
        $shop->update([
            'area_id'       => $area?->id,
            'prefecture_id' => $area?->prefecture_id,
        ]);
        return back()->with('success', 'エリアを更新しました');
    }

    public function updatePlan(Request $request, int $id)
    {
        $request->validate([
            'plan'           => ['required', 'integer', 'min:1', 'max:5'],
            'is_banner_plan' => ['nullable', 'boolean'],
        ]);

        $shop     = Shop::findOrFail($id);
        $newPlan  = (int) $request->plan;
        $isBanner = (bool) $request->input('is_banner_plan', false);

        if ($newPlan > 4 && $shop->plan <= 4) {
            $shop->update(['plan' => $newPlan, 'is_banner_plan' => false, 'paid_since' => null]);
        } else {
            $shop->update([
                'plan'           => $newPlan,
                'is_banner_plan' => $newPlan === 3 ? $isBanner : false,
                'paid_since'     => $newPlan <= 4 ? ($shop->paid_since ?? now()->toDateString()) : null,
            ]);
        }

        return back()->with('success', "掲載プランを更新しました");
    }

    public function updateGenre(Request $request, int $id)
    {
        $request->validate(['genre_id' => ['nullable', 'integer', 'exists:genres,id']]);
        Shop::findOrFail($id)->update(['genre_id' => $request->genre_id ?: null]);
        return back()->with('success', 'ジャンルを更新しました');
    }

    public function destroy(int $id)
    {
        $shop = Shop::findOrFail($id);
        $shop->delete();
        return redirect()->route('admin.shops.index')->with('success', "「{$shop->name}」を削除しました");
    }

    public function downloadPermit(int $id)
    {
        $shop = Shop::findOrFail($id);
        abort_unless($shop->permit_document_path, 404);
        return response()->download(storage_path('app/' . $shop->permit_document_path));
    }
}
