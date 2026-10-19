<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemakaian: ->middleware('role:pemilik') atau ->middleware('role:admin')
 * Pemilik mewarisi semua akses Admin, jadi selalu diloloskan.
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user, 401);

        if ($user->isPemilik()) {
            return $next($request);
        }

        abort_unless(
            in_array($user->role, $roles, true),
            403,
            'Anda tidak punya akses ke halaman ini.'
        );

        return $next($request);
    }
}
