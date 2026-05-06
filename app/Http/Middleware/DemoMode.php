<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! \App\Support\Demo::active()) {
            return $next($request);
        }

        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return $next($request);
        }

        if ($request->routeIs('filament.admin.auth.*')) {
            return $next($request);
        }

        if ($request->hasHeader('X-Livewire')) {
            return response()->json([
                'effects' => ['redirect' => url()->previous('/admin')],
            ]);
        }

        return redirect()->back();
    }
}
