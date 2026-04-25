<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Mail\ApplicationMessageToApplicant;
use App\Mail\ApplicationRejected;
use App\Models\Application;
use App\Models\ApplicationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApplicationController extends Controller
{
    private function currentShop(): ?\App\Models\Shop
    {
        $user = auth()->user();
        if ($user->isPartner() && session()->has('acting_shop_id')) {
            return \App\Models\Shop::find(session('acting_shop_id'));
        }

        $managingId = session('managing_shop_id');
        if ($managingId) {
            $shop = $user->shops()->wherePivot('role', 'owner')->where('shops.id', $managingId)->first();
            if ($shop) return $shop;
        }

        $shop = $user->shops()->wherePivot('role', 'owner')->first();
        if ($shop) session(['managing_shop_id' => $shop->id]);
        return $shop;
    }

    public function index()
    {
        $shop = $this->currentShop();
        if (! $shop) {
            return redirect()->route('manage.dashboard');
        }

        $applications = Application::with(['job', 'messages'])
            ->where('shop_id', $shop->id)
            ->latest()
            ->paginate(20);

        return view('manage.applications.index', compact('applications'));
    }

    public function show(int $id)
    {
        $shop = $this->currentShop();
        if (! $shop) {
            return redirect()->route('manage.dashboard');
        }

        $application = Application::with(['job', 'shop', 'messages'])
            ->where('shop_id', $shop->id)
            ->findOrFail($id);

        // 応募者からのメッセージを既読にする
        $application->messages()
            ->where('sender', 'applicant')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('manage.applications.show', compact('application'));
    }

    public function message(Request $request, int $id)
    {
        $shop = $this->currentShop();
        if (! $shop) {
            return redirect()->route('manage.dashboard');
        }

        $application = Application::with(['job', 'shop'])
            ->where('shop_id', $shop->id)
            ->findOrFail($id);

        $request->validate(['body' => 'required|string|max:2000']);

        $msg = $application->messages()->create([
            'sender' => 'shop',
            'body'   => $request->body,
        ]);

        // 応募者にメール通知
        try {
            Mail::to($application->applicant_email, $application->applicant_name)
                ->send(new ApplicationMessageToApplicant($application, $msg));
        } catch (\Exception) {}

        // ステータスを「連絡済み」に更新（newの場合のみ）
        if ($application->status === 'new') {
            $application->update(['status' => 'contacted']);
        }

        return redirect()->route('manage.applications.show', $id)
            ->with('sent', true);
    }

    public function updateStatus(Request $request, int $id)
    {
        $shop = $this->currentShop();
        if (! $shop) {
            return redirect()->route('manage.dashboard');
        }

        $application = Application::with(['shop'])->where('shop_id', $shop->id)->findOrFail($id);

        $request->validate([
            'status' => 'required|in:new,contacted,rejected,hired',
        ]);

        $previousStatus = $application->status;
        $application->update(['status' => $request->status]);

        // 不採用に変更した場合のみ応募者にお断りメール送信
        if ($request->status === 'rejected' && $previousStatus !== 'rejected') {
            try {
                Mail::to($application->applicant_email, $application->applicant_name)
                    ->send(new ApplicationRejected($application));
            } catch (\Exception $e) {
                Log::error("ApplicationRejected mail failed: application={$id} - {$e->getMessage()}");
            }
        }

        return redirect()->route('manage.applications.show', $id)
            ->with('status_updated', $request->status === 'rejected' ? 'rejected' : true);
    }
}
