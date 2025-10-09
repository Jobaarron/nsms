<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Payment;
use App\Models\Fee;
use Carbon\Carbon;

class PaymentScheduleController extends Controller
{
    /**
     * Get payment statistics for dashboard
     */
    public function getPaymentStatistics()
    {
        $stats = [
            'total_scheduled' => Payment::where('payable_type', 'App\\Models\\Student')->count(),
            'pending_payments' => Payment::where('payable_type', 'App\\Models\\Student')
                ->where('confirmation_status', 'pending')->count(),
            'confirmed_payments' => Payment::where('payable_type', 'App\\Models\\Student')
                ->where('confirmation_status', 'confirmed')->count(),
            'due_payments' => Payment::where('payable_type', 'App\\Models\\Student')
                ->where('confirmation_status', 'pending')
                ->where('scheduled_date', '<=', now())->count(),
            'total_amount_scheduled' => Payment::where('payable_type', 'App\\Models\\Student')->sum('amount'),
            'total_amount_collected' => Payment::where('payable_type', 'App\\Models\\Student')
                ->where('confirmation_status', 'confirmed')->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats
        ]);
    }

    /**
     * Get all payment schedules for cashier
     */
    public function getAllPaymentSchedules(Request $request)
    {
        // Get payment schedules grouped by student and payment method
        $query = Payment::with(['payable'])
            ->where('payable_type', 'App\\Models\\Student')
            ->select('payable_id', 'payment_method', 'confirmation_status')
            ->selectRaw('MIN(id) as first_payment_id')
            ->selectRaw('MIN(transaction_id) as base_transaction_id')
            ->selectRaw('SUM(amount) as total_amount')
            ->selectRaw('COUNT(*) as installment_count')
            ->selectRaw('MIN(scheduled_date) as first_due_date')
            ->selectRaw('MAX(scheduled_date) as last_due_date')
            ->selectRaw('MIN(created_at) as created_at')
            ->groupBy('payable_id', 'payment_method', 'confirmation_status');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('confirmation_status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHasMorph('payable', ['App\\Models\\Student'], function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $paymentSchedules = $query->orderBy('first_due_date')->get();

        // Transform the grouped data to include student information
        $transformedSchedules = $paymentSchedules->map(function($schedule) {
            $student = Student::find($schedule->payable_id);
            
            return [
                'id' => $schedule->first_payment_id,
                'payable_id' => $schedule->payable_id,
                'payable' => $student,
                'transaction_id' => $schedule->base_transaction_id,
                'payment_method' => $schedule->payment_method,
                'confirmation_status' => $schedule->confirmation_status,
                'total_amount' => $schedule->total_amount,
                'installment_count' => $schedule->installment_count,
                'first_due_date' => $schedule->first_due_date,
                'last_due_date' => $schedule->last_due_date,
                'scheduled_date' => $schedule->first_due_date,
                'amount' => $schedule->total_amount,
                'created_at' => $schedule->created_at,
            ];
        });

        // Simple pagination
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $total = $transformedSchedules->count();
        $items = $transformedSchedules->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return response()->json([
            'success' => true,
            'payments' => [
                'data' => $items,
                'current_page' => $currentPage,
                'last_page' => ceil($total / $perPage),
                'per_page' => $perPage,
                'total' => $total,
                'from' => $total > 0 ? (($currentPage - 1) * $perPage) + 1 : null,
                'to' => $total > 0 ? min($currentPage * $perPage, $total) : null,
            ]
        ]);
    }

    /**
     * Get pending payment schedules for cashier
     */
    public function getPendingPaymentSchedules()
    {
        // Get payment schedules grouped by student and payment method (pending only)
        $query = Payment::with(['payable'])
            ->where('payable_type', 'App\\Models\\Student')
            ->where('confirmation_status', 'pending')
            ->select('payable_id', 'payment_method', 'confirmation_status')
            ->selectRaw('MIN(id) as first_payment_id')
            ->selectRaw('MIN(transaction_id) as base_transaction_id')
            ->selectRaw('SUM(amount) as total_amount')
            ->selectRaw('COUNT(*) as installment_count')
            ->selectRaw('MIN(scheduled_date) as first_due_date')
            ->selectRaw('MAX(scheduled_date) as last_due_date')
            ->selectRaw('MIN(created_at) as created_at')
            ->groupBy('payable_id', 'payment_method', 'confirmation_status');

        $paymentSchedules = $query->orderBy('first_due_date')->get();

        // Transform the grouped data to include student information
        $transformedSchedules = $paymentSchedules->map(function($schedule) {
            $student = Student::find($schedule->payable_id);
            
            return [
                'id' => $schedule->first_payment_id,
                'payable_id' => $schedule->payable_id,
                'payable' => $student,
                'transaction_id' => $schedule->base_transaction_id,
                'payment_method' => $schedule->payment_method,
                'confirmation_status' => $schedule->confirmation_status,
                'total_amount' => $schedule->total_amount,
                'installment_count' => $schedule->installment_count,
                'first_due_date' => $schedule->first_due_date,
                'last_due_date' => $schedule->last_due_date,
                'scheduled_date' => $schedule->first_due_date,
                'amount' => $schedule->total_amount,
                'created_at' => $schedule->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'payments' => [
                'data' => $transformedSchedules->values(),
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 20,
                'total' => $transformedSchedules->count(),
                'from' => $transformedSchedules->count() > 0 ? 1 : null,
                'to' => $transformedSchedules->count(),
            ]
        ]);
    }

    /**
     * Get due payment schedules for cashier
     */
    public function getDuePaymentSchedules()
    {
        $payments = Payment::with(['payable'])
            ->where('payable_type', 'App\\Models\\Student')
            ->where('confirmation_status', 'pending')
            ->where('scheduled_date', '<=', now())
            ->orderBy('scheduled_date')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'payments' => $payments
        ]);
    }

    /**
     * Get student payment schedule details
     */
    public function getStudentPaymentSchedule($studentId, $paymentMethod)
    {
        $payments = Payment::with(['payable'])
            ->where('payable_type', 'App\\Models\\Student')
            ->where('payable_id', $studentId)
            ->where('payment_method', $paymentMethod)
            ->orderBy('scheduled_date')
            ->get();

        if ($payments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment schedule not found'
            ], 404);
        }

        $student = $payments->first()->payable;
        $totalAmount = $payments->sum('amount');
        $status = $payments->first()->confirmation_status;

        return response()->json([
            'success' => true,
            'schedule' => [
                'student' => $student,
                'payment_method' => $paymentMethod,
                'total_amount' => $totalAmount,
                'installment_count' => $payments->count(),
                'status' => $status,
                'payments' => $payments->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'scheduled_date' => $payment->scheduled_date,
                        'period_name' => $payment->period_name,
                        'status' => $payment->confirmation_status
                    ];
                })
            ]
        ]);
    }

    /**
     * Process entire student payment schedule (approve/reject all payments)
     */
    public function processStudentPaymentSchedule(Request $request, $studentId, $paymentMethod)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'required_if:action,reject|string|max:1000'
        ]);

        $cashier = Auth::guard('cashier')->user();
        
        if (!$cashier) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }
        
        // Get all payments for this student and payment method
        $payments = Payment::where('payable_type', 'App\\Models\\Student')
            ->where('payable_id', $studentId)
            ->where('payment_method', $paymentMethod)
            ->where('confirmation_status', 'pending')
            ->get();

        if ($payments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No pending payments found for this schedule'
            ], 404);
        }

        $action = $request->action;
        $newStatus = $action === 'approve' ? 'confirmed' : 'rejected';
        $statusText = $action === 'approve' ? 'paid' : 'failed';

        // Update all payments in the schedule
        foreach ($payments as $payment) {
            $payment->update([
                'confirmation_status' => $newStatus,
                'status' => $statusText,
                'processed_by' => $cashier->id,
                'confirmed_at' => now(),
                'cashier_notes' => $request->reason ?? 'Payment schedule ' . $action . 'd',
                'paid_at' => $action === 'approve' ? now() : null,
            ]);
        }

        // Update student record if approved
        if ($action === 'approve') {
            $student = Student::find($studentId);
            if ($student) {
                $totalPaid = Payment::where('payable_type', 'App\\Models\\Student')
                    ->where('payable_id', $studentId)
                    ->where('confirmation_status', 'confirmed')
                    ->sum('amount');
                
                $isFullyPaid = $totalPaid >= ($student->total_fees_due ?? 0);
                
                $student->update([
                    'enrollment_status' => 'enrolled',
                    'total_paid' => $totalPaid,
                    'is_paid' => $isFullyPaid,
                    'payment_completed_at' => $isFullyPaid ? now() : null
                ]);
            }
        }

        $message = $action === 'approve' 
            ? 'Payment schedule approved successfully. All payments have been confirmed.'
            : 'Payment schedule rejected. Reason: ' . $request->reason;

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}
