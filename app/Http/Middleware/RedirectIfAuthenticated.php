<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Only redirect if the current guard matches the authenticated guard
                // This prevents cross-guard session conflicts
                $authenticatedGuard = $this->getAuthenticatedGuard();
                
                if ($authenticatedGuard === $guard) {
                    // Redirect based on guard type
                    switch ($guard) {
                        case 'registrar':
                            return redirect()->route('registrar.dashboard');
                        case 'student':
                            return redirect()->route('student.dashboard');
                        case 'enrollee':
                            return redirect()->route('enrollee.dashboard');
                        case 'web':
                        default:
                            return redirect('/home');
                    }
                }
            }
        }

        return $next($request);
    }
    
    /**
     * Get the currently authenticated guard
     */
    private function getAuthenticatedGuard(): ?string
    {
        $guards = ['web', 'registrar', 'enrollee', 'student', 'discipline', 'guidance', 'cashier', 'faculty_head'];
        
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }
        
        return null;
    }
}
