<?php

namespace App\Http\Controllers\Manage;

use App\Models\Job;
use App\Models\JobType;
use App\Services\ImageService;
use Illuminate\Http\Request;

class StaffJobController extends BaseController
{
    public function index()
    {
        $shop = $this->shopOrFail();
        $allStaff = $shop->jobs->filter(fn($j) => in_array($j->jobType?->role_type, ['staff', 'both']));

        // XML連携求人と手動登録求人を分離
        $xmlStaffJobs  = $allStaff->filter(fn($j) => $j->xml_source !== 'manual')->values();
        $ownStaffJobs  = $allStaff->filter(fn($j) => $j->xml_source === 'manual')->values();

        // プラン上限は手動登録求人のみカウント（XML求人は上限外）
        $staffJobs = $ownStaffJobs;

        return view('manage.staff.index', compact('shop', 'staffJobs', 'xmlStaffJobs', 'ownStaffJobs'));
    }

    public function create()
    {
        $shop      = $this->shopOrFail();
        $staffJobs = $shop->jobs->filter(fn($j) => in_array($j->jobType?->role_type, ['staff', 'both']) && $j->xml_source === 'manual');
        $limit     = $shop->staffJobLimit();

        if ($staffJobs->count() >= $limit) {
            return redirect()->route('manage.staff.index')
                ->with('error', 'スタッフ求人は現在のプランでは最大' . $limit . '件まで登録できます。');
        }

        $job      = null;
        $jobTypes = JobType::whereIn('role_type', ['staff', 'both'])->orderBy('sort_order')->get();
        return view('manage.staff.form', compact('shop', 'job', 'jobTypes'));
    }

    public function store(Request $request)
    {
        $shop      = $this->shopOrFail();
        $staffJobs = $shop->jobs->filter(fn($j) => in_array($j->jobType?->role_type, ['staff', 'both']) && $j->xml_source === 'manual');
        $limit     = $shop->staffJobLimit();

        if ($staffJobs->count() >= $limit) {
            return redirect()->route('manage.staff.index')
                ->with('error', 'スタッフ求人は現在のプランでは最大' . $limit . '件まで登録できます。');
        }

        $data = $this->validated($request);
        $data['faq'] = collect($data['faq'] ?? [])->filter(fn($item) => !empty($item['q']) && !empty($item['a']))->values()->all() ?: null;

        $jobType = JobType::find($data['job_type_id']);

        $job = Job::create(array_merge($data, [
            'shop_id'       => $shop->id,
            'area_id'       => $shop->area_id,
            'prefecture_id' => $shop->prefecture_id,
            'search_group'  => $jobType->target_gender,
            'published_at'  => $request->status === 'active' ? now() : null,
        ]));

        if ($request->hasFile('image')) {
            $imagePath = (new ImageService)->saveJobImage($request->file('image'), $shop->id, $job->id);
            $job->update(['image_path' => $imagePath]);
        }

        return redirect()->route('manage.staff.index')->with('success', 'スタッフ求人を追加しました');
    }

    public function edit(int $id)
    {
        $shop = $this->shopOrFail();
        $job  = $shop->jobs->where('id', $id)
                           ->filter(fn($j) => in_array($j->jobType?->role_type, ['staff', 'both']))
                           ->firstOrFail();

        abort_if($job->xml_source !== 'manual', 403, 'XML連携求人は編集できません');

        $jobTypes = JobType::whereIn('role_type', ['staff', 'both'])->orderBy('sort_order')->get();
        return view('manage.staff.form', compact('shop', 'job', 'jobTypes'));
    }

    public function update(Request $request, int $id)
    {
        $shop = $this->shopOrFail();
        $job  = $shop->jobs->where('id', $id)
                           ->filter(fn($j) => in_array($j->jobType?->role_type, ['staff', 'both']))
                           ->firstOrFail();

        abort_if($job->xml_source !== 'manual', 403, 'XML連携求人は編集できません');
        $data = $this->validated($request);
        $data['faq'] = collect($data['faq'] ?? [])->filter(fn($item) => !empty($item['q']) && !empty($item['a']))->values()->all() ?: null;
        $imageService = new ImageService;

        if ($request->hasFile('image')) {
            if ($job->image_path) {
                $imageService->deleteJobImage($shop->id, $job->id);
            }
            $data['image_path'] = $imageService->saveJobImage($request->file('image'), $shop->id, $job->id);
        } elseif ($request->boolean('delete_image') && $job->image_path) {
            $imageService->deleteJobImage($shop->id, $job->id);
            $data['image_path'] = null;
        }

        $jobType = JobType::find($data['job_type_id']);

        $job->update(array_merge($data, [
            'search_group' => $jobType->target_gender,
            'published_at' => $request->status === 'active'
                                ? ($job->published_at ?? now())
                                : $job->published_at,
        ]));

        return redirect()->route('manage.staff.index')->with('success', 'スタッフ求人を更新しました');
    }

    public function destroy(int $id)
    {
        $shop = $this->shopOrFail();
        $job  = $shop->jobs->where('id', $id)
                           ->filter(fn($j) => in_array($j->jobType?->role_type, ['staff', 'both']))
                           ->firstOrFail();

        abort_if($job->xml_source !== 'manual', 403, 'XML連携求人は削除できません');

        if ($job->image_path) {
            (new ImageService)->deleteJobImage($shop->id, $job->id);
        }
        $job->delete();
        return redirect()->route('manage.staff.index')->with('success', 'スタッフ求人を削除しました');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'job_type_id'     => ['required', 'exists:job_types,id'],
            'title'           => ['required', 'string', 'max:60'],
            'description'     => ['nullable', 'string', 'max:3000'],
            'faq'             => ['nullable', 'array', 'max:3'],
            'faq.*.q'         => ['nullable', 'string', 'max:100'],
            'faq.*.a'         => ['nullable', 'string', 'max:300'],
            'wage_type'       => ['nullable', 'in:hourly,daily,monthly,commission'],
            'hourly_wage_min' => ['nullable', 'integer', 'min:0'],
            'hourly_wage_max' => ['nullable', 'integer', 'min:0', 'gte:hourly_wage_min'],
            'working_hours'   => ['nullable', 'string', 'max:100'],
            'employment_type' => ['nullable', 'in:PART_TIME,CONTRACTOR,FULL_TIME,PER_DIEM,OTHER'],
            'status'          => ['required', 'in:active,inactive,draft'],
            'image'           => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);
    }
}
