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

        return $next($request);
    }
}
