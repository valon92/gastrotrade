<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.role' => \App\Http\Middleware\CheckAdminRole::class,
            'admin.user' => \App\Http\Middleware\EnsureAdminUser::class,
            'no-cache-spa' => \App\Http\Middleware\NoCacheForSPA::class,
        ]);
        // Sanctum “stateful” SPA + /api/*: CSRF shpesh refuzon POST (403/419) kur meta-token nuk përputhet me session.
        // API përdor Bearer në header; CSRF për /api nuk është e nevojshme kundër faqeve të tjera (SOP).
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
