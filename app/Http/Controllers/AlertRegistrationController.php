<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\JobType;
use App\Models\WebAlertToken;
use Illuminate\Http\Request;

class AlertRegistrationController extends Controller
{
    public function show(Request $request)
    {
        $gender = in_array($request->query('gender'), ['female', 'male', 'both'], true)
            ? $request->query('gender')
            : 'female';

        $areas = Area::whereNull('parent_id')
            ->with('prefecture:id,prefecture')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'prefecture_id'])
            ->groupBy(fn($a) => $a->prefecture?->name ?? 'その他');

        // 条件タグ（keyword_filterあり）は除いた職種のみ
        $jobTypes = JobType::whereNull('group_slug')
            ->whereNull('keyword_filter')
            ->when($gender !== 'both', fn($q) => $q->where(fn($q2) =>
                $q2->where('target_gender', $gender)->orWhere('target_gender', 'both')
            ))
            ->orderBy('sort_order')
            ->get(['id', 'name', 'target_gender']);

        return view('alert.register', compact('gender', 'areas', 'jobTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'gender'          => ['required', 'in:female,male,both'],
            'area_id'         => ['required', 'exists:areas,id'],
            'job_type_id'     => ['nullable', 'exists:job_types,id'],
            'daily_pay_ok'    => ['nullable', 'boolean'],
            'inexperienced_ok'=> ['nullable', 'boolean'],
            'arubaito'        => ['nullable', 'boolean'],
        ]);

        $webToken = WebAlertToken::createFor(
            $validated['gender'],
            $validated['area_id'] ?? null,
            $validated['job_type_id'] ?? null,
            (bool) ($validated['daily_pay_ok'] ?? false),
            (bool) ($validated['inexperienced_ok'] ?? false),
            (bool) ($validated['arubaito'] ?? false),
        );

        return redirect()->route('alert.complete', ['token' => $webToken->token]);
    }

    public function complete(string $token)
    {
        $webToken = WebAlertToken::with(['area', 'jobType'])->where('token', $token)->firstOrFail();

        if ($webToken->isExpired()) {
            return redirect()->route('alert.register')->with('error', 'リンクの有効期限が切れました。もう一度設定してください。');
        }

        $lineUrl = 'https://line.me/R/oaMessage/' . config('services.line.bot_basic_id')
            . '/' . rawurlencode('ALERT-' . $token);

        $addFriendUrl = config('services.line.bot_add_friend_url');

        return view('alert.complete', compact('webToken', 'lineUrl', 'addFriendUrl'));
    }
}
