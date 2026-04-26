<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchPageViewController extends Controller
{
    public function index(Request $request)
    {
        $days   = $request->integer('days', 30);
        $days   = in_array($days, [7, 30, 90]) ? $days : 30;
        $gender = $request->input('gender', 'all');

        $query = DB::table('search_page_views as v')
            ->selectRaw('v.gender, v.area_slug, v.job_slug, SUM(v.count) as total, a.name as area_name, j.name as job_name')
            ->leftJoin('areas as a', 'a.slug', '=', 'v.area_slug')
            ->leftJoin('job_types as j', 'j.slug', '=', 'v.job_slug')
            ->whereRaw('v.date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)', [$days])
            ->groupBy('v.gender', 'v.area_slug', 'v.job_slug', 'a.name', 'j.name')
            ->orderByDesc('total')
            ->limit(300);

        if ($gender !== 'all') {
            $query->where('v.gender', $gender);
        }

        $rows = $query->get();

        $totalPv = $rows->sum('total');

        return view('admin.search-page-views.index', compact('rows', 'days', 'gender', 'totalPv'));
    }
}
