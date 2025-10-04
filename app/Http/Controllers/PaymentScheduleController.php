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
        $request->validate([
            'payment_method' => 'required|in:full,quarterly,monthly',
            'payment_mode' => 'required|in:cash,bank_transfer,online_payment',
            'total_amount' => 'required|numeric|min:0',
            'payment_notes' => 'nullable|string|max:1000',
            'scheduled_payments' => 'required|array|min:1',
            'scheduled_payments.*.period' => 'required|string',
            'scheduled_payments.*.amount' => 'required|numeric|min:0',
            'scheduled_payments.*.date' => 'required|date|after_or_equal:today',
        ]);

        try {
            DB::beginTransaction();

            $student = Auth::guard('student')->user();
            
            // Generate unique transaction ID
            $transactionId = 'TXN-' . $student->student_id . '-' . now()->format('YmdHis');

            // Get appropriate fee based on student's grade level
            $fee = \App\Models\Fee::where('is_active', true)
                ->where('is_required', true)
                ->where('fee_category', 'tuition')
                ->whereJsonContains('applicable_grades', $student->grade_level)
                ->first();

            // If no tuition fee found, try to get entrance fee
            if (!$fee) {
                $fee = \App\Models\Fee::where('is_active', true)
                    ->where('is_required', true)
                    ->where('fee_category', 'entrance')
                    ->whereJsonContains('applicable_grades', $student->grade_level)
                    ->first();
            }

            // Create payment records for each scheduled payment
            $paymentRecords = [];
            
            foreach ($request->scheduled_payments as $index => $scheduledPayment) {
                $payment = Payment::create([
                    'transaction_id' => $transactionId . '-' . ($index + 1),
                    'payable_type' => Student::class,
                    'payable_id' => $student->id,
                    'fee_id' => $fee ? $fee->id : null,
                    'amount' => $scheduledPayment['amount'],
                    'status' => 'pending',
                    'payment_method' => $request->payment_mode,
                    'payment_mode' => $request->payment_method,
                    'scheduled_date' => $scheduledPayment['date'],
                    'period_name' => $scheduledPayment['period'],
                    'reference_number' => null,
                    'notes' => $request->payment_notes,
                    'paid_at' => null,
                    'confirmation_status' => 'pending',
                    'processed_by' => null,
                    'confirmed_at' => null,
                    'cashier_notes' => null,
                    'amount_received' => null,
                ]);
                
                $paymentRecords[] = $payment;
            }

            // Update student payment mode (enrollment status will be updated by cashier after confirmation)
            $student->update([
                'payment_mode' => $request->payment_method
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment schedule submitted successfully. Please wait for cashier confirmation.',
                'transaction_id' => $transactionId,
                'payment_records' => count($paymentRecords),
                'total_amount' => $request->total_amount,
                'redirect_url' => route('student.payments'),
                'status' => 'pending_confirmation'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment schedule for a student
     */
    public function getPaymentSchedule($studentId)
    {
        $student = Student::findOrFail($studentId);
        
        // Check if current user can access this student's data
        if (Auth::guard('student')->check() && Auth::guard('student')->id() !== $student->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $payments = Payment::where('payable_type', Student::class)
            ->where('payable_id', $student->id)
            ->orderBy('scheduled_date')
            ->get();

        return response()->json([
            'success' => true,
            'student' => $student,
            'payments' => $payments,
            'summary' => [
                'total_amount' => $payments->sum('amount'),
                'paid_amount' => $payments->where('status', 'paid')->sum('amount'),
                'pending_amount' => $payments->where('status', 'pending')->sum('amount'),
                'total_payments' => $payments->count(),
                'completed_payments' => $payments->where('status', 'paid')->count(),
                'pending_payments' => $payments->where('status', 'pending')->count(),
            ]
        ]);
    }

    /**
     * Get all payment schedules for cashier
     */
    public function getAllPaymentSchedules(Request $request)
    {
        $query = Payment::with(['payable'])
            ->where('payable_type', Student::class);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('confirmation_status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHasMorph('payable', [Student::class], function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->orderBy('scheduled_date')->paginate(20);

        return response()->json([
            'success' => true,
            'payments' => $payments
        ]);
    }

    /**
     * Get pending payment schedules for cashier
     */
    public function getPendingPaymentSchedules()
    {
        $payments = Payment::with(['payable'])
            ->where('payable_type', Student::class)
            ->where('confirmation_status', 'pending')
            ->orderBy('scheduled_date')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'payments' => $payments
        ]);
    }

    /**
     * Get due payment schedules for cashier
     */
    public function getDuePaymentSchedules()
    {
        $payments = Payment::with(['payable'])
            ->where('payable_type', Student::class)
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
     * Process payment (for cashier)
     */
    public function processPayment(Request $request, $paymentId)
    {
        $request->validate([
            'action' => 'required|in:confirm,reject',
            'cashier_notes' => 'nullable|string|max:1000',
            'amount_received' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $payment = Payment::findOrFail($paymentId);
            $cashier = Auth::guard('cashier')->user();

            if ($request->action === 'confirm') {
                $payment->update([
                    'status' => 'paid',
                    'confirmation_status' => 'confirmed',
                    'processed_by' => $cashier->id,
                    'confirmed_at' => now(),
                    'cashier_notes' => $request->cashier_notes,
                    'amount_received' => $request->amount_received ?? $payment->amount,
                    'paid_at' => now(),
                ]);

                // Check if all payments for this student are confirmed
                $student = $payment->payable;
                $allPayments = Payment::where('payable_type', Student::class)
                    ->where('payable_id', $student->id)
                    ->get();
                
                $allConfirmed = $allPayments->every(function($p) {
                    return $p->confirmation_status === 'confirmed';
                });

                // Update student enrollment status if all payments are confirmed
                if ($allConfirmed && $student->enrollment_status !== 'enrolled') {
                    $student->update([
                        'enrollment_status' => 'enrolled'
                    ]);
                }

                $message = 'Payment confirmed successfully';
            } else {
                $payment->update([
                    'confirmation_status' => 'rejected',
                    'processed_by' => $cashier->id,
                    'confirmed_at' => now(),
                    'cashier_notes' => $request->cashier_notes,
                ]);

                $message = 'Payment rejected successfully';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'payment' => $payment->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment statistics for dashboard
     */
    public function getPaymentStatistics()
    {
        $stats = [
            'total_scheduled' => Payment::where('payable_type', Student::class)->count(),
            'pending_payments' => Payment::where('payable_type', Student::class)
                ->where('confirmation_status', 'pending')->count(),
            'confirmed_payments' => Payment::where('payable_type', Student::class)
                ->where('confirmation_status', 'confirmed')->count(),
            'due_payments' => Payment::where('payable_type', Student::class)
                ->where('confirmation_status', 'pending')
                ->where('scheduled_date', '<=', now())->count(),
            'total_amount_scheduled' => Payment::where('payable_type', Student::class)->sum('amount'),
            'total_amount_collected' => Payment::where('payable_type', Student::class)
                ->where('confirmation_status', 'confirmed')->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats
        ]);
    }

    /**
     * Get individual payment details for cashier
     */
    public function getPaymentDetails($paymentId)
    {
        try {
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
                    'amount_received' => $payment->amount_received,
                    'status' => $payment->status,
                    'confirmation_status' => $payment->confirmation_status,
                    'payment_method' => $payment->payment_method,
                    'payment_mode' => $payment->payment_mode,
                    'period_name' => $payment->period_name,
                    'scheduled_date' => $payment->scheduled_date,
                    'paid_at' => $payment->paid_at,
                    'confirmed_at' => $payment->confirmed_at,
                    'notes' => $payment->notes,
                    'cashier_notes' => $payment->cashier_notes,
                    'reference_number' => $payment->reference_number,
                    'created_at' => $payment->created_at,
                    'payable' => $payment->payable ? [
                        'id' => $payment->payable->id,
                        'first_name' => $payment->payable->first_name,
                        'last_name' => $payment->payable->last_name,
                        'student_id' => $payment->payable->student_id ?? $payment->payable->application_id,
                        'grade_level' => $payment->payable->grade_level ?? $payment->payable->grade_level_applied,
                        'email' => $payment->payable->email,
                        'contact_number' => $payment->payable->contact_number,
                    ] : null,
                    'fee' => $payment->fee ? [
                        'id' => $payment->fee->id,
                        'name' => $payment->fee->name,
                        'description' => $payment->fee->description,
                        'amount' => $payment->fee->amount,
                        'fee_category' => $payment->fee->fee_category,
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payment details: ' . $e->getMessage()
            ], 500);
        }
    }
}
