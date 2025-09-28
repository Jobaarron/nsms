<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\Student;

class TestStudentAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:student-auth {student_id} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test student authentication with given credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $studentId = $this->argument('student_id');
        $password = $this->argument('password');

        $this->info("Testing authentication for Student ID: {$studentId}");
        
        // Find student
        $student = Student::where('student_id', $studentId)->first();
        
        if (!$student) {
            $this->error("Student with ID '{$studentId}' not found!");
            return 1;
        }

        $this->info("Student found:");
        $this->line("- Name: {$student->full_name}");
        $this->line("- Student ID: {$student->student_id}");
        $this->line("- Auth Identifier: {$student->getAuthIdentifierName()}");
        $this->line("- Auth Identifier Value: {$student->getAuthIdentifier()}");
        
        // Test password
        $passwordMatch = Hash::check($password, $student->password);
        
        if ($passwordMatch) {
            $this->info("✅ Password matches!");
        } else {
            $this->error("❌ Password does not match!");
        }
        
        // Test Laravel Auth attempt
        $credentials = [
            'student_id' => $studentId,
            'password' => $password
        ];
        
        if (\Auth::guard('student')->attempt($credentials)) {
            $this->info("✅ Laravel Auth::attempt() successful!");
            \Auth::guard('student')->logout(); // Clean up
        } else {
            $this->error("❌ Laravel Auth::attempt() failed!");
        }

        return 0;
    }
}
