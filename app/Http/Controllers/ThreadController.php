<?php

namespace App\Http\Controllers;

use App\Mail\ApplicationMessageToShop;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ThreadController extends Controller
{
    public function show(string $token)
    {
        $application = Application::with(['job', 'shop', 'messages'])
            ->where('reply_token', $token)
            ->firstOrFail();

        // 店舗からのメッセージを既読にする（応募者が開いたとき）
        $application->messages()
            ->where('sender', 'shop')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('apply.thread', compact('application', 'token'));
    }

    public function store(Request $request, string $token)
    {
        $application = Application::with(['job', 'shop'])
            ->where('reply_token', $token)
            ->firstOrFail();

        $request->validate(['body' => 'required|string|max:2000']);

        $msg = $application->messages()->create([
            'sender' => 'applicant',
            'body'   => $request->body,
        ]);

        // 店舗オーナーにメール通知
        try {
            $owner = $application->shop->users()->first();
            if ($owner) {
                Mail::to($owner->email, $owner->name)
                    ->send(new ApplicationMessageToShop($application, $msg));
            }
        } catch (\Exception) {}

        return redirect()->route('apply.thread', $token)
            ->with('sent', true);
    }
}
