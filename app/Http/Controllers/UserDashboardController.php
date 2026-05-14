<?php
namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Cast;
use App\Models\CastSchedule;
use App\Models\CastType;
use App\Models\CastView;
use App\Models\DiscountCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $favorites = $user->favoriteCasts()
            ->with(['shop', 'castType'])
            ->where('casts.status', 'active')
            ->latest('cast_favorites.created_at')
            ->take(24)
            ->get();

        // 今日〜明後日のスケジュールをまとめて取得（cast_id → 最早work_date）
        $today     = Carbon::today();
        $dayAfter  = Carbon::today()->addDays(2);
        $castIds   = $favorites->pluck('id')->all();

        $scheduleMap = CastSchedule::whereIn('cast_id', $castIds)
            ->whereBetween('work_date', [$today, $dayAfter])
            ->orderBy('work_date')
            ->get()
            ->groupBy('cast_id')
            ->map(fn($rows) => $rows->first()->work_date); // Carbon date

        $recentlyViewed = CastView::where('user_id', $user->id)
            ->with(['cast' => fn($q) => $q->with(['shop'])->where('status', 'active')])
            ->latest('viewed_at')
            ->get()
            ->unique('cast_id')
            ->take(10)
            ->filter(fn($v) => $v->cast !== null)
            ->values();

        $recommendations = $user->hasPreferences() ? $this->getRecommendedCasts($user) : collect();

        return view('user.dashboard', compact('favorites', 'recentlyViewed', 'recommendations', 'scheduleMap'));
    }

    public function settings()
    {
        $castTypes = CastType::orderBy('id')->get();
        $areas = Area::with('prefecture')
            ->orderBy('prefecture_id')
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn($a) => $a->prefecture?->prefecture ?? 'その他');

        $user = auth()->user();
        $notifyShops = $user->shopNotifications()->with('shop')->get();

        $bodyTypes = DB::table('cast_body_types')->orderBy('sort_order')->orderBy('id')->get(['id', 'name']);

        return view('user.settings', [
            'user'        => $user,
            'castTypes'   => $castTypes,
            'areas'       => $areas,
            'notifyShops' => $notifyShops,
            'bodyTypes'   => $bodyTypes,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'notify_new_cast'      => ['boolean'],
            'pref_cast_type_ids'   => ['nullable', 'array'],
            'pref_cast_type_ids.*' => ['integer'],
            'pref_area_ids'        => ['nullable', 'array'],
            'pref_area_ids.*'      => ['integer'],
            'preferred_days'       => ['nullable', 'array'],
            'preferred_days.*'     => ['in:mon,tue,wed,thu,fri,sat,sun'],
            'preferred_times'      => ['nullable', 'array'],
            'preferred_times.*'    => ['in:morning,afternoon,evening,night,midnight'],
            'pref_age_min'          => ['nullable', 'integer', 'min:18', 'max:80'],
            'pref_age_max'          => ['nullable', 'integer', 'min:18', 'max:80'],
            'pref_body_type_ids'    => ['nullable', 'array'],
            'pref_body_type_ids.*'  => ['integer'],
        ]);

        auth()->user()->update([
            'notify_new_cast'    => $request->boolean('notify_new_cast'),
            'pref_cast_type_ids' => $request->input('pref_cast_type_ids', []),
            'pref_area_ids'      => $request->input('pref_area_ids', []),
            'pref_body_type_ids' => $request->input('pref_body_type_ids', []),
            'preferred_days'     => $request->input('preferred_days', []),
            'preferred_times'    => $request->input('preferred_times', []),
            'pref_age_min'       => $request->filled('pref_age_min') ? (int)$request->pref_age_min : null,
            'pref_age_max'       => $request->filled('pref_age_max') ? (int)$request->pref_age_max : null,
        ]);

        return back()->with('success', '設定を保存しました');
    }

    public function coupons()
    {
        $user = auth()->user();

        $coupons = DiscountCoupon::where('user_id', $user->id)
            ->with('shop:id,name')
            ->orderByDesc('created_at')
            ->get();

        return view('user.coupons', compact('coupons'));
    }

    private function getRecommendedCasts(\App\Models\User $user, int $limit = 12): \Illuminate\Support\Collection
    {
        $typeIds     = $user->pref_cast_type_ids  ?? [];
        $areaIds     = $user->pref_area_ids        ?? [];
        $bodyTypeIds = $user->pref_body_type_ids   ?? [];
        $days        = $user->preferred_days       ?? [];
        $times       = $user->preferred_times      ?? [];

        $query = Cast::with(['shop', 'castType'])
            ->where('status', 'active')
            ->whereHas('shop', fn($q) => $q->where('status', 'active'))
            ->orderByDesc('is_recommended')
            ->orderByDesc('id');

        if (!empty($typeIds)) {
            $query->whereIn('type_id', $typeIds);
        }
        if (!empty($areaIds)) {
            $query->whereHas('shop', fn($q) => $q->whereIn('area_id', $areaIds));
        }
        if (!empty($bodyTypeIds)) {
            $query->whereIn('body_id', $bodyTypeIds);
        }
        if ($user->pref_age_min !== null) {
            $query->where('age', '>=', $user->pref_age_min);
        }
        if ($user->pref_age_max !== null) {
            $query->where('age', '<=', $user->pref_age_max);
        }

        if (!empty($days) || !empty($times)) {
            $filteredQuery = clone $query;
            $filteredQuery->whereHas('schedules', function ($sq) use ($days, $times) {
                $sq->where('work_date', '>=', today())
                   ->where('work_date', '<=', today()->addDays(14));

                if (!empty($days)) {
                    // MySQLのDAYOFWEEK: 1=日,2=月,3=火,4=水,5=木,6=金,7=土
                    $dayMap = ['sun'=>1,'mon'=>2,'tue'=>3,'wed'=>4,'thu'=>5,'fri'=>6,'sat'=>7];
                    $dayNums = collect($days)->map(fn($d) => $dayMap[$d] ?? null)->filter()->values()->all();
                    if (!empty($dayNums)) {
                        $sq->whereIn(DB::raw('DAYOFWEEK(work_date)'), $dayNums);
                    }
                }

                if (!empty($times)) {
                    $timeRanges = [
                        'morning'   => ['06:00', '11:59'],
                        'afternoon' => ['12:00', '17:59'],
                        'evening'   => ['18:00', '21:59'],
                        'night'     => ['22:00', '23:59'],
                        'midnight'  => ['00:00', '05:59'],
                    ];
                    $sq->where(function ($tq) use ($times, $timeRanges) {
                        foreach ($times as $time) {
                            if (!isset($timeRanges[$time])) continue;
                            [$from, $to] = $timeRanges[$time];
                            $tq->orWhere(fn($r) => $r->where('start_time', '<=', $to)->where('end_time', '>=', $from));
                        }
                    });
                }
            });

            $results = $filteredQuery->take($limit)->get();
            if ($results->count() >= 3) {
                return $results;
            }
        }

        return $query->take($limit)->get();
    }

    public function toggleNotifyWorking(Request $request)
    {
        $user = auth()->user();
        $user->update(['notify_working' => !$user->notify_working]);
        return back()->with('notify_working_updated', true);
    }
}
