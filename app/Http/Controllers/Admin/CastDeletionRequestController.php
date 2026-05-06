<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CastDeletionNotificationMail;
use App\Models\CastDeletionRequest;
use Illuminate\Support\Facades\Mail;

class CastDeletionRequestController extends Controller
{
    public function index()
    {
        $requests = CastDeletionRequest::with('cast')
            ->orderByRaw("FIELD(status,'pending','processed')")
            ->latest()
            ->paginate(30);

        return view('admin.deletion-requests.index', compact('requests'));
    }

    public function process(CastDeletionRequest $deletionRequest)
    {
        $cast      = $deletionRequest->cast;
        $castName  = $cast?->name ?? '（削除済み）';
        $requName  = $deletionRequest->requester_name;
        $requEmail = $deletionRequest->requester_email;

        if ($cast) {
            $cast->update(['status' => 'inactive']);
            CastDeletionRequest::where('cast_id', $cast->id)
                ->where('status', 'pending')
                ->update(['status' => 'processed', 'processed_at' => now()]);
        } else {
            $deletionRequest->update(['status' => 'processed', 'processed_at' => now()]);
        }

        try {
            Mail::to($requEmail)->send(new CastDeletionNotificationMail($castName, $requName));
        } catch (\Throwable) {
            // SMTP未設定時はスキップ
        }

        return back()->with('success', '「' . $castName . '」を非公開にしました');
    }
}
