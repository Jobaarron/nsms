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
}
