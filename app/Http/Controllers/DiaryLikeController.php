<?php
namespace App\Http\Controllers;

use App\Models\CastDiary;
use App\Models\DiaryLike;

class DiaryLikeController extends Controller
{
    public function toggle(CastDiary $diary)
    {
        $userId = auth()->id();
        $existing = DiaryLike::where('diary_id', $diary->id)->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            DiaryLike::create(['diary_id' => $diary->id, 'user_id' => $userId]);
            $liked = true;
        }

        $count = DiaryLike::where('diary_id', $diary->id)->count();

        return response()->json(['liked' => $liked, 'count' => $count]);
    }
}
