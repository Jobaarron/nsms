<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cashier;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Enrollee;
use App\Models\Fee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TestCashierIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cashier-integration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test complete cashier system integration with payment workflow';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏦 Testing Cashier System Integration...');
        $this->newLine();

        // Test 1: Cashier Authentication
        $this->testCashierAuthentication();

        // Test 2: Payment Data Integration
        $this->testPaymentDataIntegration();

        // Test 3: Payment Processing
        $this->testPaymentProcessing();

        // Test 4: Controller Methods
        $this->testControllerMethods();

        // Test 5: Model Relationships
        $this->testModelRelationships();

        $this->newLine();
        $this->info('✅ Cashier System Integration Test Complete!');
    }

    private function testCashierAuthentication()
    {
        $this->info('🔐 Testing Cashier Authentication...');

        // Check if cashier exists
        $cashier = Cashier::where('email', 'cashier@nicolites.edu')->first();
        if ($cashier) {
            $this->info("   ✅ Cashier found: {$cashier->full_name} ({$cashier->employee_id})");
            
            // Test password verification
            if (Hash::check('cashier123', $cashier->password)) {
                $this->info("   ✅ Password verification works");
            } else {
                $this->error("   ❌ Password verification failed");
            }
        } else {
            $this->error("   ❌ Cashier not found");
        }

        // Check assistant cashier
        $assistantCashier = Cashier::where('email', 'assistant.cashier@nicolites.edu')->first();
        if ($assistantCashier) {
            $this->info("   ✅ Assistant Cashier found: {$assistantCashier->full_name}");
        }
    }

    private function testPaymentDataIntegration()
    {
        $this->info('💰 Testing Payment Data Integration...');

        $totalPayments = Payment::count();
        $pendingPayments = Payment::pending()->count();
        $duePayments = Payment::due()->count();
        $completedPayments = Payment::completed()->count();

        $this->info("   📊 Total Payments: {$totalPayments}");
        $this->info("   ⏳ Pending Payments: {$pendingPayments}");
        $this->info("   ⚠️  Due Payments: {$duePayments}");
        $this->info("   ✅ Completed Payments: {$completedPayments}");

        // Test payment relationships
        $payment = Payment::with(['payable', 'fee', 'cashier'])->first();
        if ($payment) {
            $this->info("   🔗 Sample Payment: {$payment->transaction_id}");
            $this->info("      - Payable: {$payment->payable->first_name} {$payment->payable->last_name}");
            $this->info("      - Fee: {$payment->fee->name}");
            $this->info("      - Amount: ₱" . number_format($payment->amount, 2));
            $this->info("      - Status: {$payment->confirmation_status}");
        }
    }

    private function testPaymentProcessing()
    {
        $this->info('⚙️  Testing Payment Processing...');

        // Get a pending payment
        $pendingPayment = Payment::pending()->first();
        if ($pendingPayment) {
            $this->info("   🔄 Testing payment confirmation for: {$pendingPayment->transaction_id}");
            
            // Get cashier
            $cashier = Cashier::first();
            if ($cashier) {
                // Simulate payment confirmation
                $pendingPayment->update([
                    'confirmation_status' => 'confirmed',
                    'processed_by' => $cashier->id,
                    'confirmed_at' => now(),
                    'cashier_notes' => 'Test confirmation via integration test',
                    'status' => 'paid',
                ]);

                $this->info("   ✅ Payment confirmed successfully");
                $this->info("      - Processed by: {$cashier->full_name}");
                $this->info("      - Confirmed at: {$pendingPayment->confirmed_at}");

                // Revert for other tests
                $pendingPayment->update([
                    'confirmation_status' => 'pending',
                    'processed_by' => null,
                    'confirmed_at' => null,
                    'cashier_notes' => null,
                    'status' => 'pending',
                ]);
                $this->info("   🔄 Reverted payment status for continued testing");
            }
        } else {
            $this->warn("   ⚠️  No pending payments found for testing");
        }
    }

    private function testControllerMethods()
    {
        $this->info('🎛️  Testing Controller Methods...');

        try {
            // Test CashierController methods exist
            $controller = new \App\Http\Controllers\CashierController();
            
            $methods = [
                'index', 'pendingPayments', 'duePayments', 'completedPayments',
                'paymentHistory', 'confirmPayment', 'rejectPayment', 'reports'
            ];

            foreach ($methods as $method) {
                if (method_exists($controller, $method)) {
                    $this->info("   ✅ Method exists: {$method}");
                } else {
                    $this->error("   ❌ Method missing: {$method}");
                }
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Controller test failed: {$e->getMessage()}");
        }
    }

    private function testModelRelationships()
    {
        $this->info('🔗 Testing Model Relationships...');

        // Test Cashier -> Payments relationship
        $cashier = Cashier::first();
        if ($cashier) {
            $processedPayments = $cashier->processedPayments()->count();
            $this->info("   ✅ Cashier processed payments: {$processedPayments}");
        }

        // Test Payment -> Cashier relationship
        $payment = Payment::whereNotNull('processed_by')->first();
        if ($payment && $payment->cashier) {
            $this->info("   ✅ Payment -> Cashier relationship works");
            $this->info("      - Payment: {$payment->transaction_id}");
            $this->info("      - Processed by: {$payment->cashier->full_name}");
        }

        // Test Payment -> Payable polymorphic relationship
        $studentPayment = Payment::where('payable_type', 'App\Models\Student')->first();
        if ($studentPayment && $studentPayment->payable) {
            $this->info("   ✅ Payment -> Student relationship works");
        }

        $enrolleePayment = Payment::where('payable_type', 'App\Models\Enrollee')->first();
        if ($enrolleePayment && $enrolleePayment->payable) {
            $this->info("   ✅ Payment -> Enrollee relationship works");
        }

        // Test Payment scopes
        $pendingCount = Payment::pending()->count();
        $confirmedCount = Payment::confirmed()->count();
        $dueCount = Payment::due()->count();
        $completedCount = Payment::completed()->count();

        $this->info("   📊 Payment Scopes:");
        $this->info("      - Pending: {$pendingCount}");
        $this->info("      - Confirmed: {$confirmedCount}");
        $this->info("      - Due: {$dueCount}");
        $this->info("      - Completed: {$completedCount}");
    }
}
