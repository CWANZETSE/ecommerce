<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {

        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthenticated request.');
        }
        // Customers do not belong in the admin area — send them home
        if (!$user->isAdmin()) {
            return redirect()
                ->route('home')
                ->with('info', 'You were redirected to the customer home page.');
        }

        return $next($request);
    }
}