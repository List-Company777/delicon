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
        return view('diary.post', compact('cast', 'dt', 'postedToday'));
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
