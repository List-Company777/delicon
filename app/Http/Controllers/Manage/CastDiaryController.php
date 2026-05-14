<?php
namespace App\Http\Controllers\Manage;

use App\Models\Cast;
use App\Models\CastDiary;
use App\Models\CastDiaryImage;
use App\Models\CastDiaryToken;
use App\Models\CastFavorite;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;

class CastDiaryController extends BaseController
{
    public function index(int $castId)
    {
        $cast    = Cast::findOrFail($castId);
        $diaries = CastDiary::where('cast_id', $castId)->with('images')->latest()->paginate(20);
        return view('manage.cast-diary.index', compact('cast', 'diaries'));
    }

    public function create(int $castId)
    {
        $cast = Cast::findOrFail($castId);

        // このキャストのお気に入り登録者
        $fanList = CastFavorite::where('cast_id', $castId)
            ->join('users', 'cast_favorites.user_id', '=', 'users.id')
            ->select('users.name as user_name', 'users.preferred_days', 'users.preferred_times')
            ->get();

        $scheduleStats = $this->buildScheduleStats($fanList->map(fn($r) => (object)[
            'preferred_days'  => is_string($r->preferred_days)  ? json_decode($r->preferred_days, true)  : ($r->preferred_days ?? []),
            'preferred_times' => is_string($r->preferred_times) ? json_decode($r->preferred_times, true) : ($r->preferred_times ?? []),
        ]));

        return view('manage.cast-diary.create', compact('cast', 'scheduleStats', 'fanList'));
    }

    public function store(Request $request, int $castId, ImageService $imgSvc)
    {
        $request->validate([
            'title'    => ['nullable', 'string', 'max:100'],
            'body'     => ['nullable', 'string', 'max:2000'],
            'status'   => ['nullable', 'in:draft,published,pending'],
            'images'   => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'max:5120'],
        ]);

        $diary = CastDiary::create([
            'cast_id' => $castId,
            'title'   => $request->title,
            'body'    => $request->body,
            'status'  => 'published',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $file) {
                $path = $imgSvc->saveDiaryImage($file, $castId, $diary->id, $i);
                CastDiaryImage::create(['diary_id' => $diary->id, 'img_path' => $path, 'sort_order' => $i, 'created_at' => now()]);
            }
        }

        return redirect()->route('manage.cast-diary.index', $castId)->with('success', '日記を投稿しました');
    }

    public function shopDiaries()
    {
        $shop    = $this->shopOrFail();
        $castIds = Cast::where('shop_id', $shop->id)->pluck('id');
        $diaries = CastDiary::with(['cast', 'images'])
            ->whereIn('cast_id', $castIds)
            ->latest()
            ->paginate(30);

        return view('manage.cast-diary.shop-index', compact('shop', 'diaries'));
    }

    public function destroy(CastDiary $diary)
    {
        $diary->delete();
        return back()->with('success', '日記を削除しました');
    }

    public function issueToken(int $castId)
    {
        Cast::findOrFail($castId);
        CastDiaryToken::generateFor($castId);
        return back()->with('token_issued', true);
    }

    public static function buildScheduleStats(\Illuminate\Support\Collection $users): array
    {
        $total = $users->count();
        if ($total === 0) return ['total' => 0, 'days' => [], 'times' => []];

        $days  = array_fill_keys(['mon','tue','wed','thu','fri','sat','sun'], 0);
        $times = array_fill_keys(['morning','afternoon','evening','night','midnight'], 0);

        foreach ($users as $user) {
            foreach ($user->preferred_days  ?? [] as $d) { if (isset($days[$d]))  $days[$d]++; }
            foreach ($user->preferred_times ?? [] as $t) { if (isset($times[$t])) $times[$t]++; }
        }

        return ['total' => $total, 'days' => $days, 'times' => $times];
    }
}
