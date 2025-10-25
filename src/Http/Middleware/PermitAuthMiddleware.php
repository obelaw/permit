<?php

namespace Obelaw\Permit\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Twist\Facades\Tenancy;
use Twist\Tenancy\DTO\TenantDTO;
use Symfony\Component\HttpFoundation\Response;

class PermitAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantModel = Filament::getTenant();

        $tenant = new TenantDTO(id: $tenantModel->id);
        Tenancy::initialize($tenant);

        if (!Filament::auth()->check() || !Filament::auth()->user()->authable || !Filament::auth()->user()->authable->is_active) {
            abort(403);
        }

        return $next($request);
    }
}
