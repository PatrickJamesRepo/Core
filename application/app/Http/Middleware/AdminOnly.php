<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next): mixed
    {
        // Get the currently authenticated user.
        $user = auth()->user();

        // If no user is authenticated, abort with a 403 status.
        if (! $user) {
            abort(403, trans('Admin only'));
        }

        // Allow if singular 'role' attribute === 'admin'
        if (isset($user->role) && $user->role === 'admin') {
            return $next($request);
        }

        // Ensure that the user's roles are in an array if using roles JSON
        $roles = is_array($user->roles)
            ? $user->roles
            : json_decode($user->roles, true);

        // If roles are not set as an array or 'admin' is not in the roles, abort.
        if (! is_array($roles) || ! in_array('admin', $roles)) {
            abort(403, trans('Admin only'));
        }

        // If all checks pass, allow the request to proceed.
        return $next($request);
    }
}
