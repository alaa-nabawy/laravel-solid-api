<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\PublicRateLimitMiddleware;
use App\Http\Middleware\AuthenticatedRateLimitMiddleware;
use App\Http\Middleware\AdminRateLimitMiddleware;
use App\Http\Middleware\SecureHeaders;
use App\Http\Middleware\RequestId;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register Inertia middleware for web routes
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Register middlewares for API routes (prepend to ensure they run for all API routes)
        $middleware->api(prepend: [
            SecureHeaders::class,
            RequestId::class,
        ]);

        // Register rate limiting middleware
        $middleware->alias([
            'throttle.public' => PublicRateLimitMiddleware::class,
            'throttle.auth' => AuthenticatedRateLimitMiddleware::class,
            'throttle.admin' => AdminRateLimitMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
