<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\JobType;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SuggestController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q      = mb_substr(trim($request->input('q', '')), 0, 40);
        $type   = $request->input('type', 'area'); // area | keyword
        $gender = $request->input('gender', 'female');

        if (mb_strlen($q) < 1) {
            return response()->json([]);
        }

        if ($type === 'keyword') {
            return $this->suggestKeyword($q, $gender);
        }

        return $this->suggestArea($q);
    }

    private function suggestArea(string $q): JsonResponse
    {
        $results = Area::where(function ($query) use ($q) {
                $query->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('slug', 'LIKE', "%{$q}%");
            })
            ->whereExists(function ($sub) {
                $sub->from('shops')
                    ->whereColumn('shops.area_id', 'areas.id')
                    ->where('shops.status', 'active');
            })
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END, name", ["{$q}%"])
            ->limit(8)
            ->pluck('name');

        return response()->json($results);
    }

    private function suggestKeyword(string $q, string $gender): JsonResponse
    {
        $jobTypeNames = Cache::remember("suggest_jobtypes_v2_{$gender}", 3600, function () use ($gender) {
            return JobType::orderBy('sort_order')
                ->when($gender !== 'yoasobi', fn($query) => $query->where('target_gender', $gender))
                ->whereExists(function ($sub) {
                    $sub->from('jobs')
                        ->join('shops', 'jobs.shop_id', '=', 'shops.id')
                        ->whereColumn('jobs.job_type_id', 'job_types.id')
                        ->where('jobs.status', 'active')
                        ->where('shops.status', 'active');
                })
                ->pluck('name')
                ->toArray();
        });

        $filtered = collect($jobTypeNames)->filter(fn($name) => mb_strpos($name, $q) !== false)->values();

        // 店舗名（yoasobi / 業種・店名入力想定）
        if ($gender === 'yoasobi' && $filtered->count() < 5) {
            $shops = Shop::where('name', 'LIKE', "%{$q}%")
                ->where('status', 'active')
                ->limit(5 - $filtered->count())
                ->pluck('name');
            $filtered = $filtered->merge($shops)->values();
        }

        return response()->json($filtered->take(8));
    }
}
