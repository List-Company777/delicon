<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminIpRestrict
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = array_filter(array_map('trim', explode(',', env('ADMIN_ALLOWED_IPS', ''))));

        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps, true)) {
            abort(403);
        }

        return $next($request);
    }
}
