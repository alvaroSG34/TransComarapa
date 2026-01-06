<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * Verifica el email solo si el usuario tiene rol 'Cliente'
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Solo verificar email para usuarios con rol 'Cliente'
        if ($user && $user->rol === 'Cliente') {
            if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
                return $request->expectsJson()
                    ? abort(403, 'Tu dirección de correo electrónico no está verificada.')
                    : Redirect::route('verification.notice');
            }
        }

        return $next($request);
    }
}
