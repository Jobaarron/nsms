<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Enrollee;
use App\Models\Payment;
use App\Models\Fee;
use Illuminate\Support\Str;

class CreateTestPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test payment data for cashier system testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating test payment data...');

        // Get or create test fees
        $tuitionFee = Fee::firstOrCreate([
            'name' => 'Tuition Fee',
            'academic_year' => '2024-2025'
        ], [
            'description' => 'Annual tuition fee for Grade 11',
            'amount' => 25000.00,
            'applicable_grades' => ['Grade 11', 'Grade 12'],
            'educational_level' => 'senior_high',
            'fee_category' => 'tuition',
            'payment_schedule' => 'quarterly',
            'is_required' => true,
            'payment_order' => 1,
            'academic_year' => '2024-2025',
            'is_active' => true
        ]);

        $miscFee = Fee::firstOrCreate([
            'name' => 'Miscellaneous Fee',
            'academic_year' => '2024-2025'
        ], [
            'description' => 'Miscellaneous school fees',
            'amount' => 5000.00,
            'applicable_grades' => ['Grade 11', 'Grade 12'],
            'educational_level' => 'senior_high',
            'fee_category' => 'miscellaneous',
            'payment_schedule' => 'quarterly',
            'is_required' => true,
            'payment_order' => 2,
            'academic_year' => '2024-2025',
            'is_active' => true
        ]);

        // Get existing student or enrollees
        $student = Student::first();
        $enrollees = Enrollee::take(3)->get();

        $paymentData = [];

        // Create payments for student if exists
        if ($student) {
            $paymentData[] = [
                'payable' => $student,
                'fee' => $tuitionFee,
                'amount' => 25000.00,
                'status' => 'pending',
                'confirmation_status' => 'pending',
                'payment_method' => 'online_payment',
                'reference_number' => 'BT-' . Str::random(8),
                'notes' => 'First semester tuition payment'
            ];

            $paymentData[] = [
                'payable' => $student,
                'fee' => $miscFee,
                'amount' => 5000.00,
                'status' => 'pending',
                'confirmation_status' => 'pending',
                'payment_method' => 'online_payment',
                'reference_number' => 'GC-' . Str::random(8),
                'notes' => 'Miscellaneous fees payment'
            ];
        }

        // Create payments for enrollees
        foreach ($enrollees as $enrollee) {
            $paymentData[] = [
                'payable' => $enrollee,
                'fee' => $tuitionFee,
                'amount' => 12500.00, // Half payment
                'status' => 'pending',
                'confirmation_status' => 'pending',
                'payment_method' => 'cash',
                'reference_number' => 'CASH-' . Str::random(6),
                'notes' => 'Enrollment down payment'
            ];
        }

        // Create the payments
        foreach ($paymentData as $data) {
            $transactionId = 'TXN-' . date('Ymd') . '-' . Str::random(6);
            
            Payment::create([
                'transaction_id' => $transactionId,
                'fee_id' => $data['fee']->id,
                'payable_id' => $data['payable']->id,
                'payable_type' => get_class($data['payable']),
                'amount' => $data['amount'],
                'status' => $data['status'],
                'confirmation_status' => $data['confirmation_status'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'],
                'notes' => $data['notes'],
                'paid_at' => now()->subHours(rand(1, 48)), // Random time in last 48 hours
            ]);

            $this->info("Created payment: {$transactionId} for {$data['payable']->first_name} {$data['payable']->last_name}");
        }

        // Create some confirmed payments for testing completed payments view
        if ($student) {
            $confirmedPayment = Payment::create([
                'transaction_id' => 'TXN-' . date('Ymd') . '-CONF01',
                'fee_id' => $miscFee->id,
                'payable_id' => $student->id,
                'payable_type' => get_class($student),
                'amount' => 2500.00,
                'status' => 'paid',
                'confirmation_status' => 'confirmed',
                'payment_method' => 'online_payment',
                'reference_number' => 'PM-CONFIRMED',
                'notes' => 'Laboratory fee payment',
                'paid_at' => now()->subDays(5),
                'confirmed_at' => now()->subDays(4),
                'cashier_notes' => 'Payment verified and confirmed',
                'processed_by' => 1, // Assuming cashier ID 1
            ]);

            $this->info("Created confirmed payment: {$confirmedPayment->transaction_id}");
        }

        $totalPayments = Payment::count();
        $this->info("✅ Test payment data created successfully!");
        $this->info("📊 Total payments in system: {$totalPayments}");
        $this->info("💰 Pending payments: " . Payment::where('confirmation_status', 'pending')->count());
        $this->info("✅ Confirmed payments: " . Payment::where('confirmation_status', 'confirmed')->count());
    }
}
