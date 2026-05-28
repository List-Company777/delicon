<?php
namespace App\Http\Controllers;

use App\Models\CastFavorite;
use App\Models\Cast;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /** お気に入りトグル（AJAX JSON） */
    public function toggle(Cast $cast)
    {
        $user = auth()->user();
        $exists = CastFavorite::where('user_id', $user->id)->where('cast_id', $cast->id)->first();

        if ($exists) {
            $exists->delete();
            $favorited = false;
        } else {
            CastFavorite::create(['user_id' => $user->id, 'cast_id' => $cast->id]);
            $favorited = true;
        }

        return response()->json(['favorited' => $favorited]);
    }
}
