<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enrollee;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class TestEnrolleeStudent extends Command
{
    protected $signature = 'test:enrollee-student {application_id}';
    protected $description = 'Test creating student from enrollee data step by step';

    public function handle()
    {
        $applicationId = $this->argument('application_id');
        $enrollee = Enrollee::where('application_id', $applicationId)->first();
        
        if (!$enrollee) {
            $this->error("Enrollee not found!");
            return 1;
        }

        $this->info("Testing student creation with enrollee data...");
        
        // Generate credentials
        $latestStudent = Student::orderBy('id', 'desc')->first();
        $nextNumber = $latestStudent ? (intval(substr($latestStudent->student_id, 3)) + 1) : 25001;
        $studentId = 'NS-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        
        $appIdNumber = str_replace('25', '', $enrollee->application_id);
        $password = '25-' . str_pad($appIdNumber, 3, '0', STR_PAD_LEFT);
        
        $fullName = trim($enrollee->first_name . ' ' . ($enrollee->middle_name ? $enrollee->middle_name . ' ' : '') . $enrollee->last_name . ($enrollee->suffix ? ' ' . $enrollee->suffix : ''));
        
        // Test with basic fields first
        try {
            $basicData = [
                'enrollee_id' => $enrollee->id,
                'student_id' => $studentId,
                'password' => Hash::make($password),
                'first_name' => $enrollee->first_name,
                'middle_name' => $enrollee->middle_name,
                'last_name' => $enrollee->last_name,
                'full_name' => $fullName,
                'date_of_birth' => $enrollee->date_of_birth,
                'gender' => $enrollee->gender,
                'email' => $enrollee->email,
                'address' => $enrollee->address,
                'guardian_name' => $enrollee->guardian_name,
                'guardian_contact' => $enrollee->guardian_contact,
                'grade_level' => $enrollee->grade_level_applied,
                'enrollment_status' => 'pre_registered',
                'academic_year' => $enrollee->academic_year ?? '2024-2025',
                'is_active' => true
            ];
            
            $this->info("Testing basic fields...");
            $student = Student::create($basicData);
            $this->info("✅ Basic student created!");
            $student->delete();
            
        } catch (\Exception $e) {
            $this->error("❌ Error with basic fields:");
            $this->error($e->getMessage());
            return 1;
        }
        
        // Test with additional fields
        try {
            $extendedData = [
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
                'guardian_name' => $enrollee->guardian_name,
                'guardian_contact' => $enrollee->guardian_contact,
                'enrollment_status' => 'pre_registered',
                'academic_year' => $enrollee->academic_year ?? '2024-2025',
                'is_active' => true
            ];
            
            $this->info("Testing extended fields...");
            $student = Student::create($extendedData);
            $this->info("✅ Extended student created!");
            $student->delete();
            
        } catch (\Exception $e) {
            $this->error("❌ Error with extended fields:");
            $this->error($e->getMessage());
            return 1;
        }
        
        // Test with all fields
        try {
            $fullData = [
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
            ];
            
            $this->info("Testing full data set...");
            $student = Student::create($fullData);
            $this->info("✅ Full student created successfully!");
            $this->line("Student ID: {$student->id}");
            $this->line("Student Number: {$student->student_id}");
            
            // Update enrollee
            $enrollee->update([
                'student_id' => $studentId,
                'pre_registered_at' => now()
            ]);
            
            $this->info("✅ Enrollee updated with student_id!");
            $this->info("Pre-registration test completed successfully!");
            
        } catch (\Exception $e) {
            $this->error("❌ Error with full data:");
            $this->error($e->getMessage());
            
            // Check which field might be causing the issue
            $this->info("Checking individual problematic fields...");
            
            $problematicFields = ['documents', 'id_photo_data_url'];
            foreach ($problematicFields as $field) {
                $value = $enrollee->$field;
                if (is_string($value) && strlen($value) > 1000) {
                    $this->line("⚠️  {$field} is very long: " . strlen($value) . " characters");
                } elseif (is_null($value)) {
                    $this->line("⚠️  {$field} is NULL");
                } else {
                    $this->line("✅ {$field} looks OK");
                }
            }
            
            return 1;
        }

        return 0;
    }
}
