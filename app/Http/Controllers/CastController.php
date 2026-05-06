<?php

namespace App\Http\Controllers;

use App\Models\Cast;
use App\Models\CastType;
use App\Models\CastBodyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CastController extends Controller
{
    public function index(Request $request)
    {
        $castTypesRaw = Cache::remember('delicon:cast_types', 3600, fn() =>
            CastType::orderBy('id')->get()->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->all()
        );
        $castTypes = collect($castTypesRaw)->map(fn($t) => (object) $t);

        $bodyTypesRaw = Cache::remember('delicon:cast_body_types', 3600, fn() =>
            CastBodyType::orderBy('id')->get()->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->all()
        );
        $bodyTypes = collect($bodyTypesRaw)->map(fn($t) => (object) $t);

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
