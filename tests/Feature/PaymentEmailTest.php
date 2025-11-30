<?php

namespace Tests\Feature;

use App\Mail\PaymentConfirmedMail;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentEmailTest extends TestCase
{
    /**
     * Test payment confirmation email sending
     */
    public function test_payment_confirmation_email_sends_to_custom_email()
    {
        Mail::fake();

        // Get first student and payment
        $student = Student::first();
        $payment = Payment::first();

        if (!$student || !$payment) {
            $this->markTestSkipped('No student or payment found in database');
        }

        // Override student email for testing
        $testEmail = '22-70891@g.batstate-u.edu.ph';
        $student->user->email = $testEmail;

        // Send the email
        Mail::send(new PaymentConfirmedMail($payment, $student));

        // Assert email was sent
        Mail::assertSent(PaymentConfirmedMail::class, function ($mail) use ($testEmail) {
            return $mail->hasTo($testEmail);
        });

        $this->assertTrue(true);
    }

    /**
     * Test payment confirmation email with actual sending
     */
    public function test_payment_confirmation_email_content()
    {
        $student = Student::first();
        $payment = Payment::first();

        if (!$student || !$payment) {
            $this->markTestSkipped('No student or payment found in database');
        }

        $mail = new PaymentConfirmedMail($payment, $student);

        // Check email subject
        $this->assertStringContainsString('Payment Receipt', $mail->envelope()->subject);
        $this->assertStringContainsString($payment->transaction_id, $mail->envelope()->subject);

        // Check email view
        $this->assertEquals('emails.payment_confirmed', $mail->content()->view);
    }
}
