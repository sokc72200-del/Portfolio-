<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        foreach ($roles as $role) {
            $expectedRole = Role::tryFrom($role);

            if ($expectedRole !== null && $user->hasRole($expectedRole)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
