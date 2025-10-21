<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;

class CheckExistingStudents extends Command
{
    protected $signature = 'check:existing-students';
    protected $description = 'Check existing students in database';

    public function handle()
    {
        $students = Student::all();
        
        $this->info("Found " . $students->count() . " students:");
        
        foreach ($students as $student) {
            $this->line("ID: {$student->id} | Student ID: {$student->student_id} | Name: {$student->full_name}");
        }
        
        return 0;
    }
}
