<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\AreaAddressMapping;
use App\Models\JobType;
use App\Models\Prefecture;
use App\Services\LpNormalizationService;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function index()
    {
        $jobTypes        = JobType::orderBy('sort_order')->orderBy('id')->get();
        $addressMappings = AreaAddressMapping::with('area.prefecture')
            ->orderByRaw('area_id IS NOT NULL')  // 未解決を先頭に
            ->orderBy('updated_at', 'desc')
            ->get();
        $areas = Area::with('prefecture')->orderBy('prefecture_id')->orderBy('sort_order')->get();
        return view('admin.master.index', compact('jobTypes', 'addressMappings', 'areas'));
    }

    public function updateAddressMapping(Request $request, int $id)
    {
        $request->validate([
            'area_id' => ['nullable', 'exists:areas,id'],
        ]);

        $mapping = AreaAddressMapping::findOrFail($id);
        $mapping->update(['area_id' => $request->input('area_id') ?: null]);

        return back()->with('success', "「{$mapping->keyword}」のエリアマッピングを更新しました");
    }

    public function deleteAddressMapping(int $id)
    {
        AreaAddressMapping::findOrFail($id)->delete();
        return back()->with('success', 'マッピングを削除しました');
    }

    public function storeArea(Request $request)
    {
        $request->validate([
            'prefecture_id' => ['required', 'exists:prefectures,id'],
            'name'          => ['required', 'string', 'max:50'],
            'slug'          => ['required', 'string', 'max:50', 'unique:areas,slug', 'regex:/^[a-z0-9\-]+$/'],
            'parent_id'     => ['nullable', 'exists:areas,id'],
        ]);

        $area = Area::create([
            'prefecture_id' => $request->prefecture_id,
            'parent_id'     => $request->input('parent_id') ?: null,
            'name'          => $request->name,
            'slug'          => $request->slug,
            'sort_order'    => Area::max('sort_order') + 1,
        ]);

        $area->load('prefecture');
        $count = (new LpNormalizationService)->generateForArea($area);

        return back()->with('success', "エリア「{$request->name}」を追加しました（LP正規化 {$count} 件生成）");
    }

    public function storeJobType(Request $request)
    {
        $request->validate([
            'name'           => ['required', 'string', 'max:50'],
            'slug'           => ['required', 'string', 'max:50', 'unique:job_types,slug', 'regex:/^[a-z0-9\-]+$/'],
            'target_gender'  => ['required', 'in:male,female,both'],
            'role_type'      => ['required', 'in:cast,staff,both'],
            'group_slug'     => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9\-]*$/'],
            'keyword_filter' => ['nullable', 'string', 'max:100'],
        ]);

        JobType::create([
            'name'           => $request->name,
            'slug'           => $request->slug,
            'target_gender'  => $request->target_gender,
            'role_type'      => $request->role_type,
            'group_slug'     => $request->group_slug ?: null,
            'keyword_filter' => $request->keyword_filter ?: null,
            'sort_order'     => JobType::max('sort_order') + 1,
        ]);

        return back()->with('success', "職種「{$request->name}」を追加しました");
    }

    public function updateJobType(Request $request, int $id)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:50'],
            'target_gender'  => ['required', 'in:male,female,both'],
            'role_type'      => ['required', 'in:cast,staff,both'],
            'group_slug'     => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9\-]*$/'],
            'keyword_filter' => ['nullable', 'string', 'max:100'],
            'sort_order'     => ['required', 'integer', 'min:0', 'max:255'],
        ]);

        $data['group_slug']     = $data['group_slug'] ?: null;
        $data['keyword_filter'] = $data['keyword_filter'] ?: null;

        $jt = JobType::findOrFail($id);
        $jt->update($data);

        return back()->with('success', "「{$jt->name}」を更新しました");
    }
}
