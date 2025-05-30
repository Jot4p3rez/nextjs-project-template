<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // If user is admin, allow access to everything
        if ($request->user()->isAdmin()) {
            return $next($request);
        }

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        // If user doesn't have any of the required roles
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'No tiene permisos para acceder a este recurso.',
            ], 403);
        }

        return redirect()->back()->with('error', 'No tiene permisos para acceder a esta sección.');
    }

    /**
     * Get the path the user should be redirected to when they do not have access.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo(Request $request): string
    {
        return route('dashboard');
    }
}
