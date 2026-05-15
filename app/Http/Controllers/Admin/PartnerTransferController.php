<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Shop;
use Illuminate\Http\Request;

class PartnerTransferController extends Controller
{
    public function index(Request $request)
    {
        $filterPartner = $request->get('partner_filter', 'all');
        $keyword       = $request->get('keyword', '');

        $shops = Shop::with(['partner', 'genre', 'prefecture', 'area'])
            ->when($filterPartner === 'unassigned', fn($q) => $q->whereNull('partner_id'))
            ->when(
                $filterPartner !== 'all' && $filterPartner !== 'unassigned',
                fn($q) => $q->where('partner_id', (int) $filterPartner)
            )
            ->when($keyword, fn($q) => $q->where('name', 'like', "%{$keyword}%"))
            ->orderBy('id', 'desc')
            ->paginate(40)
            ->withQueryString();

        $partners = Partner::where('status', 'active')
            ->orderBy('company_name')
            ->get();

        $unassignedCount = Shop::whereNull('partner_id')->count();

        return view('admin.partner-transfer.index', compact(
            'shops', 'partners', 'filterPartner', 'keyword', 'unassignedCount'
        ));
    }
}
