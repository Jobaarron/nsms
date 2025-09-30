<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enrollee;

class CheckEnrollees extends Command
{
    protected $signature = 'check:enrollees';
    protected $description = 'Check enrollees in database';

    public function handle()
    {
        $enrollees = Enrollee::all();
        
        $this->info("Found " . $enrollees->count() . " enrollees:");
        
        foreach ($enrollees as $enrollee) {
            $this->line("Application ID: {$enrollee->application_id}");
            $this->line("Name: {$enrollee->first_name} {$enrollee->last_name}");
            $this->line("Email: {$enrollee->email}");
            $this->line("Paid: " . ($enrollee->is_paid ? 'Yes' : 'No'));
            $this->line("Payment Date: " . ($enrollee->payment_date ?? 'None'));
            $this->line("Payment Completed At: " . ($enrollee->payment_completed_at ?? 'None'));
            $this->line("Enrollment Status: {$enrollee->enrollment_status}");
            $this->line("Student ID: " . ($enrollee->student_id ?? 'None'));
            $this->line("---");
        }
        
        return 0;
    }
}
