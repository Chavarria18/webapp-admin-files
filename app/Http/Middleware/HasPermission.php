<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasPermission
{
    /**
     * Allow the request when the user's role grants the action (any scope).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $action): Response
    {
        if (! auth()->user()->hasPermission($action)) {
            abort(403);
        }

        return $next($request);
    }
}
