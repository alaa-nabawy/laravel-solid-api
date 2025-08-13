<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            Route::middleware('test_route')
                ->prefix('test-route')
                ->group(base_path('routes/test-route.php'));

            Route::middleware('admin_auth')
                ->prefix('admin')
                ->group(base_path('routes/admin-panel.php'));})
    ->withMiddleware(function (Middleware $middleware): void {$middleware->group('test_route', [
            // Add your middleware here
        ]);
        $middleware->group('admin_auth', [
            // Add your middleware here
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
