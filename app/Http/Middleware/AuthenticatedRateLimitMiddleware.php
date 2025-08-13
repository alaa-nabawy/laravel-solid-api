<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatedRateLimitMiddleware
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
        $user   = $request->user();
        $key    = $this->resolveRequestSignature($request, $user, $type);
        $config = $this->getConfig($type, $request->method(), $user);

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
        $userId = $user ? $user->id : 'guest';

        return "auth_rate_limit:{$type}:{$userId}";
    }

    /**
     * Get rate limiting configuration based on type, method, and user role.
     *
     * @return array{max_attempts: int, decay_minutes: int}
     */
    protected function getConfig(string $type, string $method, ?object $user): array
    {
        $userRole = $this->getUserRole($user);

        $config         = config('rate_limits.authenticated', []);
        $typeConfig     = $config[$type] ?? $config['general'] ?? [];
        $userTypeConfig = $typeConfig[$userRole] ?? $typeConfig['authenticated'] ?? [];
        $maxAttempts    = $userTypeConfig[strtolower($method)] ?? $userTypeConfig['get'] ?? 100;
        $decayMinutes   = config('rate_limits.decay_minutes', 1);

        return [
            'max_attempts'  => $maxAttempts,
            'decay_minutes' => $decayMinutes,
        ];
    }

    /**
     * Determine user role.
     */
    protected function getUserRole(?object $user): string
    {
        if (! $user) {
            return 'authenticated';
        }

        // Check for admin role
        if ($this->isAdmin($user)) {
            return 'admin';
        }

        // Check for premium role
        if ($this->isPremium($user)) {
            return 'premium';
        }

        return 'authenticated';
    }

    /**
     * Check if user is admin.
     */
    protected function isAdmin(?object $user): bool
    {
        return isset($user->role) && in_array($user->role, ['admin', 'administrator']) ||
               isset($user->is_admin) && $user->is_admin ||
               method_exists($user, 'hasRole') && $user->hasRole('admin');
    }

    /**
     * Check if user is premium.
     */
    protected function isPremium(?object $user): bool
    {
        return isset($user->role) && in_array($user->role, ['premium', 'pro', 'paid']) ||
               isset($user->is_premium) && $user->is_premium ||
               method_exists($user, 'hasRole') && $user->hasRole('premium');
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
            'message'     => 'Rate limit exceeded for authenticated endpoints. Please try again later.',
            'retry_after' => $retryAfter,
            'limit'       => $config['max_attempts'],
            'type'        => 'authenticated_rate_limit',
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
            Log::warning('Authenticated rate limit exceeded', [
                'user_id' => $user ? $user->id : null,
                'ip'      => $request->ip(),
                'path'    => $request->path(),
                'method'  => $request->method(),
                'type'    => $type,
                'key'     => $key,
            ]);
        }
    }
}
