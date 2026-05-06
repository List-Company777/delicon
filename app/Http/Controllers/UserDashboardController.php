<?php
namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Cast;
use App\Models\CastType;
use App\Models\CastView;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        $favorites = $user->favoriteCasts()
            ->with(['shop', 'castType'])
            ->where('casts.status', 'active')
            ->latest('cast_favorites.created_at')
            ->take(24)
            ->get();

        $recentlyViewed = CastView::where('user_id', $user->id)
            ->with(['cast' => fn($q) => $q->with(['shop'])->where('status', 'active')])
            ->latest('viewed_at')
            ->get()
            ->unique('cast_id')
            ->take(10)
            ->filter(fn($v) => $v->cast !== null)
            ->values();

        $recommendations = $user->hasPreferences() ? $this->getRecommendedCasts($user) : collect();

        return view('user.dashboard', compact('favorites', 'recentlyViewed', 'recommendations'));
    }

    public function settings()
    {
        $castTypes = CastType::orderBy('id')->get();
        $areas = Area::with('prefecture')
            ->orderBy('prefecture_id')
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn($a) => $a->prefecture?->prefecture ?? 'その他');

        return view('user.settings', [
            'user'      => auth()->user(),
            'castTypes' => $castTypes,
            'areas'     => $areas,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'notify_new_cast'      => ['boolean'],
            'notify_working'       => ['boolean'],
            'pref_cast_type_ids'   => ['nullable', 'array'],
            'pref_cast_type_ids.*' => ['integer'],
            'pref_area_ids'        => ['nullable', 'array'],
            'pref_area_ids.*'      => ['integer'],
        ]);

        auth()->user()->update([
            'notify_new_cast'    => $request->boolean('notify_new_cast'),
            'notify_working'     => $request->boolean('notify_working'),
            'pref_cast_type_ids' => $request->input('pref_cast_type_ids', []),
            'pref_area_ids'      => $request->input('pref_area_ids', []),
        ]);

        return back()->with('success', '設定を保存しました');
    }

    private function getRecommendedCasts(\App\Models\User $user, int $limit = 12): \Illuminate\Support\Collection
    {
        $typeIds = $user->pref_cast_type_ids ?? [];
        $areaIds = $user->pref_area_ids ?? [];

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

        return $query->take($limit)->get();
    }
}
