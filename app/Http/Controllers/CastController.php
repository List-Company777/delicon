<?php

namespace App\Http\Controllers;

use App\Models\Cast;
use App\Models\Shop;
use App\Models\CastType;
use App\Models\CastBodyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CastController extends Controller
{
    public function index(Request $request)
    {
        $castTypes = Cache::remember('cast_types_all', 3600, fn() =>
            CastType::orderBy('id')->get()
        );
        $bodyTypes = Cache::remember('cast_body_types_all', 3600, fn() =>
            CastBodyType::orderBy('id')->get()
        );

        $query = Cast::active()
            ->with(['shop', 'castType', 'bodyType', 'tags'])
            ->whereHas('shop', fn($q) => $q->where('status', 'active'));

        if ($request->filled('type')) {
            $query->where('type_id', $request->type);
        }
        if ($request->filled('body')) {
            $query->where('body_id', $request->body);
        }
        if ($request->filled('age_from')) {
            $query->where('age', '>=', (int) $request->age_from);
        }
        if ($request->filled('age_to')) {
            $query->where('age', '<=', (int) $request->age_to);
        }
        if ($request->filled('cup')) {
            $query->where('cup', $request->cup);
        }

        $casts = $query->orderByDesc('is_recommended')
            ->orderBy('sort_order')
            ->paginate(30);

        return view('cast.index', compact('casts', 'castTypes', 'bodyTypes'));
    }

    public function show(Cast $cast)
    {
        if ($cast->status !== 'active') {
            abort(404);
        }

        $cast->load([
            'shop', 'castType', 'bodyType',
            'charms', 'plays', 'personalities', 'tags',
            'images', 'schedules', 'reviews',
        ]);

        $otherCasts = Cast::active()
            ->where('shop_id', $cast->shop_id)
            ->where('id', '!=', $cast->id)
            ->with(['castType'])
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        return view('cast.show', compact('cast', 'otherCasts'));
    }
}
