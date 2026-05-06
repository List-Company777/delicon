<?php
namespace App\Http\Controllers;

use App\Models\Cast;
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

        // お気に入りキャスト
        $favorites = $user->favoriteCasts()
            ->with(['shop', 'castType'])
            ->where('casts.status', 'active')
            ->latest('cast_favorites.created_at')
            ->take(24)
            ->get();

        // 閲覧履歴（最新10件・重複なし）
        $recentlyViewed = CastView::where('user_id', $user->id)
            ->with(['cast' => fn($q) => $q->with(['shop'])->where('status','active')])
            ->latest('viewed_at')
            ->get()
            ->unique('cast_id')
            ->take(10)
            ->filter(fn($v) => $v->cast !== null)
            ->values();

        return view('user.dashboard', compact('favorites', 'recentlyViewed'));
    }

    public function settings()
    {
        return view('user.settings', ['user' => auth()->user()]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'notify_new_cast' => ['boolean'],
            'notify_working'  => ['boolean'],
        ]);

        auth()->user()->update([
            'notify_new_cast' => $request->boolean('notify_new_cast'),
            'notify_working'  => $request->boolean('notify_working'),
        ]);

        return back()->with('success', '通知設定を保存しました');
    }
}
