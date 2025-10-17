<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage examples:
     * ->middleware('role:admin')
     * ->middleware('role:service_worker,admin')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Check authentication first
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Allow all if no specific roles provided
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user's role matches allowed roles
        if (!in_array($user->role, $roles)) {
            return response()->json(['message' => 'Forbidden - insufficient permissions'], 403);
        }

        return $next($request);
    }
}
