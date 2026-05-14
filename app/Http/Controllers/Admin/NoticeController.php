<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminNoticeMail;
use App\Models\AdminNotice;
use App\Models\Prefecture;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = AdminNotice::latest()->paginate(20);
        return view('admin.notices.index', compact('notices'));
    }

    public function create()
    {
        $prefectures = Prefecture::orderBy('id')->get();
        return view('admin.notices.create', compact('prefectures'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:100'],
            'body'           => ['required', 'string', 'max:5000'],
            'target'         => ['required', 'in:all,active,inactive'],
            'filter_pref_id' => ['nullable', 'integer', 'exists:prefectures,id'],
            'filter_plan'    => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $notice = AdminNotice::create($validated);

        return redirect()->route('admin.notices.show', $notice)
            ->with('success', '下書きを保存しました。内容を確認して送信してください。');
    }

    public function show(AdminNotice $notice)
    {
        $targetCount = $this->getTargetUsers($notice)->count();
        return view('admin.notices.show', compact('notice', 'targetCount'));
    }

    public function send(AdminNotice $notice)
    {
        if ($notice->isSent()) {
            return back()->withErrors(['error' => 'すでに送信済みです']);
        }

        $users = $this->getTargetUsers($notice);
        $count = 0;

        foreach ($users as $user) {
            Mail::to($user->email, $user->name)->queue(new AdminNoticeMail($notice));
            $count++;
        }

        $notice->update([
            'status'     => 'sent',
            'sent_at'    => now(),
            'sent_count' => $count,
        ]);

        return redirect()->route('admin.notices.index')
            ->with('success', "{$count}件のメールを送信キューに追加しました。");
    }

    private function getTargetUsers(AdminNotice $notice)
    {
        return User::whereHas('shops', function ($q) use ($notice) {
            $q->wherePivot('role', 'owner');
            if ($notice->target !== 'all') {
                $q->where('shops.status', $notice->target);
            }
            if ($notice->filter_pref_id) {
                $q->where('shops.prefecture_id', $notice->filter_pref_id);
            }
            if ($notice->filter_plan !== null) {
                $q->where('shops.plan', $notice->filter_plan);
            }
        })->where('role', '!=', 'admin')->get();
    }
}
