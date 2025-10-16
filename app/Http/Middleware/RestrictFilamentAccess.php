<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictFilamentAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // if (auth()->check() && auth()->user()->role === 'call_agent') {
        //     return redirect('/orders/follow-up');
        // }

        // return $next($request);

        // Check if user is authenticated
        // if (!auth()->check()) {
        //     return redirect()->route('login'); // or abort(403)

        // }

        // Check if user has the call_agent role
        // if (auth()->user()->role !== 'call_agent') {
        //     // Deny access for non-call agents
        //     return abort(403, 'Unauthorized - Call Agent access only');
        // }

        // Allow access for call agents
        return $next($request);
    }
}
