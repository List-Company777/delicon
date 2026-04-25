<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrailingSlash
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        // 静的ファイル・API・POST系は対象外（301するとPOSTデータが消えるため）
        if ($path === '/' || str_contains($path, '.') || str_starts_with($path, '/api/') || !$request->isMethod('GET')) {
            return $next($request);
        }

        // 末尾スラッシュなし → あり に 301リダイレクト
        if (!str_ends_with($path, '/')) {
            $query = $request->getQueryString();
            $url   = $path . '/' . ($query ? '?' . $query : '');
            return redirect($url, 301);
        }

        return $next($request);
    }
}
