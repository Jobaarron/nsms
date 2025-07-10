<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('admin.login')
                ->with('error', 'You must be logged in to access the admin area.');
        }
        
        // Check if user has admin role
        if (!Auth::user()->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', 'You do not have permission to access the admin area.');
        }
        
        return $next($request);
    }
}
