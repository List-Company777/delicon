<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prefecture;
use App\Models\Shop;
use Illuminate\Http\Request;

class PlanApplicationController extends Controller
{
    public function index(Request $request)
    {
        $prefId = $request->query('pref');
        $plan   = $request->query('plan');
        $status = $request->query('status');
        $name   = $request->query('name');

        $query = Shop::with(['area.prefecture', 'prefecture'])
            ->when($prefId, fn($q) => $q->where('prefecture_id', $prefId))
            ->when($plan !== null && $plan !== '', fn($q) => $q->where('plan', $plan))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($name, fn($q) => $q->where('name', 'like', '%' . $name . '%'))
            ->orderBy('plan')
            ->orderBy('id');

        $shops       = $query->paginate(50)->withQueryString();
        $prefectures = Prefecture::orderBy('id')->get();

        $planLabels = [
            1 => ['label' => 'VIP',       'color' => 'bg-yellow-100 text-yellow-700'],
            2 => ['label' => 'ミドル',    'color' => 'bg-purple-100 text-purple-700'],
            3 => ['label' => 'ベーシック', 'color' => 'bg-blue-100 text-blue-700'],
            4 => ['label' => '無料上位',  'color' => 'bg-green-100 text-green-700'],
            5 => ['label' => '無料',      'color' => 'bg-gray-100 text-gray-500'],
        ];

        return view('admin.plan-applications.index', compact(
            'shops', 'prefectures', 'planLabels', 'prefId', 'plan', 'status', 'name'
        ));
    }
}
