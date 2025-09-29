<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class TestStudentLogin extends Command
{
    protected $signature = 'test:student-login {student_id} {password}';
    protected $description = 'Test student login credentials';

    public function handle()
    {
        $studentId = $this->argument('student_id');
        $password = $this->argument('password');
        
        $this->info("Testing student login:");
        $this->line("Student ID: {$studentId}");
        $this->line("Password: {$password}");
        
        // Find student
        $student = Student::where('student_id', $studentId)->first();
        
        if (!$student) {
            $this->error("❌ Student not found!");
            return 1;
        }
        
        $this->info("✅ Student found: {$student->full_name}");
        
        // Check password
        if (Hash::check($password, $student->password)) {
            $this->info("✅ Password matches!");
            $this->info("🎉 Login credentials are correct!");
            
            // Show student details
            $this->line("Student Details:");
            $this->line("- ID: {$student->id}");
            $this->line("- Student ID: {$student->student_id}");
            $this->line("- Full Name: {$student->full_name}");
            $this->line("- Email: {$student->email}");
            $this->line("- Grade Level: {$student->grade_level}");
            $this->line("- Enrollment Status: {$student->enrollment_status}");
            
        } else {
            $this->error("❌ Password does not match!");
            $this->line("Stored password hash: " . substr($student->password, 0, 20) . "...");
            return 1;
        }

        return 0;
    }
}
