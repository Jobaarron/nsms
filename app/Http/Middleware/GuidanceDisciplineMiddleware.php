<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GuidanceDisciplineMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('guidance.login')
                ->withErrors(['error' => 'Please login to access this area.']);
        }

        // Check if user has guidance session
        if (!session('guidance_user')) {
            return redirect()->route('guidance.login')
                ->withErrors(['error' => 'Please login through the guidance system.']);
        }

        // Check if user is guidance staff
        if (!Auth::user()->isGuidanceStaff()) {
            return redirect()->route('guidance.login')
                ->withErrors(['error' => 'You do not have permission to access this system.']);
        }

        // Check if user status is active (using the status field)
        if (Auth::user()->status !== 'active') {
            Auth::logout();
            session()->forget('guidance_user');
            return redirect()->route('guidance.login')
                ->withErrors(['error' => 'Your account has been deactivated.']);
        }

        return $next($request);
    }
}
