<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission = null, ...$roles): Response
    {
        // user has employee role
        // tried to access admin coordinator panel

        dd($roles);
        $user = auth()->user();
        if ($user->role == Role::SUPERADMIN) {
            return $next($request);
        }

        $correct_role = false;

        foreach ($roles as $role) {
            if ($user->role->value == $role) {
                return $correct_role;
            }
        }

        if ($permission !== null) {
            if ($correct_role && !$user->can($permission)) {
                return abort(403);
            }
        } else if (!$correct_role) {
            return abort(403);
        }

        return $next($request);
    }
}
