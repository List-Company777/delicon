<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint'   => ['required', 'string', 'max:1000'],
            'publicKey'  => ['required', 'string', 'max:500'],
            'authToken'  => ['required', 'string', 'max:100'],
            'encoding'   => ['nullable', 'string', 'max:20'],
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'public_key'       => $data['publicKey'],
                'auth_token'       => $data['authToken'],
                'content_encoding' => $data['encoding'] ?? 'aesgcm',
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request)
    {
        $request->validate(['endpoint' => ['required', 'string']]);
        PushSubscription::where('endpoint', $request->endpoint)->delete();
        return response()->json(['ok' => true]);
    }
}
