<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
    public function index(Request $request)
    {
        $cashier = Auth::guard('cashier')->user();
        
        if (!$cashier) {
            return redirect()->route('cashier.login');
        }

        // Handle date filtering for reports
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Get payment statistics
        $pendingPayments = Payment::pending()->count();
        $duePayments = Payment::due()->count();
        $completedPayments = Payment::completed()->count();
        $todayPayments = Payment::whereDate('confirmed_at', today())->confirmed()->count();

        // Get payment method analytics
        $paymentMethodStats = Payment::where('confirmation_status', 'confirmed')
            ->select('payment_method')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(amount) as total_amount')
            ->groupBy('payment_method')
            ->get();

        // Format payment method data for charts
        $paymentMethodData = [
            'full' => ['count' => 0, 'amount' => 0],
            'quarterly' => ['count' => 0, 'amount' => 0],
            'monthly' => ['count' => 0, 'amount' => 0]
        ];

        foreach ($paymentMethodStats as $stat) {
            if (isset($paymentMethodData[$stat->payment_method])) {
                $paymentMethodData[$stat->payment_method] = [
                    'count' => $stat->count,
                    'amount' => $stat->total_amount
                ];
            }
        }

        // Get monthly revenue trend (last 6 months)
        $monthlyRevenue = Payment::where('confirmation_status', 'confirmed')
            ->where('confirmed_at', '>=', now()->subMonths(6))
            ->selectRaw('MONTH(confirmed_at) as month')
            ->selectRaw('YEAR(confirmed_at) as year')
            ->selectRaw('SUM(amount) as total')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Get daily revenue for current month
        $dailyRevenue = Payment::where('confirmation_status', 'confirmed')
            ->whereMonth('confirmed_at', now()->month)
            ->whereYear('confirmed_at', now()->year)
            ->selectRaw('DAY(confirmed_at) as day')
            ->selectRaw('SUM(amount) as total')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Get top performing fee categories
        $topFeeCategories = Payment::with('fee')
            ->where('confirmation_status', 'confirmed')
            ->join('fees', 'payments.fee_id', '=', 'fees.id')
            ->select('fees.fee_category')
            ->selectRaw('SUM(payments.amount) as total_amount')
            ->selectRaw('COUNT(payments.id) as payment_count')
            ->groupBy('fees.fee_category')
            ->orderBy('total_amount', 'desc')
            ->limit(5)
            ->get();

        return view('cashier.index', compact(
            'cashier',
            'pendingPayments',
            'duePayments', 
            'completedPayments',
            'todayPayments',
            'paymentMethodData',
            'monthlyRevenue',
            'dailyRevenue',
            'topFeeCategories',
            'dateFrom',
            'dateTo'
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
     * Display payments with filtering for due/not due.
     */
    public function payments()
    {
        // Get grouped payment schedules for pending payments only
        $groupedPayments = Payment::with(['payable'])
            ->where('payable_type', Student::class)
            ->where('confirmation_status', 'pending')
            ->select('payable_id', 'payment_method', 'confirmation_status')
            ->selectRaw('MIN(id) as first_payment_id')
            ->selectRaw('MIN(transaction_id) as base_transaction_id')
            ->selectRaw('SUM(amount) as total_amount')
            ->selectRaw('COUNT(*) as installment_count')
            ->selectRaw('MIN(scheduled_date) as first_due_date')
            ->selectRaw('MAX(scheduled_date) as last_due_date')
            ->selectRaw('MIN(created_at) as created_at')
            ->groupBy('payable_id', 'payment_method', 'confirmation_status')
            ->orderBy('first_due_date')
            ->get();

        // Create a custom paginator-like object
        $payments = new class($groupedPayments) {
            private $data;
            
            public function __construct($data) {
                $this->data = $data->map(function($schedule) {
                    $student = Student::find($schedule->payable_id);
                    
                    return (object)[
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
            }
            
            public function total() {
                return $this->data->count();
            }
            
            public function count() {
                return $this->data->count();
            }
            
            public function isEmpty() {
                return $this->data->isEmpty();
            }
            
            public function isNotEmpty() {
                return $this->data->isNotEmpty();
            }
            
            public function getIterator() {
                return $this->data->getIterator();
            }
        };

        return view('cashier.payments', compact('payments'));
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

        // Payment method filter removed for on-site processing
        // if ($request->filled('payment_method')) {
        //     $query->where('payment_method', $request->payment_method);
        // }

        // Payment mode filter removed - now using payment_method for schedule type
        // if ($request->filled('payment_mode')) {
        //     $query->where('payment_method', $request->payment_mode);
        // }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Prioritize Student ID search first
                $q->whereHasMorph('payable', [Student::class, Enrollee::class], function($subQuery, $type) use ($search) {
                      if ($type === Student::class) {
                          $subQuery->where('student_id', 'like', "%{$search}%");
                      } elseif ($type === Enrollee::class) {
                          $subQuery->where('application_id', 'like', "%{$search}%");
                      }
                  })
                  ->orWhereHasMorph('payable', [Student::class, Enrollee::class], function($subQuery, $type) use ($search) {
                      $subQuery->where('first_name', 'like', "%{$search}%")
                               ->orWhere('last_name', 'like', "%{$search}%")
                               ->orWhere('full_name', 'like', "%{$search}%");
                  })
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('cashier.payment-history', compact('payments'));
    }

    /**
     * Get payment history data for AJAX requests.
     */
    public function getPaymentHistoryData(Request $request)
    {
        // Get payment data without grouping to avoid SQL errors
        $query = Payment::with(['payable', 'fee', 'cashier']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('confirmation_status', $request->status);
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
                // Prioritize Student ID search first
                $q->whereHasMorph('payable', [Student::class, Enrollee::class], function($subQuery, $type) use ($search) {
                      if ($type === Student::class) {
                          $subQuery->where('student_id', 'like', "%{$search}%");
                      } elseif ($type === Enrollee::class) {
                          $subQuery->where('application_id', 'like', "%{$search}%");
                      }
                  })
                  ->orWhereHasMorph('payable', [Student::class, Enrollee::class], function($subQuery, $type) use ($search) {
                      $subQuery->where('first_name', 'like', "%{$search}%")
                               ->orWhere('last_name', 'like', "%{$search}%")
                               ->orWhere('full_name', 'like', "%{$search}%");
                  })
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $allPayments = $query->orderBy('created_at', 'desc')->get();
        
        // Group payments by student and payment method for consolidation
        $consolidatedPayments = collect();
        $grouped = $allPayments->groupBy(function($payment) {
            $studentId = $payment->payable ? ($payment->payable->student_id ?? $payment->payable->application_id) : 'unknown';
            return $studentId . '_' . $payment->payment_method;
        });
        
        foreach ($grouped as $key => $paymentGroup) {
            $firstPayment = $paymentGroup->first();
            $totalAmount = $paymentGroup->sum('amount');
            $paymentCount = $paymentGroup->count();
            $latestDate = $paymentGroup->max('created_at');
            
            // Create consolidated payment object
            $consolidatedPayment = $firstPayment->toArray();
            $consolidatedPayment['total_amount_paid'] = $totalAmount;
            $consolidatedPayment['payment_count'] = $paymentCount;
            $consolidatedPayment['latest_payment'] = $latestDate;
            
            $consolidatedPayments->push((object) $consolidatedPayment);
        }
        
        // Paginate the consolidated results
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $pagedData = $consolidatedPayments->forPage($currentPage, $perPage);
        
        $paginatedPayments = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            $consolidatedPayments->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        return response()->json([
            'success' => true,
            'payments' => $paginatedPayments
        ]);
    }

    /**
     * Confirm a payment.
     */
    public function confirmPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'cashier_notes' => 'nullable|string|max:1000',
            'amount_received' => 'nullable|numeric|min:0',
        ]);

        $cashier = Auth::guard('cashier')->user();

        // Use amount_received if provided, otherwise use the original amount
        $amountReceived = $request->amount_received ?? $payment->amount;

        $payment->update([
            'confirmation_status' => 'confirmed',
            'processed_by' => $cashier->id,
            'confirmed_at' => now(),
            'cashier_notes' => $request->cashier_notes,
            'status' => 'paid',
            'amount_received' => $amountReceived,
            'paid_at' => now(),
        ]);

        // Update student enrollment status and payment totals when payment schedule is approved
        if ($payment->payable_type === 'App\\Models\\Student') {
            $student = $payment->payable;
            if ($student) {
                // Calculate total paid amount from all confirmed payments
                // Use amount_received if available, otherwise use amount
                $totalPaid = Payment::where('payable_type', 'App\\Models\\Student')
                    ->where('payable_id', $student->id)
                    ->where('confirmation_status', 'confirmed')
                    ->get()
                    ->sum(function($payment) {
                        return $payment->amount_received ?? $payment->amount;
                    });
                
                // Check if payment is complete (total paid >= total fees due)
                $isFullyPaid = $totalPaid >= ($student->total_fees_due ?? 0);
                
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

    /**
     * Display payment archives (merged completed payments and history).
     */
    public function paymentArchives()
    {
        $cashier = Auth::guard('cashier')->user();
        
        if (!$cashier) {
            return redirect()->route('cashier.login');
        }

        return view('cashier.payment-archives', compact('cashier'));
    }

    /**
     * Get payment archives data for AJAX requests.
     */
    public function getPaymentArchivesData(Request $request)
    {
        $query = Payment::with(['payable', 'fee', 'cashier']);

        // Apply filters
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('confirmation_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('payable', function ($subQ) use ($search) {
                      $subQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhere('student_id', 'like', "%{$search}%")
                           ->orWhere('application_id', 'like', "%{$search}%");
                  });
            });
        }

        // Get confirmed and completed payments
        $query->whereIn('confirmation_status', ['confirmed', 'completed']);

        $payments = $query->orderBy('confirmed_at', 'desc')->paginate(20);

        // Get statistics
        $statistics = [
            'confirmed_payments' => Payment::where('confirmation_status', 'confirmed')->count(),
            'completed_payments' => Payment::where('confirmation_status', 'completed')->count(),
        ];

        return response()->json([
            'success' => true,
            'payments' => $payments,
            'statistics' => $statistics
        ]);
    }

    /**
     * Get completed payments data for AJAX requests.
     */
    public function getCompletedPaymentsData(Request $request)
    {
        $query = Payment::with(['payable', 'fee', 'cashier'])
            ->where('confirmation_status', 'confirmed');

        // Apply filters
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('payable', function ($subQ) use ($search) {
                      $subQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhere('student_id', 'like', "%{$search}%")
                           ->orWhere('application_id', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->orderBy('confirmed_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'payments' => $payments
        ]);
    }
}
