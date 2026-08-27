<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasWfhSchedule
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $event = \App\Models\Event::where('hris_number', auth()->user()->hris_number)->whereDate('start', now()->format('Y-m-d'))->first();
        if ($event?->tag == \App\Enums\Events::WFH) {
            return $next($request);
        }

        return abort(403);
    }
}
