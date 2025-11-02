<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckStudentEnrollmentStatus
{
    /**
     * Handle an incoming request - Check if enrollment is complete
     * This middleware is uniform with CheckStudentPaymentStatus for student access control
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if student is authenticated
        if (!Auth::guard('student')->check()) {
            return redirect()->route('student.login');
        }

        $student = Auth::guard('student')->user();

        // Check if student has completed enrollment (pre_registered or enrolled)
        if (!in_array($student->enrollment_status, ['enrolled', 'pre_registered'])) {
            // Redirect to dashboard with message about completing enrollment
            return redirect()->route('student.dashboard')->with('error', 
                'Please complete your enrollment first to access this feature.');
        }

        return $next($request);
    }
}
