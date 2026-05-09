<?php
namespace App\Http\Controllers\Manage;

use App\Models\Cast;
use App\Models\CastSchedule;
use App\Models\CastShiftRequest;
use Illuminate\Http\Request;

class ShiftRequestController extends BaseController
{
    public function index()
    {
        $shop = $this->shopOrFail();
        $castIds = Cast::where('shop_id', $shop->id)->pluck('id');

        $pending = CastShiftRequest::with('cast')
            ->whereIn('cast_id', $castIds)
            ->where('status', 'pending')
            ->orderBy('work_date')
            ->get();

        $recent = CastShiftRequest::with('cast')
            ->whereIn('cast_id', $castIds)
            ->whereIn('status', ['approved', 'rejected'])
            ->where('updated_at', '>=', now()->subDays(7))
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get();

        return view('manage.shift-requests.index', compact('pending', 'recent'));
    }

    public function approve(int $id)
    {
        $shop = $this->shopOrFail();
        $req = CastShiftRequest::with('cast')
            ->where('status', 'pending')
            ->findOrFail($id);

        // shop owns this cast
        if ($req->cast->shop_id !== $shop->id) {
            abort(403);
        }

        // create schedule if not exists
        $exists = CastSchedule::where('cast_id', $req->cast_id)
            ->where('work_date', $req->work_date)
            ->exists();

        if (!$exists) {
            CastSchedule::create([
                'cast_id'    => $req->cast_id,
                'work_date'  => $req->work_date,
                'start_time' => $req->start_time,
                'end_time'   => $req->end_time,
                'note'       => $req->note,
            ]);
        }

        $req->update(['status' => 'approved', 'approved_at' => now()]);

        return back()->with('success', $req->cast->name . ' ' . $req->work_date->format('m/d') . ' を承認しました。');
    }

    public function reject(int $id)
    {
        $shop = $this->shopOrFail();
        $req = CastShiftRequest::with('cast')
            ->where('status', 'pending')
            ->findOrFail($id);

        if ($req->cast->shop_id !== $shop->id) {
            abort(403);
        }

        $req->update(['status' => 'rejected']);

        return back()->with('success', $req->cast->name . ' ' . $req->work_date->format('m/d') . ' を却下しました。');
    }
}
