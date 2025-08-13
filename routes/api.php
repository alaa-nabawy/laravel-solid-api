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

// Public API routes with tight rate limiting
Route::prefix('v1')->middleware('throttle.public:auth')->group(function () {
    // Authentication routes (public) - very restrictive
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/signup', [AuthController::class, 'signup']);
});

// Public general API routes
Route::prefix('v1')->middleware('throttle.public:general')->group(function () {
    // Public endpoints with moderate restrictions
    Route::get('/public/info', function () {
        return response()->json(['message' => 'Public information']);
    });
});

// Public search endpoints
Route::prefix('v1')->middleware('throttle.public:search')->group(function () {
    Route::get('/search', function () {
        return response()->json(['results' => []]);
    });
});

// Authentication required routes with role-based rate limiting
Route::middleware(['auth:api', 'throttle.auth:general'])->group(function () {
    Route::prefix('v1')->group(function () {
        // Protected authentication routes
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // General authenticated endpoints
        Route::get('/profile', function (Request $request) {
            return $request->user();
        });
    });

    // Legacy route for backward compatibility
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Search endpoints for authenticated users
Route::middleware(['auth:api', 'throttle.auth:search'])->group(function () {
    Route::prefix('v1')->group(function () {
        Route::get('/search/advanced', function () {
            return response()->json(['results' => []]);
        });
    });
});

// Upload endpoints for authenticated users
Route::middleware(['auth:api', 'throttle.auth:upload'])->group(function () {
    Route::prefix('v1')->group(function () {
        Route::post('/upload', function () {
            return response()->json(['message' => 'File uploaded']);
        });
    });
});

// Heavy operation endpoints for authenticated users
Route::middleware(['auth:api', 'throttle.auth:heavy'])->group(function () {
    Route::prefix('v1')->group(function () {
        Route::get('/reports/generate', function () {
            return response()->json(['message' => 'Report generated']);
        });
        Route::post('/export/data', function () {
            return response()->json(['message' => 'Data exported']);
        });
    });
});

// Admin routes with admin-specific rate limiting
Route::middleware(['auth:api', 'throttle.admin:general'])->group(function () {
    Route::prefix('v1/admin')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Admin dashboard']);
        });
    });
});

// Admin user management with stricter limits
Route::middleware(['auth:api', 'throttle.admin:users'])->group(function () {
    Route::prefix('v1/admin')->group(function () {
        Route::get('/users', function () {
            return response()->json(['users' => []]);
        });
        Route::post('/users', function () {
            return response()->json(['message' => 'User created']);
        });
        Route::put('/users/{id}', function ($id) {
            return response()->json(['message' => 'User updated']);
        });
        Route::delete('/users/{id}', function ($id) {
            return response()->json(['message' => 'User deleted']);
        });
    });
});

// Admin settings with very strict limits
Route::middleware(['auth:api', 'throttle.admin:settings'])->group(function () {
    Route::prefix('v1/admin')->group(function () {
        Route::get('/settings', function () {
            return response()->json(['settings' => []]);
        });
        Route::put('/settings', function () {
            return response()->json(['message' => 'Settings updated']);
        });
    });
});

// Admin logs with moderate limits
Route::middleware(['auth:api', 'throttle.admin:logs'])->group(function () {
    Route::prefix('v1/admin')->group(function () {
        Route::get('/logs', function () {
            return response()->json(['logs' => []]);
        });
    });
});

// Admin reports with strict limits
Route::middleware(['auth:api', 'throttle.admin:reports'])->group(function () {
    Route::prefix('v1/admin')->group(function () {
        Route::get('/reports', function () {
            return response()->json(['reports' => []]);
        });
        Route::post('/reports/generate', function () {
            return response()->json(['message' => 'Admin report generated']);
        });
    });
});
