<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ResendWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // トークン認証
        if ($request->query('token') !== config('services.resend.webhook_secret')) {
            abort(403);
        }

        $type  = $request->input('type');
        $email = $request->input('data.email_id')
            ? null
            : $request->input('data.to.0');

        // Resend のペイロード構造: data.to は配列
        if (!$email) {
            $to = $request->input('data.to');
            $email = is_array($to) ? ($to[0] ?? null) : $to;
        }

        if (!$email) {
            Log::warning('ResendWebhook: email not found in payload', $request->all());
            return response()->json(['ok' => true]);
        }

        if (in_array($type, ['email.bounced', 'email.complained'], true)) {
            User::where('email', $email)
                ->whereNull('email_bounced_at')
                ->update(['email_bounced_at' => now()]);

            Log::info("ResendWebhook: marked bounce for {$email} (type={$type})");
        }

        return response()->json(['ok' => true]);
    }
}
