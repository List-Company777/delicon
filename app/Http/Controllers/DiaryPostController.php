<?php
namespace App\Http\Controllers;

use App\Models\CastDiary;
use App\Models\CastDiaryImage;
use App\Models\CastDiaryToken;
use App\Services\ImageService;
use Illuminate\Http\Request;

class DiaryPostController extends Controller
{
    private function findToken(string $token): ?CastDiaryToken
    {
        return CastDiaryToken::with('cast')->where('token', $token)->first();
    }

    private function postedToday(\App\Models\Cast $cast): bool
    {
        return CastDiary::where('cast_id', $cast->id)
            ->whereDate('created_at', today())
            ->exists();
    }

    public function show(string $token)
    {
        $t = $this->findToken($token);
        if (!$t) {
            abort(404);
        }
        if ($t->isExpired()) {
            return response()->view('diary.expired', [], 410);
        }
        $cast       = $t->cast;
        $dt         = $t;
        $postedToday = $this->postedToday($cast);
        $today = \Illuminate\Support\Carbon::today();
        $shiftRequests = \App\Models\CastShiftRequest::where('cast_id', $cast->id)
            ->where('work_date', '>=', $today)
            ->where('work_date', '<=', $today->copy()->addDays(13))
            ->orderBy('work_date')
            ->get()
            ->keyBy(fn($r) => $r->work_date->format('Y-m-d'));
        return view('diary.post', compact('cast', 'dt', 'postedToday', 'shiftRequests', 'today'));
    }

    public function storeShiftRequest(Request $request, string $token)
    {
        $t = $this->findToken($token);
        if (!$t || $t->isExpired()) { abort(404); }
        $cast = $t->cast;

        $data = $request->validate([
            'work_date'  => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . now()->addDays(13)->toDateString()],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time'   => ['nullable', 'date_format:H:i'],
            'note'       => ['nullable', 'string', 'max:50'],
        ]);

        $req = \App\Models\CastShiftRequest::updateOrCreate(
            ['cast_id' => $cast->id, 'work_date' => $data['work_date']],
            ['start_time' => $data['start_time'] ?? null, 'end_time' => $data['end_time'] ?? null, 'note' => $data['note'] ?? null, 'status' => 'pending', 'approved_at' => null]
        );

        // 店舗オーナーにメール通知
        $owner = $cast->shop?->users()->wherePivot('role', 'owner')->first();
        if ($owner) {
            \Illuminate\Support\Facades\Mail::to($owner->email)
                ->queue(new \App\Mail\ShiftRequestSubmitted($cast, $req));
        }

        return redirect('/diary/post/' . $token . '/#shift')->with('shift_success', 'シフトを申請しました。');
    }

    public function destroyShiftRequest(string $token, int $id)
    {
        $t = $this->findToken($token);
        if (!$t || $t->isExpired()) { abort(404); }

        \App\Models\CastShiftRequest::where('cast_id', $t->cast->id)
            ->where('status', 'pending')
            ->findOrFail($id)
            ->delete();

        return redirect('/diary/post/' . $token . '/#shift')->with('shift_success', '申請を取り消しました。');
    }

    public function store(Request $request, string $token, ImageService $imgSvc)
    {
        $t = $this->findToken($token);
        if (!$t) {
            abort(404);
        }
        if ($t->isExpired()) {
            return response()->view('diary.expired', [], 410);
        }
        $cast = $t->cast;

        if ($this->postedToday($cast)) {
            return back()->withErrors(['limit' => '本日はすでに投稿済みです。写メ日記は1日1件までです。']);
        }

        $request->validate([
            'body'      => ['nullable', 'string', 'max:2000'],
            'images'    => ['nullable', 'array', 'max:5'],
            'images.*'  => ['image', 'max:8192'],
        ]);

        $diary = CastDiary::create([
            'cast_id' => $cast->id,
            'title'   => null,
            'body'    => $request->body,
            'status'  => 'published',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $imgSvc->saveDiaryImage($file, $cast->id, $diary->id, $index);
                CastDiaryImage::create([
                    'diary_id'   => $diary->id,
                    'img_path'   => $path,
                    'sort_order' => $index,
                    'created_at' => now(),
                ]);
            }
        }

        return redirect('/diary/post/' . $token . '/')->with('success', '日記を投稿しました！');
    }
}
