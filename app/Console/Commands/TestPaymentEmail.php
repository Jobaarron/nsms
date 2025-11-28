<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\Student;
use App\Mail\PaymentConfirmedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestPaymentEmail extends Command
{
    protected $signature = 'test:payment-email {payment_id}';
    protected $description = 'Test payment confirmation email sending';

    public function handle()
    {
        $paymentId = $this->argument('payment_id');
        
        $payment = Payment::find($paymentId);
        if (!$payment) {
            $this->error("Payment ID {$paymentId} not found");
            return 1;
        }

        $student = null;
        if ($payment->payable_type === 'App\\Models\\Student') {
            $student = $payment->payable;
        } elseif ($payment->payable_type === 'App\\Models\\Enrollee') {
            $enrollee = $payment->payable;
            $student = $enrollee->student;
        }

        if (!$student) {
            $this->error("No student found for payment");
            return 1;
        }

        if (!$student->email) {
            $this->error("Student has no email address");
            return 1;
        }

        $this->info("Sending payment confirmation email to: {$student->email}");

        try {
            Mail::to($student->email)->send(new PaymentConfirmedMail($payment, $student));
            Log::info('Test payment email sent', [
                'payment_id' => $payment->id,
                'student_id' => $student->id,
                'email' => $student->email
            ]);
            $this->info("✅ Email sent successfully to {$student->email}");
            return 0;
        } catch (\Exception $e) {
            Log::error('Test payment email failed: ' . $e->getMessage());
            $this->error("❌ Failed to send email: " . $e->getMessage());
            return 1;
        }
    }
}
