<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|array  $roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $roles)
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('admin.login')
                ->with('error', 'You must be logged in to access this area.');
        }

        // Convert pipe-separated string to array
        $roles = is_array($roles) ? $roles : explode('|', $roles);

        // Check if user has any of the specified roles
        if (!Auth::user()->hasAnyRole($roles)) {
            return redirect()->route('index')
                ->with('error', 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
