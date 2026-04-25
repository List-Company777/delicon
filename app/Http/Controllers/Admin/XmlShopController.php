<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class XmlShopController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all'); // all | no_account

        $query = Shop::whereNotNull('xml_source')
            ->with(['genre:id,name', 'prefecture:id,name', 'area:id,name', 'users:id,name,email'])
            ->withCount('users');

        if ($filter === 'no_account') {
            $query->whereDoesntHave('users');
        }

        $shops = $query->orderBy('xml_source')->orderBy('name')->paginate(50)->withQueryString();

        $totalCount     = Shop::whereNotNull('xml_source')->count();
        $noAccountCount = Shop::whereNotNull('xml_source')->whereDoesntHave('users')->count();

        return view('admin.xml-shops.index', compact('shops', 'filter', 'totalCount', 'noAccountCount'));
    }
}
