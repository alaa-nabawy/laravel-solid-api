<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestId
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get existing request ID from header or generate a new UUID v4
        $existingRequestId = $request->header('X-Request-Id');

        // Validate if existing request ID is a valid UUID, otherwise generate new one
        $requestId = ($existingRequestId && Str::isUuid($existingRequestId))
            ? $existingRequestId
            : Str::uuid()->toString();

        // Add request ID to log context
        Log::withContext([
            'request_id' => $requestId,
        ]);

        $response = $next($request);

        // Add request ID to response headers
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
