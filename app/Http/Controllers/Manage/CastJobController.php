<?php

namespace App\Http\Controllers\Manage;

use App\Models\Job;
use App\Models\JobType;
use App\Services\ImageService;
use Illuminate\Http\Request;

class CastJobController extends BaseController
{
    protected array $shopWith = ['detail', 'genre', 'area', 'prefecture', 'jobs.jobType'];

    public function index()
    {
        $shop     = $this->shopOrFail();
        $castJobs = $shop->jobs->filter(fn($j) => in_array($j->jobType?->role_type, ['cast', 'both']))->values();
        return view('manage.cast.index', compact('shop', 'castJobs'));
    }

    public function create()
    {
        $shop     = $this->shopOrFail();
        $castJobs = $shop->jobs->filter(fn($j) => in_array($j->jobType?->role_type, ['cast', 'both']));

        if ($castJobs->count() >= $shop->castJobLimit()) {
            return redirect()->route('manage.cast.index')
                ->with('error', 'キャスト求人は現在のプランでは最大' . $shop->castJobLimit() . '件まで登録できます。');
        }

        $job      = null;
        $jobTypes = JobType::whereIn('role_type', ['cast', 'both'])->orderBy('sort_order')->get();
        return view('manage.cast.form', compact('shop', 'job', 'jobTypes'));
    }

    public function store(Request $request)
    {
        $shop     = $this->shopOrFail();
        $castJobs = $shop->jobs->filter(fn($j) => in_array($j->jobType?->role_type, ['cast', 'both']));

        if ($castJobs->count() >= $shop->castJobLimit()) {
            return redirect()->route('manage.cast.index')
                ->with('error', 'キャスト求人は現在のプランでは最大' . $shop->castJobLimit() . '件まで登録できます。');
        }

        $data = $this->validated($request);
        $data['faq'] = collect($data['faq'] ?? [])->filter(fn($item) => !empty($item['q']) && !empty($item['a']))->values()->all() ?: null;

        $job = Job::create(array_merge($data, [
            'shop_id'       => $shop->id,
            'area_id'       => $shop->area_id,
            'prefecture_id' => $shop->prefecture_id,
            'search_group'  => $shop->genre?->default_gender ?? 'female',
            'published_at'  => $request->status === 'active' ? now() : null,
        ]));

        if ($request->hasFile('image')) {
            $imagePath = (new ImageService)->saveJobImage($request->file('image'), $shop->id, $job->id);
            $job->update(['image_path' => $imagePath]);
        }

        return redirect()->route('manage.cast.index')->with('success', 'キャスト求人を追加しました');
    }

    public function edit(int $id)
    {
        $shop     = $this->shopOrFail();
        $job      = $shop->jobs->filter(fn($j) => in_array($j->jobType?->role_type, ['cast', 'both']))->find($id);
        abort_if(! $job, 404);
        $jobTypes = JobType::whereIn('role_type', ['cast', 'both'])->orderBy('sort_order')->get();
        return view('manage.cast.form', compact('shop', 'job', 'jobTypes'));
    }

    public function update(Request $request, int $id)
    {
        $shop = $this->shopOrFail();
        $job  = $shop->jobs->filter(fn($j) => in_array($j->jobType?->role_type, ['cast', 'both']))->find($id);
        abort_if(! $job, 404);

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

        $job->update(array_merge($data, [
            'search_group' => $shop->genre?->default_gender ?? 'female',
            'published_at' => $request->status === 'active'
                                ? ($job->published_at ?? now())
                                : $job->published_at,
        ]));

        return redirect()->route('manage.cast.index')->with('success', 'キャスト求人を更新しました');
    }

    public function destroy(int $id)
    {
        $shop = $this->shopOrFail();
        $job  = $shop->jobs->filter(fn($j) => in_array($j->jobType?->role_type, ['cast', 'both']))->find($id);
        abort_if(! $job, 404);
        if ($job->image_path) {
            (new ImageService)->deleteJobImage($shop->id, $job->id);
        }
        $job->delete();
        return redirect()->route('manage.cast.index')->with('success', 'キャスト求人を削除しました');
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
            'job_benefits'    => ['nullable', 'string', 'max:2000'],
            'insurance'       => ['nullable', 'string', 'max:200'],
            'preventsmoke'    => ['nullable', 'string', 'max:200'],
            'holiday'         => ['nullable', 'string', 'max:500'],
            'employment_type' => ['nullable', 'in:PART_TIME,CONTRACTOR,FULL_TIME,PER_DIEM,OTHER'],
            'status'          => ['required', 'in:active,inactive,draft'],
            'image'           => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120', 'dimensions:max_width=4000,max_height=4000'],
        ]);
    }
}
