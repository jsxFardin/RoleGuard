<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequirePermission
{
    public function handle(Request $request, Closure $next, string $permission): mixed
    {
        $user = $request->user();

        if (! $user || ! $user->hasPermission($permission)) {
            abort(403);
        }

        return $next($request);
    }
}

