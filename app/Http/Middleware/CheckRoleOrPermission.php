<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRoleOrPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|array  $rolesOrPermissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $rolesOrPermissions)
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('admin.login')
                ->with('error', 'You must be logged in to access this area.');
        }

        // Convert pipe-separated string to array
        $rolesOrPermissions = is_array($rolesOrPermissions) ? $rolesOrPermissions : explode('|', $rolesOrPermissions);

        // Check if user has any of the specified roles or permissions
        if (!Auth::user()->hasAnyRole($rolesOrPermissions) && !Auth::user()->hasAnyPermission($rolesOrPermissions)) {
            return redirect()->route('home')
                ->with('error', 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
