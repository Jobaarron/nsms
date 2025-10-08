<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Cashier;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Enrollee;
use App\Models\Fee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    /**
     * Display the cashier dashboard.
     */
    public function index()
    {
        $cashier = Auth::guard('cashier')->user();
        
        if (!$cashier) {
            return redirect()->route('cashier.login');
        }

        // Get payment statistics
        $pendingPayments = Payment::pending()->count();
        $duePayments = Payment::due()->count();
        $completedPayments = Payment::completed()->count();
        $todayPayments = Payment::whereDate('confirmed_at', today())->confirmed()->count();

        // Get recent payments
        $recentPayments = Payment::with(['payable', 'fee', 'cashier'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('cashier.index', compact(
            'cashier',
            'pendingPayments',
            'duePayments', 
            'completedPayments',
            'todayPayments',
            'recentPayments'
        ));
    }

    /**
     * Show the cashier login form.
     */
    public function showLoginForm()
    {
        return view('cashier.login');
    }

    /**
     * Handle cashier login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('cashier')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('cashier.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle cashier logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('cashier')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('cashier.login');
    }

    /**
     * Display pending payments.
     */
    public function pendingPayments()
    {
        $payments = Payment::with(['payable', 'fee'])
            ->pending()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('cashier.pending-payments', compact('payments'));
    }

    /**
     * Display due payments.
     */
    public function duePayments()
    {
        $payments = Payment::with(['payable', 'fee'])
            ->due()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('cashier.due-payments', compact('payments'));
    }

    /**
     * Display completed payments.
     */
    public function completedPayments()
    {
        $payments = Payment::with(['payable', 'fee', 'cashier'])
            ->completed()
            ->orderBy('confirmed_at', 'desc')
            ->paginate(20);

        return view('cashier.completed-payments', compact('payments'));
    }

    /**
     * Display payment history.
     */
    public function paymentHistory(Request $request)
    {
        $query = Payment::with(['payable', 'fee', 'cashier']);

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
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHasMorph('payable', [Student::class, Enrollee::class], function($subQuery, $type) use ($search) {
                      $subQuery->where('first_name', 'like', "%{$search}%")
                               ->orWhere('last_name', 'like', "%{$search}%");
                      
                      if ($type === Student::class) {
                          $subQuery->orWhere('student_id', 'like', "%{$search}%");
                      } elseif ($type === Enrollee::class) {
                          $subQuery->orWhere('application_id', 'like', "%{$search}%");
                      }
                  });
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('cashier.payment-history', compact('payments'));
    }

    /**
     * Confirm a payment.
     */
    public function confirmPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'cashier_notes' => 'nullable|string|max:1000',
        ]);

        $cashier = Auth::guard('cashier')->user();

        $payment->update([
            'confirmation_status' => 'confirmed',
            'processed_by' => $cashier->id,
            'confirmed_at' => now(),
            'cashier_notes' => $request->cashier_notes,
            'status' => 'paid',
        ]);

        // Update student enrollment status and payment totals when payment schedule is approved
        if ($payment->payable_type === 'App\\Models\\Student') {
            $student = $payment->payable;
            if ($student) {
                // Calculate total paid amount from all confirmed payments
                $totalPaid = \App\Models\Payment::where('payable_type', 'App\\Models\\Student')
                    ->where('payable_id', $student->id)
                    ->where('confirmation_status', 'confirmed')
                    ->sum('amount');
                
                // Check if payment is complete (total paid >= total fees due)
                $isFullyPaid = $totalPaid >= $student->total_fees_due;
                
                // Update student record
                $student->update([
                    'enrollment_status' => 'enrolled',
                    'total_paid' => $totalPaid,
                    'is_paid' => $isFullyPaid,
                    'payment_completed_at' => $isFullyPaid ? now() : null
                ]);
                
                Log::info("Student payment updated - ID: {$student->id}, Total Paid: {$totalPaid}, Fully Paid: " . ($isFullyPaid ? 'Yes' : 'No'));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed successfully.',
        ]);
    }

    /**
     * Reject a payment.
     */
    public function rejectPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'cashier_notes' => 'required|string|max:1000',
        ]);

        $cashier = Auth::guard('cashier')->user();

        $payment->update([
            'confirmation_status' => 'rejected',
            'processed_by' => $cashier->id,
            'confirmed_at' => now(),
            'cashier_notes' => $request->cashier_notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment rejected successfully.',
        ]);
    }

    /**
     * Get payment details for modal.
     */
    public function getPaymentDetails(Payment $payment)
    {
        $payment->load(['payable', 'fee', 'cashier']);
        
        return response()->json([
            'success' => true,
            'payment' => $payment,
        ]);
    }

    /**
     * Generate payment reports.
     */
    public function reports(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Payment summary
        $paymentSummary = Payment::selectRaw('
            confirmation_status,
            COUNT(*) as count,
            SUM(amount) as total_amount
        ')
        ->whereBetween('created_at', [$dateFrom, $dateTo])
        ->groupBy('confirmation_status')
        ->get();

        // Daily payment trends
        $dailyPayments = Payment::selectRaw('
            DATE(confirmed_at) as date,
            COUNT(*) as count,
            SUM(amount) as total_amount
        ')
        ->whereBetween('confirmed_at', [$dateFrom, $dateTo])
        ->where('confirmation_status', 'confirmed')
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // Payment methods breakdown
        $paymentMethods = Payment::selectRaw('
            payment_method,
            COUNT(*) as count,
            SUM(amount) as total_amount
        ')
        ->whereBetween('confirmed_at', [$dateFrom, $dateTo])
        ->where('confirmation_status', 'confirmed')
        ->groupBy('payment_method')
        ->get();

        return view('cashier.reports', compact(
            'paymentSummary',
            'dailyPayments',
            'paymentMethods',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Display fee management page.
     */
    public function fees()
    {
        $cashier = Auth::guard('cashier')->user();
        
        if (!$cashier) {
            return redirect()->route('cashier.login');
        }

        $fees = Fee::orderBy('academic_year', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(20);

        return view('cashier.fees', compact('cashier', 'fees'));
    }

    /**
     * Show create fee form.
     */
    public function createFee()
    {
        $cashier = Auth::guard('cashier')->user();
        
        if (!$cashier) {
            return redirect()->route('cashier.login');
        }

        return view('cashier.fees-create', compact('cashier'));
    }

    /**
     * Store a new fee.
     */
    public function storeFee(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric',
            'academic_year' => 'required|string|max:10',
            'educational_level' => 'required|string|in:preschool,elementary,junior_high,senior_high',
            'fee_category' => 'required|string|in:entrance,tuition,miscellaneous,laboratory,library,other',
            'payment_schedule' => 'required|string|in:full_payment,pay_separate,pay_before_exam,monthly,quarterly',
            'grade_levels' => 'required|array|min:1',
            'grade_levels.*' => 'string',
            'is_active' => 'boolean',
        ]);

        // Create base fee
        $fee = Fee::create([
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'academic_year' => $request->academic_year,
            'applicable_grades' => $request->grade_levels,
            'educational_level' => $request->educational_level,
            'fee_category' => $request->fee_category,
            'payment_schedule' => $request->payment_schedule,
            'is_required' => true,
            'payment_order' => 1,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('cashier.fees')
            ->with('success', 'Fee created successfully!');
    }

    /**
     * Show edit fee form.
     */
    public function editFee(Fee $fee)
    {
        $cashier = Auth::guard('cashier')->user();
        
        if (!$cashier) {
            return redirect()->route('cashier.login');
        }

        return view('cashier.fees-edit', compact('cashier', 'fee'));
    }

    /**
     * Update fee.
     */
    public function updateFee(Request $request, Fee $fee)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric',
            'academic_year' => 'required|string|max:10',
            'educational_level' => 'required|string|in:preschool,elementary,junior_high,senior_high',
            'fee_category' => 'required|string|in:entrance,tuition,miscellaneous,laboratory,library,other',
            'payment_schedule' => 'required|string|in:full_payment,pay_separate,pay_before_exam,monthly,quarterly',
            'grade_levels' => 'required|array|min:1',
            'grade_levels.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $fee->update([
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'academic_year' => $request->academic_year,
            'applicable_grades' => $request->grade_levels,
            'educational_level' => $request->educational_level,
            'fee_category' => $request->fee_category,
            'payment_schedule' => $request->payment_schedule,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('cashier.fees')
            ->with('success', 'Fee updated successfully!');
    }

    /**
     * Delete fee.
     */
    public function destroyFee(Fee $fee)
    {
        // Check if fee is being used in payments
        $paymentCount = Payment::where('fee_id', $fee->id)->count();
        
        if ($paymentCount > 0) {
            return back()->withErrors([
                'error' => 'Cannot delete fee that has associated payments. Deactivate it instead.'
            ]);
        }

        $fee->delete();

        return redirect()->route('cashier.fees')
            ->with('success', 'Fee deleted successfully!');
    }

    /**
     * Toggle fee active status.
     */
    public function toggleFeeStatus(Fee $fee)
    {
        $fee->update([
            'is_active' => !$fee->is_active
        ]);

        $status = $fee->is_active ? 'activated' : 'deactivated';
        
        return response()->json([
            'success' => true,
            'message' => "Fee {$status} successfully!",
            'is_active' => $fee->is_active
        ]);
    }
}
