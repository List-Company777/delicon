<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\TrailingSlash::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
        $middleware->alias([
            'admin'          => \App\Http\Middleware\AdminOnly::class,
            'admin.ip'       => \App\Http\Middleware\AdminIpRestrict::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/line/webhook/',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
