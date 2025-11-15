<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckClassAdviser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please log in to access this feature.');
        }

        $user = auth()->user();
        $teacher = $user->teacher ?? null;
        
        // Check if user has teacher profile and is assigned as class adviser
        if (!$teacher || !$teacher->isClassAdviser()) {
            return redirect()->route('teacher.dashboard')
                           ->with('error', 'Access denied. This feature is only available for class advisers.');
        }

        return $next($request);
    }
}
