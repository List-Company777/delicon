<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CastDiary;

class CastDiaryController extends Controller
{
    public function index()
    {
        $diaries = CastDiary::with(['cast.shop', 'images'])
            ->whereNull('reviewed_at')
            ->latest()
            ->paginate(30);

        return view('admin.cast-diaries.index', compact('diaries'));
    }

    public function approve(CastDiary $diary)
    {
        $diary->update(['reviewed_at' => now()]);
        return back()->with('success', 'OK にしました');
    }

    public function destroy(CastDiary $diary)
    {
        $diary->delete();
        return back()->with('success', '日記を削除しました');
    }
}
