<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminRateLimitMiddleware
{
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'general'): Response
    {
        $user = $request->user();

        // Check if user is admin
        if (! $this->isAdmin($user)) {
            return response()->json([
                'error'   => 'Forbidden',
                'message' => 'Admin access required.',
            ], 403);
        }

        $key    = $this->resolveRequestSignature($request, $user, $type);
        $config = $this->getConfig($type, $request->method());

        if ($this->limiter->tooManyAttempts($key, $config['max_attempts'])) {
            $this->logViolation($request, $user, $key, $type);

            return $this->buildResponse($key, $config);
        }

        $this->limiter->hit($key, $config['decay_minutes'] * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $config['max_attempts'],
            $this->limiter->retriesLeft($key, $config['max_attempts']),
            $this->limiter->availableIn($key)
        );
    }

    /**
     * Resolve the rate limiting key for the request.
     */
    protected function resolveRequestSignature(Request $request, ?object $user, string $type): string
    {
        return "admin_rate_limit:{$type}:{$user->id}";
    }

    /**
     * Get rate limiting configuration for admin endpoints.
     *
     * @return array{max_attempts: int, decay_minutes: int}
     */
    protected function getConfig(string $type, string $method): array
    {
        $config       = config('rate_limits.admin', []);
        $typeConfig   = $config[$type] ?? $config['general'] ?? [];
        $maxAttempts  = $typeConfig[strtolower($method)] ?? $typeConfig['get'] ?? 100;
        $decayMinutes = config('rate_limits.decay_minutes', 1);

        return [
            'max_attempts'  => $maxAttempts,
            'decay_minutes' => $decayMinutes,
        ];
    }

    /**
     * Check if user is admin.
     */
    protected function isAdmin(?object $user): bool
    {
        if (! $user) {
            return false;
        }

        return isset($user->role) && in_array($user->role, ['admin', 'administrator']) ||
               isset($user->is_admin) && $user->is_admin ||
               method_exists($user, 'hasRole') && $user->hasRole('admin');
    }

    /**
     * Build the rate limit exceeded response.
     *
     * @param array{max_attempts: int, decay_minutes: int} $config
     */
    protected function buildResponse(string $key, array $config): JsonResponse
    {
        $retryAfter = $this->limiter->availableIn($key);

        return response()->json([
            'error'       => 'Too Many Requests',
            'message'     => 'Admin rate limit exceeded. Please try again later.',
            'retry_after' => $retryAfter,
            'limit'       => $config['max_attempts'],
            'type'        => 'admin_rate_limit',
        ], 429);
    }

    /**
     * Add rate limit headers to the response.
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts, int $retryAfter): Response
    {
        if (config('rate_limits.add_headers', true)) {
            $response->headers->add([
                'X-RateLimit-Limit'     => $maxAttempts,
                'X-RateLimit-Remaining' => max(0, $remainingAttempts),
                'X-RateLimit-Reset'     => now()->addSeconds($retryAfter)->timestamp,
            ]);
        }

        return $response;
    }

    /**
     * Log rate limit violation.
     */
    protected function logViolation(Request $request, ?object $user, string $key, string $type): void
    {
        if (config('rate_limits.log_violations', true)) {
            Log::warning('Admin rate limit exceeded', [
                'user_id'    => $user->id,
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'path'       => $request->path(),
                'method'     => $request->method(),
                'type'       => $type,
                'key'        => $key,
            ]);
        }
    }
}
