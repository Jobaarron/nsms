<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enrollee;
use Illuminate\Support\Facades\Auth;

class TestAuthenticatedPreRegistration extends Command
{
    protected $signature = 'test:auth-pre-registration {application_id}';
    protected $description = 'Test pre-registration with proper authentication';

    public function handle()
    {
        $applicationId = $this->argument('application_id');
        $enrollee = Enrollee::where('application_id', $applicationId)->first();
        
        if (!$enrollee) {
            $this->error("Enrollee not found!");
            return 1;
        }

        $this->info("Testing authenticated pre-registration for: {$enrollee->full_name}");
        
        try {
            // Manually authenticate the enrollee
            Auth::guard('enrollee')->login($enrollee);
            
            // Verify authentication
            $authenticatedUser = Auth::guard('enrollee')->user();
            if (!$authenticatedUser) {
                $this->error("Failed to authenticate enrollee");
                return 1;
            }
            
            $this->info("✅ Enrollee authenticated successfully");
            $this->line("Authenticated as: {$authenticatedUser->full_name}");
            
            // Now test the pre-registration logic manually
            $this->info("Checking pre-registration eligibility...");
            
            // Check if already pre-registered
            if ($enrollee->student_id) {
                $this->error("Already pre-registered with Student ID: {$enrollee->student_id}");
                return 1;
            }
            
            // Check payment status
            if (!$enrollee->is_paid && !$enrollee->payment_completed_at) {
                $this->error("Payment not completed - cannot pre-register");
                return 1;
            }
            
            $this->info("✅ Eligibility checks passed");
            
            // Generate student ID
            $latestStudent = \App\Models\Student::orderBy('id', 'desc')->first();
            $nextNumber = $latestStudent ? (intval(substr($latestStudent->student_id, 3)) + 1) : 25001;
            $studentId = 'NS-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            
            // Generate password (same as application_id)
            $password = $enrollee->application_id;
            
            $this->info("Generated credentials:");
            $this->line("Student ID: {$studentId}");
            $this->line("Password: {$password}");
            
            // Create full name
            $fullName = trim($enrollee->first_name . ' ' . ($enrollee->middle_name ? $enrollee->middle_name . ' ' : '') . $enrollee->last_name . ($enrollee->suffix ? ' ' . $enrollee->suffix : ''));
            
            // Create student record with the same logic as controller
            $student = \App\Models\Student::create([
                'enrollee_id' => $enrollee->id,
                'student_id' => $studentId,
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'lrn' => $enrollee->lrn,
                'first_name' => $enrollee->first_name,
                'middle_name' => $enrollee->middle_name,
                'last_name' => $enrollee->last_name,
                'suffix' => $enrollee->suffix,
                'full_name' => $fullName,
                'date_of_birth' => $enrollee->date_of_birth,
                'place_of_birth' => $enrollee->place_of_birth ?? 'Not specified',
                'gender' => $enrollee->gender,
                'nationality' => $enrollee->nationality ?? 'Filipino',
                'religion' => $enrollee->religion,
                'contact_number' => $enrollee->contact_number,
                'email' => $enrollee->email,
                'address' => $enrollee->address,
                'city' => $enrollee->city,
                'province' => $enrollee->province,
                'zip_code' => $enrollee->zip_code,
                'grade_level' => $enrollee->grade_level_applied,
                'strand' => $enrollee->strand_applied,
                'track' => $enrollee->track_applied ?? 'Academic',
                'student_type' => $enrollee->student_type ?? 'new',
                'enrollment_status' => 'pre_registered',
                'academic_year' => $enrollee->academic_year ?? '2024-2025',
                'documents' => $enrollee->documents,
                'id_photo_data_url' => $enrollee->id_photo_data_url,
                'father_name' => $enrollee->father_name,
                'father_occupation' => $enrollee->father_occupation,
                'father_contact' => $enrollee->father_contact,
                'mother_name' => $enrollee->mother_name,
                'mother_occupation' => $enrollee->mother_occupation,
                'mother_contact' => $enrollee->mother_contact,
                'guardian_name' => $enrollee->guardian_name,
                'guardian_contact' => $enrollee->guardian_contact,
                'last_school_type' => $enrollee->last_school_type,
                'last_school_name' => $enrollee->last_school_name,
                'medical_history' => $enrollee->medical_history ?? 'None specified',
                'pre_registered_at' => now(),
                'is_active' => true
            ]);
            
            $this->info("✅ Student record created successfully!");
            $this->line("Student Database ID: {$student->id}");
            $this->line("Student ID: {$student->student_id}");
            
            // Update enrollee
            $enrollee->update([
                'student_id' => $studentId,
                'pre_registered_at' => now()
            ]);
            
            $this->info("✅ Enrollee updated with student_id!");
            $this->info("🎉 Pre-registration completed successfully!");
            
            // Logout
            Auth::guard('enrollee')->logout();
            
        } catch (\Exception $e) {
            $this->error("❌ Error during authenticated pre-registration:");
            $this->error($e->getMessage());
            $this->line("Stack trace:");
            $this->line($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
