<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict access to routes based on the authenticated user's role.
 *
 * Usage in routes:
 *   ->middleware(['auth', 'role:admin,manager'])
 */
class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'Accès refusé — rôle insuffisant.');
        }

        return $next($request);
    }
}
