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
     * Create a payment schedule from student enrollment
     */
    public function createPaymentSchedule(Request $request)
    {
        // This method would be implemented for student enrollment
        // For now, return a simple response
        return response()->json([
            'success' => true,
            'message' => 'Payment schedule creation not implemented yet'
        ]);
    }

    /**
     * Get payment schedule for a student
     */
    public function getPaymentSchedule($studentId)
    {
        // This method would return payment schedule for a specific student
        return response()->json([
            'success' => true,
            'schedule' => []
        ]);
    }

    /**
     * Get individual payment details for cashier
     */
    public function getPaymentDetails($paymentId)
    {
        $payment = Payment::with(['payable', 'fee'])
            ->where('id', $paymentId)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'payment' => [
                'id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'confirmation_status' => $payment->confirmation_status,
                'payment_method' => $payment->payment_method,
                'scheduled_date' => $payment->scheduled_date,
                'created_at' => $payment->created_at,
                'confirmed_at' => $payment->confirmed_at,
                'payable' => $payment->payable,
                'fee' => $payment->fee ? [
                    'name' => $payment->fee->name,
                    'amount' => $payment->fee->amount,
                    'fee_category' => $payment->fee->fee_category,
                ] : null
            ]
        ]);
    }

    /**
     * Process payment (for cashier)
     */
    public function processPayment(Request $request, $paymentId)
    {
        $request->validate([
            'action' => 'required|in:confirm,reject',
            'cashier_notes' => 'nullable|string|max:1000',
        ]);

        $payment = Payment::find($paymentId);
        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        $cashier = Auth::guard('cashier')->user();
        $action = $request->action;

        $payment->update([
            'confirmation_status' => $action === 'confirm' ? 'confirmed' : 'rejected',
            'status' => $action === 'confirm' ? 'paid' : 'failed',
            'processed_by' => $cashier->id,
            'confirmed_at' => now(),
            'cashier_notes' => $request->cashier_notes,
            'paid_at' => $action === 'confirm' ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment ' . ($action === 'confirm' ? 'confirmed' : 'rejected') . ' successfully'
        ]);
    }

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

        // Due status filter
        if ($request->filled('due_status')) {
            $today = now()->toDateString();
            if ($request->due_status === 'due') {
                $query->havingRaw('MIN(scheduled_date) <= ?', [$today]);
            } elseif ($request->due_status === 'not_due') {
                $query->havingRaw('MIN(scheduled_date) > ?', [$today]);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHasMorph('payable', ['App\\Models\\Student'], function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        // Order by due status (due payments first) then by date
        $paymentSchedules = $query->orderByRaw('CASE WHEN MIN(scheduled_date) <= ? THEN 0 ELSE 1 END', [now()->toDateString()])
                                  ->orderBy('first_due_date')
                                  ->get();

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
        // Find student by student_id (like "NS-25001") or database ID
        if (is_numeric($studentId)) {
            // If numeric, treat as database ID
            $student = Student::find($studentId);
        } else {
            // If not numeric, treat as student_id
            $student = Student::where('student_id', $studentId)->first();
        }
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found with ID: ' . $studentId
            ], 404);
        }

        $payments = Payment::with(['payable'])
            ->where('payable_type', 'App\\Models\\Student')
            ->where('payable_id', $student->id)
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
                        'status' => $payment->status,
                        'confirmation_status' => $payment->confirmation_status
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
        // Debug logging
        \Log::info('processStudentPaymentSchedule called', [
            'studentId' => $studentId,
            'paymentMethod' => $paymentMethod,
            'request_data' => $request->all()
        ]);

        try {
            $request->validate([
                'action' => 'required|in:approve,reject',
                'reason' => 'required_if:action,reject|nullable|string|max:1000'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        }

        $cashier = Auth::guard('cashier')->user();
        
        if (!$cashier) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }
        
        // Find student by student_id (like "NS-25001") or database ID
        if (is_numeric($studentId)) {
            // If numeric, treat as database ID
            $student = Student::find($studentId);
        } else {
            // If not numeric, treat as student_id
            $student = Student::where('student_id', $studentId)->first();
        }
        
        if (!$student) {
            \Log::error('Student not found', ['studentId' => $studentId]);
            return response()->json([
                'success' => false,
                'message' => 'Student not found with ID: ' . $studentId
            ], 404);
        }

        \Log::info('Student found', ['student' => $student->toArray()]);

        // Get all payments for this student and payment method
        $payments = Payment::where('payable_type', 'App\\Models\\Student')
            ->where('payable_id', $student->id)
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
            $totalPaid = Payment::where('payable_type', 'App\\Models\\Student')
                ->where('payable_id', $student->id)
                ->where('confirmation_status', 'confirmed')
                ->sum('amount');
                
                $isFullyPaid = $totalPaid >= ($student->total_fees_due ?? 0);
                
                $student->update([
                    'enrollment_status' => 'enrolled',
                    'total_paid' => $totalPaid,
                    'is_paid' => $isFullyPaid,
                    'payment_completed_at' => $isFullyPaid ? now() : null
                ]);
                
                // Auto-assign section when payment is fully settled
                if ($isFullyPaid && !$student->section) {
                    $this->assignStudentToSection($student);
                }
        }

        $message = $action === 'approve' 
            ? 'Payment schedule approved successfully. All payments have been confirmed.'
            : 'Payment schedule rejected. Reason: ' . $request->reason;

        \Log::info('Payment schedule processed successfully', [
            'action' => $action,
            'message' => $message
        ]);

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Get payment history for cashier dashboard
     */
    public function getPaymentHistory(Request $request)
    {
        // First, get all payments with their relationships
        $baseQuery = Payment::with(['payable', 'fee'])
            ->whereIn('confirmation_status', ['confirmed', 'pending', 'rejected']);

        // Apply filters before grouping
        if ($request->has('status') && $request->status) {
            $baseQuery->where('confirmation_status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $baseQuery->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('payable', function ($payableQuery) use ($search) {
                      $payableQuery->where('student_id', 'like', "%{$search}%")
                                   ->orWhere('first_name', 'like', "%{$search}%")
                                   ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        // Get all matching payments
        $allPayments = $baseQuery->orderBy('created_at', 'desc')->get();

        // Group payments by student and payment method
        $groupedPayments = $allPayments->groupBy(function ($payment) {
            return $payment->payable_id . '_' . $payment->payment_method . '_' . $payment->confirmation_status;
        })->map(function ($paymentGroup) {
            // Get the first payment as the representative
            $firstPayment = $paymentGroup->first();
            
            // Calculate totals
            $totalAmount = $paymentGroup->sum('amount');
            $installmentCount = $paymentGroup->count();
            
            // Create a consolidated payment object
            $consolidatedPayment = $firstPayment->replicate();
            $consolidatedPayment->total_amount = $totalAmount;
            $consolidatedPayment->amount = $totalAmount;
            $consolidatedPayment->installment_count = $installmentCount;
            
            // Ensure we have proper dates - use the earliest created_at and latest confirmed_at
            $consolidatedPayment->created_at = $paymentGroup->min('created_at') ?: $firstPayment->created_at ?: now();
            $consolidatedPayment->confirmed_at = $paymentGroup->whereNotNull('confirmed_at')->max('confirmed_at');
            
            // Try to get cashier info from any payment that has it
            $paymentWithCashier = $paymentGroup->whereNotNull('processed_by')->first();
            if ($paymentWithCashier && $paymentWithCashier->processed_by) {
                // Load the cashier relationship
                $cashier = \App\Models\Cashier::find($paymentWithCashier->processed_by);
                if ($cashier) {
                    $consolidatedPayment->cashier = $cashier;
                    $consolidatedPayment->processed_by = $paymentWithCashier->processed_by;
                    $consolidatedPayment->confirmed_at = $paymentWithCashier->confirmed_at;
                }
            } else {
                // If no processed_by, try to find any cashier who might have processed it
                // For now, let's use a default cashier or the currently logged in cashier
                $currentCashier = auth('cashier')->user();
                if ($currentCashier) {
                    $consolidatedPayment->cashier = $currentCashier;
                    $consolidatedPayment->processed_by = $currentCashier->id;
                }
            }
            
            return $consolidatedPayment;
        })->values();

        // Apply pagination
        $perPage = 15;
        $currentPage = $request->get('page', 1);
        $total = $groupedPayments->count();
        $items = $groupedPayments->forPage($currentPage, $perPage);

        $paginatedPayments = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'pageName' => 'page']
        );

        return response()->json([
            'success' => true,
            'payments' => [
                'data' => $paginatedPayments->items(),
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'last_page' => $paginatedPayments->lastPage(),
                'from' => $paginatedPayments->firstItem(),
                'to' => $paginatedPayments->lastItem()
            ]
        ]);
    }
    
    /**
     * Automatically assign student to available section
     */
    private function assignStudentToSection(Student $student)
    {
        try {
            // Get current academic year
            $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
            
            // Define section priority order
            $sectionOrder = ['A', 'B', 'C', 'D', 'E', 'F'];
            
            // Build query to find students in same grade/strand/track
            $baseQuery = Student::where('grade_level', $student->grade_level)
                               ->where('academic_year', $currentAcademicYear)
                               ->where('is_active', true)
                               ->where('enrollment_status', 'enrolled')
                               ->where('is_paid', true)
                               ->whereNotNull('section');
            
            // Add strand filter for SHS students
            if (in_array($student->grade_level, ['Grade 11', 'Grade 12']) && $student->strand) {
                $baseQuery->where('strand', $student->strand);
                
                // Add track filter for TVL students
                if ($student->strand === 'TVL' && $student->track) {
                    $baseQuery->where('track', $student->track);
                }
            }
            
            // Get section counts
            $sectionCounts = $baseQuery->select('section', DB::raw('count(*) as count'))
                                     ->groupBy('section')
                                     ->pluck('count', 'section')
                                     ->toArray();
            
            // Find the section with the least students, following priority order
            $assignedSection = null;
            $minCount = PHP_INT_MAX;
            
            foreach ($sectionOrder as $section) {
                $count = $sectionCounts[$section] ?? 0;
                
                // Assign to first available section or section with least students
                if ($count < $minCount) {
                    $minCount = $count;
                    $assignedSection = $section;
                }
                
                // If we find an empty section, use it immediately
                if ($count === 0) {
                    break;
                }
            }
            
            // If no sections exist yet, start with section A
            if (!$assignedSection) {
                $assignedSection = 'A';
            }
            
            // Update student with assigned section
            $student->update(['section' => $assignedSection]);
            
            \Log::info('Student assigned to section', [
                'student_id' => $student->student_id,
                'grade_level' => $student->grade_level,
                'strand' => $student->strand,
                'track' => $student->track,
                'assigned_section' => $assignedSection,
                'section_count' => $minCount
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to assign student to section', [
                'student_id' => $student->student_id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Process individual payment installment (for partial payments)
     */
    public function processIndividualPayment(Request $request, $paymentId)
    {
        try {
            $request->validate([
                'action' => 'required|in:approve,reject',
                'reason' => 'required_if:action,reject|nullable|string|max:1000'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        }

        $cashier = Auth::guard('cashier')->user();
        
        if (!$cashier) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }

        // Find the specific payment
        $payment = Payment::with(['payable'])->find($paymentId);
        
        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        $student = $payment->payable;
        $action = $request->action;
        $newStatus = $action === 'approve' ? 'confirmed' : 'rejected';
        $statusText = $action === 'approve' ? 'paid' : 'failed';

        // Update the specific payment
        $payment->update([
            'confirmation_status' => $newStatus,
            'status' => $statusText,
            'processed_by' => $cashier->id,
            'confirmed_at' => now(),
            'cashier_notes' => $request->reason ?? 'Individual payment ' . $action . 'd',
            'paid_at' => $action === 'approve' ? now() : null,
        ]);

        // Check if this is the first quarter payment and if approved
        if ($action === 'approve') {
            // Calculate total paid amount
            $totalPaid = Payment::where('payable_type', 'App\\Models\\Student')
                ->where('payable_id', $student->id)
                ->where('confirmation_status', 'confirmed')
                ->sum('amount');
                
            $isFullyPaid = $totalPaid >= ($student->total_fees_due ?? 0);
            
            // Update student payment status
            $student->update([
                'total_paid' => $totalPaid,
                'is_paid' => $isFullyPaid,
                'payment_completed_at' => $isFullyPaid ? now() : null
            ]);
            
            // Check if this is the first payment (1st quarter) and student is not yet enrolled
            $isFirstPayment = Payment::where('payable_type', 'App\\Models\\Student')
                ->where('payable_id', $student->id)
                ->where('confirmation_status', 'confirmed')
                ->count() === 1;
                
            if ($isFirstPayment && $student->enrollment_status !== 'enrolled') {
                // Enroll student after first payment
                $student->update(['enrollment_status' => 'enrolled']);
                
                // Auto-assign section if not already assigned
                if (!$student->section) {
                    $this->assignStudentToSection($student);
                }
            }
        }

        $message = $action === 'approve' 
            ? 'Payment approved successfully.'
            : 'Payment rejected. Reason: ' . $request->reason;

        \Log::info('Individual payment processed successfully', [
            'payment_id' => $paymentId,
            'action' => $action,
            'student_id' => $student->student_id,
            'message' => $message
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'payment' => [
                'id' => $payment->id,
                'status' => $payment->confirmation_status,
                'student_id' => $student->student_id,
                'amount' => $payment->amount
            ]
        ]);
    }
}
