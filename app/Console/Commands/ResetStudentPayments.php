<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetStudentPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:student-payments {student_id : Student ID to reset}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset student payments to show proper initial state (only 1st quarter paid)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $studentId = $this->argument('student_id');
        
        $this->info("🔄 Resetting payments for student: {$studentId}");
        
        // Find student
        $student = DB::table('students')->where('student_id', $studentId)->first();
        
        if (!$student) {
            $this->error("❌ Student {$studentId} not found");
            return Command::FAILURE;
        }
        
        // Get student's payments
        $payments = DB::table('payments')
            ->where('payable_type', 'App\\Models\\Student')
            ->where('payable_id', $student->id)
            ->orderBy('scheduled_date')
            ->get();
            
        if ($payments->isEmpty()) {
            $this->error("❌ No payments found for student {$studentId}");
            return Command::FAILURE;
        }
        
        $this->info("Found {$payments->count()} payments for {$studentId}");
        
        // Reset all payments to pending first
        DB::table('payments')
            ->where('payable_type', 'App\\Models\\Student')
            ->where('payable_id', $student->id)
            ->update([
                'confirmation_status' => 'pending',
                'status' => 'pending',
                'processed_by' => null,
                'confirmed_at' => null,
                'paid_at' => null,
                'cashier_notes' => null,
                'updated_at' => now()
            ]);
            
        // Approve only the first payment (1st quarter)
        $firstPayment = $payments->first();
        DB::table('payments')
            ->where('id', $firstPayment->id)
            ->update([
                'confirmation_status' => 'confirmed',
                'status' => 'paid',
                'processed_by' => 1,
                'confirmed_at' => now(),
                'paid_at' => now(),
                'cashier_notes' => 'First quarter payment approved',
                'updated_at' => now()
            ]);
            
        // Update student status
        $totalPaid = $firstPayment->amount;
        $isFullyPaid = $totalPaid >= ($student->total_fees_due ?? 0);
        
        DB::table('students')
            ->where('id', $student->id)
            ->update([
                'enrollment_status' => 'enrolled',
                'total_paid' => $totalPaid,
                'is_paid' => $isFullyPaid,
                'payment_completed_at' => $isFullyPaid ? now() : null,
                'updated_at' => now()
            ]);
        
        $this->info("✅ Reset complete for {$studentId}:");
        $this->line("  - 1st payment: CONFIRMED (₱{$firstPayment->amount})");
        $this->line("  - Remaining payments: PENDING");
        $this->line("  - Student status: ENROLLED");
        $this->line("  - Total paid: ₱{$totalPaid}");
        $this->line("  - Fully paid: " . ($isFullyPaid ? 'Yes' : 'No'));
        
        return Command::SUCCESS;
    }
}
