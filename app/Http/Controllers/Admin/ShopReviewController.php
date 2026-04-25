<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ShopApproved;
use App\Mail\ShopRejected;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ShopReviewController extends Controller
{
    public function index()
    {
        $status = request('status', 'pending');

        $shops = Shop::with(['genre', 'area', 'users' => fn($q) => $q->wherePivot('role', 'owner')])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->orderByRaw("FIELD(status, 'pending', 'inactive', 'active')")
            ->orderByDesc('updated_at')
            ->paginate(30);

        $counts = [
            'pending'  => Shop::where('status', 'pending')->count(),
            'active'   => Shop::where('status', 'active')->count(),
            'inactive' => Shop::where('status', 'inactive')->count(),
            'all'      => Shop::count(),
        ];

        return view('admin.shops.index', compact('shops', 'status', 'counts'));
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

        return view('admin.shops.show', compact('shop'));
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
}
