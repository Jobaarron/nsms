<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class TestMinimalStudent extends Command
{
    protected $signature = 'test:minimal-student';
    protected $description = 'Test creating minimal student record';

    public function handle()
    {
        $this->info("Testing minimal student creation...");
        
        try {
            $student = Student::create([
                'student_id' => 'NS-99999',
                'password' => Hash::make('test123'),
                'first_name' => 'Test',
                'last_name' => 'Student',
                'full_name' => 'Test Student',
                'date_of_birth' => '2000-01-01',
                'gender' => 'male',
                'email' => 'test@example.com',
                'address' => 'Test Address',
                'grade_level' => 'Grade 10',
                'guardian_name' => 'Test Guardian',
                'guardian_contact' => '09123456789',
                'enrollment_status' => 'pre_registered',
                'academic_year' => '2024-2025',
                'is_active' => true
            ]);
            
            $this->info("✅ Minimal student created successfully!");
            $this->line("Student ID: {$student->id}");
            $this->line("Student Number: {$student->student_id}");
            
            // Clean up
            $student->delete();
            $this->info("✅ Test student deleted");
            
        } catch (\Exception $e) {
            $this->error("❌ Error creating minimal student:");
            $this->error($e->getMessage());
            
            // Get the SQL error details
            if (method_exists($e, 'getPrevious') && $e->getPrevious()) {
                $this->error("SQL Error: " . $e->getPrevious()->getMessage());
            }
            
            return 1;
        }

        return 0;
    }
}
