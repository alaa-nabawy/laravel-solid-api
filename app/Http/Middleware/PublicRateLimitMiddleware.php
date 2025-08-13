<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PublicRateLimitMiddleware
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
        $key    = $this->resolveRequestSignature($request, $type);
        $config = $this->getConfig($type, $request->method());

        if ($this->limiter->tooManyAttempts($key, $config['max_attempts'])) {
            $this->logViolation($request, $key, $type);

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
    protected function resolveRequestSignature(Request $request, string $type): string
    {
        $ip        = $request->ip();
        $userAgent = substr(md5($request->userAgent() ?? ''), 0, 8);

        return "public_rate_limit:{$type}:{$ip}:{$userAgent}";
    }

    /**
     * Get rate limiting configuration based on type and method.
     *
     * @return array{max_attempts: int, decay_minutes: int}
     */
    protected function getConfig(string $type, string $method): array
    {
        $config       = config('rate_limits.public', []);
        $typeConfig   = $config[$type] ?? $config['general'] ?? [];
        $maxAttempts  = $typeConfig[strtolower($method)] ?? $typeConfig['get'] ?? 60;
        $decayMinutes = config('rate_limits.decay_minutes', 1);

        return [
            'max_attempts'  => $maxAttempts,
            'decay_minutes' => $decayMinutes,
        ];
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
            'message'     => 'Rate limit exceeded for public endpoints. Please try again later.',
            'retry_after' => $retryAfter,
            'limit'       => $config['max_attempts'],
            'type'        => 'public_rate_limit',
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
    protected function logViolation(Request $request, string $key, string $type): void
    {
        if (config('rate_limits.log_violations', true)) {
            Log::warning('Public rate limit exceeded', [
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
