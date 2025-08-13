<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status'    => 'ok',
        'timestamp' => now()->toISOString(),
        'version'   => config('app.version', '1.0.0'),
    ]);
});

// Public API routes
Route::prefix('v1')->group(function () {
    // Authentication routes (public)
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/signup', [AuthController::class, 'signup']);
});

// Authentication required routes
Route::middleware('auth:api')->group(function () {
    Route::prefix('v1')->group(function () {
        // Protected authentication routes
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
    });

    // Legacy route for backward compatibility
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
