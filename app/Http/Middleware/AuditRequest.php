<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Throwable;

class AuditRequest
{
    /**
     * Track user actions. By default logs only mutating requests (POST/PUT/PATCH/DELETE).
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $method = strtoupper($request->getMethod());
        $isMutating = in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true);

        if (! $isMutating) {
            return $next($request);
        }

        $user = $request->user();
        $route = $request->route();
        $routeName = $route?->getName();

        $payload = $request->except([
            'password',
            'password_confirmation',
            'current_password',
            '_token',
            'two_factor_code',
            'recovery_code',
        ]);

        try {
            $response = $next($request);

            AuditLog::create([
                'user_id' => $user?->id,
                'event' => 'request',
                'action' => $routeName,
                'route' => $routeName,
                'method' => $method,
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status_code' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
                'request' => [
                    'input' => $payload,
                ],
                'meta' => [
                    'route_uri' => $route?->uri(),
                ],
            ]);

            return $response;
        } catch (Throwable $e) {
            // Log failed attempts too (403/500 etc.)
            AuditLog::create([
                'user_id' => $user?->id,
                'event' => 'request.exception',
                'action' => $routeName,
                'route' => $routeName,
                'method' => $method,
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request' => [
                    'input' => $payload,
                ],
                'meta' => [
                    'route_uri' => $route?->uri(),
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ],
            ]);

            throw $e;
        }
    }
}

