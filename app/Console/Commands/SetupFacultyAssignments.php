<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FacultyAssignment;
use App\Models\User;
use App\Models\Subject;
use App\Models\Student;

class SetupFacultyAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:setup-faculty-assignments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test faculty assignments with strand/track information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get or create a test teacher
        $teacherUser = User::role('teacher')->first();
        if (!$teacherUser) {
            $this->error('No teacher users found. Please create a teacher first.');
            return 1;
        }
        
        $teacher = $teacherUser->teacher;
        if (!$teacher) {
            $this->error('Teacher profile not found for user. Please ensure teacher profile exists.');
            return 1;
        }
        
        // Get faculty head user for assigned_by
        $facultyHead = User::role('faculty_head')->first();
        if (!$facultyHead) {
            $this->error('No faculty head found. Please create a faculty head first.');
            return 1;
        }
        
        // Get or create a test subject
        $subject = Subject::where('academic_year', $currentAcademicYear)->first();
        if (!$subject) {
            $subject = Subject::create([
                'subject_name' => 'Test Subject',
                'subject_code' => 'TEST101',
                'grade_level' => 'Grade 11',
                'strand' => 'STEM',
                'track' => null,
                'semester' => 'First Semester',
                'academic_year' => $currentAcademicYear,
                'is_active' => true,
            ]);
        }
        
        // Clear existing test assignments
        FacultyAssignment::where('notes', 'LIKE', '%TEST ASSIGNMENT%')->delete();
        
        $this->info('Creating faculty assignments with strand/track information...');
        
        // Create assignments for different grade/section/strand combinations
        $assignments = [
            // Grade 11 STEM
            [
                'grade_level' => 'Grade 11',
                'section' => 'A',
                'strand' => 'STEM',
                'track' => null,
            ],
            [
                'grade_level' => 'Grade 11',
                'section' => 'B',
                'strand' => 'STEM',
                'track' => null,
            ],
            // Grade 11 ABM
            [
                'grade_level' => 'Grade 11',
                'section' => 'A',
                'strand' => 'ABM',
                'track' => null,
            ],
            // Grade 11 TVL
            [
                'grade_level' => 'Grade 11',
                'section' => 'A',
                'strand' => 'TVL',
                'track' => 'ICT',
            ],
            [
                'grade_level' => 'Grade 11',
                'section' => 'B',
                'strand' => 'TVL',
                'track' => 'H.E',
            ],
        ];
        
        foreach ($assignments as $assignmentData) {
            FacultyAssignment::create([
                'teacher_id' => $teacher->id,
                'subject_id' => $subject->id,
                'assigned_by' => $facultyHead->id,
                'grade_level' => $assignmentData['grade_level'],
                'section' => $assignmentData['section'],
                'strand' => $assignmentData['strand'],
                'track' => $assignmentData['track'],
                'assignment_type' => 'subject_teacher',
                'academic_year' => $currentAcademicYear,
                'assigned_date' => now(),
                'status' => 'active',
                'notes' => 'TEST ASSIGNMENT - Created for modal testing',
            ]);
            
            $classInfo = $assignmentData['grade_level'] . ' ' . $assignmentData['strand'];
            if ($assignmentData['track']) {
                $classInfo .= '-' . $assignmentData['track'];
            }
            $classInfo .= ' Section ' . $assignmentData['section'];
            
            $this->info("✅ Created assignment: {$teacherUser->name} -> {$classInfo}");
        }
        
        $this->info("\n🎯 Faculty assignments created successfully!");
        $this->info("Now the Faculty Head assign-faculty modal should work properly.");
        $this->info("The assignments have proper strand/track information for filtering.");
        
        return 0;
    }
}
