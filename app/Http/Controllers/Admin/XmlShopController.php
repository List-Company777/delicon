<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class XmlShopController extends Controller
{
    public function index(Request $request)
    {
        $filter  = $request->query('filter', 'all'); // all | no_account
        $source  = $request->query('source', '');
        $keyword = $request->query('keyword', '');

        $query = Shop::whereNotNull('xml_source')
            ->with(['genre:id,name', 'prefecture:id,prefecture', 'area:id,name', 'users:id,name,email'])
            ->withCount('users');

        if ($filter === 'no_account') {
            $query->whereDoesntHave('users');
        }
        if ($source !== '') {
            $query->where('xml_source', $source);
        }
        if ($keyword !== '') {
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        $shops = $query->orderBy('xml_source')->orderBy('name')->paginate(50)->withQueryString();

        $totalCount     = Shop::whereNotNull('xml_source')->count();
        $noAccountCount = Shop::whereNotNull('xml_source')->whereDoesntHave('users')->count();
        $sources        = Shop::whereNotNull('xml_source')->distinct()->orderBy('xml_source')->pluck('xml_source');

        return view('admin.xml-shops.index', compact('shops', 'filter', 'source', 'keyword', 'totalCount', 'noAccountCount', 'sources'));
    }
}
