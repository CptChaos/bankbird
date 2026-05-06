<?php

namespace App\Http\Middleware;

use App\Support\Demo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Demo::active()) {
            return $next($request);
        }

        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return $next($request);
        }

        if ($request->routeIs('filament.admin.auth.*')) {
            return $next($request);
        }

        if ($request->hasHeader('X-Livewire')) {
            return $next($request);
        }

        return redirect()->back();
    }
}
