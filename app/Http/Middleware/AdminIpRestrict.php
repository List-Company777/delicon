<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminIpRestrict
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = config('admin.allowed_ips', []);

        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps, true)) {
            abort(403);
        }

        return $next($request);
    }
}
