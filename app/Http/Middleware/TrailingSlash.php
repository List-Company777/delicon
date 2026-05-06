<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrailingSlash
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        if ($path === '/' || str_contains($path, '.') || str_starts_with($path, '/api/') || !$request->isMethod('GET')) {
            return $next($request);
        }

        if (!str_ends_with($path, '/')) {
            $query = $request->getQueryString();
            $url   = $request->getSchemeAndHttpHost() . $path . '/' . ($query ? '?' . $query : '');
            return redirect()->away($url, 301);
        }

        return $next($request);
    }
}
