<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        if (!$request->bearerToken()) {
            return $this->unauthenticated($request);
        }

        try {
            $user = auth('sanctum')->user();
            
            if (!$user) {
                return $this->unauthenticated($request);
            }

            // Add user to request for easy access in controllers
            $request->merge(['auth_user' => $user]);

            return $next($request);
        } catch (\Exception $e) {
            return $this->unauthenticated($request);
        }
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated(Request $request): Response
    {
        return response()->json([
            'error' => 'No autenticado.',
            'message' => 'Debe iniciar sesión para acceder a este recurso.',
        ], 401);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
