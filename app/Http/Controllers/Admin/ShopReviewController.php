<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ShopApproved;
use App\Mail\ShopRejected;
use App\Models\Area;
use App\Models\Partner;
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

        $shops = Shop::with(['genre', 'area', 'users' => fn($q) => $q->wherePivot('role', 'owner')])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($keyword !== '', fn($q) => $q->where('name', 'like', '%' . $keyword . '%'))
            ->when($noArea, fn($q) => $q->whereNull('area_id'))
            ->orderByRaw("FIELD(status, 'pending', 'inactive', 'active')")
            ->orderByDesc('updated_at')
            ->paginate(30);

        $counts = [
            'pending'  => Shop::where('status', 'pending')->count(),
            'active'   => Shop::where('status', 'active')->count(),
            'inactive' => Shop::where('status', 'inactive')->count(),
            'all'      => Shop::count(),
        ];
        $noAreaCount = Shop::whereNull('area_id')->count();

        return view('admin.shops.index', compact('shops', 'status', 'counts', 'keyword', 'noArea', 'noAreaCount'));
    }

    public function show(int $id)
    {
        $shop = Shop::with([
            'genre', 'area', 'prefecture', 'partner',
            'detail',
            'setPrices',
            'externalUrls',
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

    public function updateArea(Request $request, int $id)
    {
        $shop = Shop::findOrFail($id);
        $request->validate([
            'area_id' => ['nullable', 'exists:areas,id'],
        ]);
        $areaId = $request->input('area_id') ?: null;
        $shop->update(['area_id' => $areaId]);

        return back()->with('success', 'エリアを更新しました');
    }

    public function approve(int $id)
    {
        $shop = Shop::with(['users' => fn($q) => $q->wherePivot('role', 'owner'), 'partner'])
                    ->findOrFail($id);

        $shop->update(['status' => 'active']);

        $owner = $shop->users->first();
        if ($owner) {
            Mail::to($owner->email)->queue(new ShopApproved($shop));
        }

        return back()->with('success', '承認しました。');
    }

    public function reject(Request $request, int $id)
    {
        $shop = Shop::with(['users' => fn($q) => $q->wherePivot('role', 'owner')])
                    ->findOrFail($id);

        $note = $request->input('note');

        $shop->update(['status' => 'inactive']);

        $owner = $shop->users->first();
        if ($owner) {
            Mail::to($owner->email)->queue(new ShopRejected($shop, $note ?: null));
        }

        return back()->with('success', '却下しました。');
    }

    public function updateBidPrice(int $id)
    {
        $data = request()->validate([
            'bid_price' => ['required', 'integer', 'min:30', 'max:9990'],
        ]);

        Shop::findOrFail($id)->update($data);
        return back()->with('success', '入札単価を更新しました。');
    }

    public function addBudget(Request $request, int $id)
    {
        $request->validate([
            'amount' => ['required', 'integer', 'min:1000', 'max:9999999'],
        ]);

        $shop = Shop::findOrFail($id);
        $shop->increment('budget_balance', $request->integer('amount'));

        return back()->with('success', number_format($request->integer('amount')) . '円を残高に追加しました（残高: ' . number_format($shop->fresh()->budget_balance) . '円）');
    }

    public function updatePartner(Request $request, int $id)
    {
        $data = $request->validate([
            'partner_id' => ['nullable', 'exists:partners,id'],
        ]);

        Shop::findOrFail($id)->update(['partner_id' => $data['partner_id'] ?? null]);

        $msg = ($data['partner_id'] ?? null)
            ? '代理店を紐づけました。'
            : '代理店の紐づけを解除しました。';

        return back()->with('success', $msg);
    }

    public function updatePlan(\Illuminate\Http\Request $request, int $id)
    {
        $shop = Shop::findOrFail($id);
        $request->validate(['plan' => ['required', 'integer', 'min:1', 'max:3']]);

        $newPlan = (int) $request->plan;
        $oldPlan = (int) $shop->plan;

        $update = ['plan' => $newPlan];

        if ($newPlan < $oldPlan) {
            // アップグレード（上位プランへ）：paid_since をリセット
            $update['paid_since'] = $newPlan <= 3 ? now()->toDateString() : null;
        } elseif ($newPlan > $oldPlan && $newPlan > 3) {
            // 有料→無料へのダウングレード：paid_since をリセット
            $update['paid_since'] = null;
        }
        // 有料内のダウングレード：paid_since はそのまま維持

        $shop->update($update);

        return back()->with('success', "プランを {$newPlan} に変更しました。");
    }

    public function downloadPermit(int $id)
    {
        $shop = Shop::findOrFail($id);
        abort_if(! $shop->permit_document_path, 404);

        $path = storage_path('app/' . $shop->permit_document_path);
        abort_if(! file_exists($path), 404);

        return response()->file($path);
    }

    public function destroy(int $id)
    {
        $shop = Shop::findOrFail($id);
        $shopName = $shop->name;

        $owners = $shop->users()->wherePivot('role', 'owner')->get();

        $shop->delete(); // cascadeOnDelete で関連レコードも削除

        foreach ($owners as $owner) {
            if (!$owner->shops()->exists()) {
                $owner->delete();
            }
        }

        return redirect()->route('admin.shops.index')
            ->with('success', "「{$shopName}」を削除しました。");
    }
}
