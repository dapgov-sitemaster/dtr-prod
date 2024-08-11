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
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user()->role;
        if ($user == Role::SUPERADMIN) {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($user->value == $role) {
                return $next($request);
            }
        }

        return abort(403);
    }
}
