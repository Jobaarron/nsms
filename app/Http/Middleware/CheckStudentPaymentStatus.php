<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckStudentPaymentStatus
{
    /**
     * Handle an incoming request.
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

        // Check if student is enrolled (which happens after 1st quarter payment approval)
        if ($student->enrollment_status !== 'enrolled') {
            // Redirect to dashboard with message about completing enrollment/payment
            return redirect()->route('student.dashboard')->with('error', 
                'Please complete your enrollment and settle your first quarter payment to access this feature.');
        }
        
        // Additional check: Ensure at least one payment is confirmed (1st quarter)
        $hasConfirmedPayment = \App\Models\Payment::where('payable_type', 'App\\Models\\Student')
            ->where('payable_id', $student->id)
            ->where('confirmation_status', 'confirmed')
            ->exists();
            
        if (!$hasConfirmedPayment) {
            return redirect()->route('student.dashboard')->with('error', 
                'Please wait for cashier to approve your first quarter payment to access this feature.');
        }

        return $next($request);
    }
}

