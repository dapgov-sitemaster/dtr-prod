<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCenter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $center): Response
    {
        // if ($center == 'DAPCC' && auth()->user()->employee->department->center == $center) {
        if ($center == 'TEST' && auth()->user()->employee->department->center == $center) {
            return $next($request);
        } else if ($center == 'PASIG' && auth()->user()->employee->department->center != 'TEST') {
            return $next($request);
        }
        abort(403);
    }
}
