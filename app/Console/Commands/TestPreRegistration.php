<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enrollee;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class TestPreRegistration extends Command
{
    protected $signature = 'test:pre-registration {application_id}';
    protected $description = 'Test pre-registration process for an enrollee';

    public function handle()
    {
        $applicationId = $this->argument('application_id');
        $this->info("Testing pre-registration for Application ID: {$applicationId}");
        
        // Find enrollee
        $enrollee = Enrollee::where('application_id', $applicationId)->first();
        
        if (!$enrollee) {
            $this->error("Enrollee with Application ID '{$applicationId}' not found!");
            return 1;
        }

        $this->info("Enrollee found:");
        $this->line("- Name: {$enrollee->full_name}");
        $this->line("- Application ID: {$enrollee->application_id}");
        $this->line("- Payment Status: " . ($enrollee->is_paid ? 'Paid' : 'Not Paid'));
        $this->line("- Enrollment Status: {$enrollee->enrollment_status}");
        
        // Check if already pre-registered
        if ($enrollee->student_id) {
            $this->error("Already pre-registered with Student ID: {$enrollee->student_id}");
            return 1;
        }
        
        // Check payment status
        if (!$enrollee->payment_date && !$enrollee->payment_completed_at && !$enrollee->is_paid) {
            $this->error("Payment not completed - cannot pre-register");
            return 1;
        }
        
        try {
            // Generate student ID (format: NS-25001)
            $latestStudent = Student::orderBy('id', 'desc')->first();
            $nextNumber = $latestStudent ? (intval(substr($latestStudent->student_id, 3)) + 1) : 25001;
            $studentId = 'NS-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            
            // Generate password (format: 25-001 based on application_id)
            $appIdNumber = str_replace('25', '', $enrollee->application_id);
            $password = '25-' . str_pad($appIdNumber, 3, '0', STR_PAD_LEFT);
            
            $this->info("Generated credentials:");
            $this->line("- Student ID: {$studentId}");
            $this->line("- Password: {$password}");
            
            // Test student record creation
            $fullName = trim($enrollee->first_name . ' ' . ($enrollee->middle_name ? $enrollee->middle_name . ' ' : '') . $enrollee->last_name . ($enrollee->suffix ? ' ' . $enrollee->suffix : ''));
            
            $this->info("Testing Student::create with data:");
            $this->line("- enrollee_id: {$enrollee->id}");
            $this->line("- student_id: {$studentId}");
            $this->line("- full_name: {$fullName}");
            $this->line("- grade_level: {$enrollee->grade_level_applied}");
            
            // Test the actual creation
            $studentData = [
                'enrollee_id' => $enrollee->id,
                'student_id' => $studentId,
                'password' => Hash::make($password),
                'lrn' => $enrollee->lrn,
                'first_name' => $enrollee->first_name,
                'middle_name' => $enrollee->middle_name,
                'last_name' => $enrollee->last_name,
                'suffix' => $enrollee->suffix,
                'full_name' => $fullName,
                'date_of_birth' => $enrollee->date_of_birth,
                'place_of_birth' => $enrollee->place_of_birth,
                'gender' => $enrollee->gender,
                'nationality' => $enrollee->nationality,
                'religion' => $enrollee->religion,
                'contact_number' => $enrollee->contact_number,
                'email' => $enrollee->email,
                'address' => $enrollee->address,
                'city' => $enrollee->city,
                'province' => $enrollee->province,
                'zip_code' => $enrollee->zip_code,
                'grade_level' => $enrollee->grade_level_applied,
                'strand' => $enrollee->strand_applied,
                'track' => $enrollee->track_applied,
                'student_type' => $enrollee->student_type,
                'enrollment_status' => 'pre_registered',
                'academic_year' => $enrollee->academic_year,
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
                'medical_history' => $enrollee->medical_history,
                'pre_registered_at' => now(),
                'is_active' => true
            ];
            
            // Check for null values that might cause issues
            $this->info("Checking for potential null values:");
            foreach ($studentData as $key => $value) {
                if (is_null($value)) {
                    $this->line("⚠️  {$key} is null");
                }
            }
            
            $student = Student::create($studentData);
            
            $this->info("✅ Student record created successfully!");
            $this->line("Student ID: {$student->id}");
            $this->line("Database Student ID: {$student->student_id}");
            
            // Update enrollee
            $enrollee->update([
                'student_id' => $studentId,
                'pre_registered_at' => now()
            ]);
            
            $this->info("✅ Enrollee updated with student_id!");
            $this->info("Pre-registration test completed successfully!");
            
        } catch (\Exception $e) {
            $this->error("❌ Error during pre-registration:");
            $this->error($e->getMessage());
            $this->line("Stack trace:");
            $this->line($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
