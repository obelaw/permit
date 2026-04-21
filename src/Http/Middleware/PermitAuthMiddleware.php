<?php

namespace Obelaw\Permit\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Obelaw\Permit\Models\PermitUser;
use Symfony\Component\HttpFoundation\Response;

class PermitAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = Filament::auth()->user();

        if (!$authUser || !$authUser->permit) {
            abort(403, 'Unauthorized.');
        }

        if (oconfig()->get('obelaw.permit.suspend_all_users', false)) {
            $exceptions = oconfig()->get('obelaw.permit.suspend_all_users_exceptions', []);

            if (!in_array($authUser->email, (array) $exceptions)) {
                abort(403, 'All user accounts have been suspended. Please contact the administrator.');
            }
        }

        $permitUser = $authUser->permit;

        if ($permitUser->is_suspend !== null) {
            abort(403, 'Your account has been suspended. Please contact the administrator.');
        }

        if (
            $permitUser->last_active_at === null ||
            $permitUser->last_active_at->diffInMinutes(now()) >= 1
        ) {
            $permitUser->last_active_at = now();
            $permitUser->saveQuietly();
        }

        if (oconfig()->get('obelaw.permit.auto_suspend_inactive', false) && $permitUser->is_suspend === null) {
            $threshold = (int) oconfig()->get('obelaw.permit.auto_suspend_inactive_after_minutes', 60);

            PermitUser::whereNotNull('last_active_at')
                ->where('last_active_at', '<=', now()->subMinutes($threshold))
                ->whereNull('is_suspend')
                ->update(['is_suspend' => now()]);
        }

        return $next($request);
    }
}
