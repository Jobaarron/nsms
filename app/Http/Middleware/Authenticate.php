<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Get the current route to determine which guard is being used
            $route = $request->route();
            $middleware = $route ? $route->gatherMiddleware() : [];
            
            // Check for specific auth guards in middleware
            foreach ($middleware as $middlewareName) {
                if (str_starts_with($middlewareName, 'auth:')) {
                    $guard = str_replace('auth:', '', $middlewareName);
                    
                    switch ($guard) {
                        case 'student':
                            return route('student.login');
                        case 'enrollee':
                            return route('enrollee.login');
                        case 'registrar':
                            return route('registrar.login');
                        case 'guidance':
                            return route('guidance.login');
                        case 'discipline':
                            return route('discipline.login');
                        case 'cashier':
                            return route('cashier.login');
                        case 'teacher':
                            return route('teacher.login');
                        case 'faculty-head':
                            return route('faculty-head.login');
                        case 'admin':
                        case 'web':
                        default:
                            return route('admin.login');
                    }
                }
            }
            
            // Fallback: determine by URL path
            $path = $request->path();
            
            if (str_starts_with($path, 'student')) {
                return route('student.login');
            }
            
            if (str_starts_with($path, 'enrollee')) {
                return route('enrollee.login');
            }
            
            if (str_starts_with($path, 'registrar')) {
                return route('registrar.login');
            }
            
            if (str_starts_with($path, 'guidance')) {
                return route('guidance.login');
            }
            
            if (str_starts_with($path, 'discipline')) {
                return route('discipline.login');
            }
            
            if (str_starts_with($path, 'cashier')) {
                return route('cashier.login');
            }
            
            if (str_starts_with($path, 'teacher')) {
                return route('teacher.login');
            }
            
            if (str_starts_with($path, 'faculty-head')) {
                return route('faculty-head.login');
            }
            
            if (str_starts_with($path, 'admin')) {
                return route('admin.login');
            }
            
            // Default fallback to admin login
            return route('admin.login');
        }

        return null;
    }
}
