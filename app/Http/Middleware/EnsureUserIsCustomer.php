<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        // Admins do not belong in the customer area — send them home
        if ($user->isAdmin()) {
            return redirect()
                ->route('admin_dashboard')
                ->with('info', 'You were redirected to the admin dashboard.');
        }

        return $next($request);
    }
}