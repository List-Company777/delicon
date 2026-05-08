<?php
namespace App\Http\Controllers\Manage;

use App\Models\Cast;
use App\Models\CastDiary;
use App\Models\CastDiaryImage;
use App\Models\CastDiaryToken;
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
        $scheduleStats = \App\Http\Controllers\Manage\DashboardController::scheduleStats();
        return view('manage.cast-diary.create', compact('cast', 'scheduleStats'));
    }

    public function store(Request $request, int $castId, ImageService $imgSvc)
    {
        $request->validate([
            'title'    => ['nullable', 'string', 'max:100'],
            'body'     => ['nullable', 'string', 'max:2000'],
            'status'   => ['required', 'in:draft,published'],
            'images'   => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'max:5120'],
        ]);

        $diary = CastDiary::create([
            'cast_id' => $castId,
            'title'   => $request->title,
            'body'    => $request->body,
            'status'  => $request->status,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $file) {
                $path = $imgSvc->saveDiaryImage($file, $castId, $diary->id, $i);
                CastDiaryImage::create(['diary_id' => $diary->id, 'img_path' => $path, 'sort_order' => $i, 'created_at' => now()]);
            }
        }

        return redirect()->route('cast-diary.index', $castId)->with('success', '日記を投稿しました');
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
}
