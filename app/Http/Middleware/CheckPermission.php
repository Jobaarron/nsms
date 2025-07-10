<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|array  $permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permissions)
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('admin.login')
                ->with('error', 'You must be logged in to access this area.');
        }

        // Convert pipe-separated string to array
        $permissions = is_array($permissions) ? $permissions : explode('|', $permissions);

        // Check if user has any of the specified permissions
        foreach ($permissions as $permission) {
            if (Auth::user()->can($permission)) {
                return $next($request);
            }
        }

        return redirect()->route('index')
            ->with('error', 'You do not have permission to access this area.');
    }
}
