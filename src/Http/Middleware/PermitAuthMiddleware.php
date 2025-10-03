<?php

namespace Obelaw\Permit\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermitAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Filament::auth()->check() || !Filament::auth()->user()->authable || !Filament::auth()->user()->authable->is_active) {
            throw new \Illuminate\Auth\AuthenticationException();
        }

        return $next($request);
    }
}
