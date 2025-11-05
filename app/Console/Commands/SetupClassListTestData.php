<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;

class SetupClassListTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:setup-class-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup comprehensive test data for class list logic testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up comprehensive test data for class list logic...');
        
        // Check if test data already exists
        $existingTestStudents = Student::where('student_id', 'LIKE', 'TEST-%')->count();
        if ($existingTestStudents > 0) {
            if (!$this->confirm("Found {$existingTestStudents} existing test students. Continue anyway?")) {
                $this->info('Setup cancelled.');
                return 0;
            }
        }
        
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        DB::beginTransaction();
        
        try {
        
        // Test data structure
        $testData = [
            // Elementary - Grades 1-6 with sections A, B, C
            ['grade' => 'Grade 1', 'sections' => ['A', 'B', 'C']],
            ['grade' => 'Grade 2', 'sections' => ['A', 'B', 'C']],
            ['grade' => 'Grade 3', 'sections' => ['A', 'B', 'C']],
            ['grade' => 'Grade 4', 'sections' => ['A', 'B', 'C']],
            ['grade' => 'Grade 5', 'sections' => ['A', 'B', 'C']],
            ['grade' => 'Grade 6', 'sections' => ['A', 'B', 'C']],
            
            // Junior High - Grades 7-10 with sections A, B, C
            ['grade' => 'Grade 7', 'sections' => ['A', 'B', 'C']],
            ['grade' => 'Grade 8', 'sections' => ['A', 'B', 'C']],
            ['grade' => 'Grade 9', 'sections' => ['A', 'B', 'C']],
            ['grade' => 'Grade 10', 'sections' => ['A', 'B', 'C']],
        ];
        
        // Senior High - Non-TVL strands with sections A, B, C
        $seniorHighStrands = ['STEM', 'ABM', 'HUMSS'];
        foreach (['Grade 11', 'Grade 12'] as $grade) {
            foreach ($seniorHighStrands as $strand) {
                $testData[] = [
                    'grade' => $grade,
                    'sections' => ['A', 'B', 'C'],
                    'strand' => $strand
                ];
            }
        }
        
        // Senior High - TVL strand with tracks and sections A, B, C
        $tvlTracks = ['ICT', 'H.E'];
        foreach (['Grade 11', 'Grade 12'] as $grade) {
            foreach ($tvlTracks as $track) {
                $testData[] = [
                    'grade' => $grade,
                    'sections' => ['A', 'B', 'C'],
                    'strand' => 'TVL',
                    'track' => $track
                ];
            }
        }
        
        $studentCounter = 1;
        $createdStudents = [];
        
        foreach ($testData as $classData) {
            foreach ($classData['sections'] as $section) {
                // Create 1 student per section as requested
                $studentId = 'TEST-' . str_pad($studentCounter, 4, '0', STR_PAD_LEFT);
                
                // Create user account for student
                $user = User::create([
                    'name' => "Test Student {$studentCounter}",
                    'email' => "teststudent{$studentCounter}@test.com",
                    'password' => Hash::make('password123'),
                    'role' => 'student',
                ]);
                
                // Create student record with all required fields
                $student = Student::create([
                    'user_id' => $user->id,
                    'student_id' => $studentId,
                    'first_name' => "Test",
                    'middle_name' => "Student",
                    'last_name' => "Number{$studentCounter}",
                    'suffix' => null,
                    'grade_level' => $classData['grade'],
                    'section' => $section,
                    'strand' => $classData['strand'] ?? null,
                    'track' => $classData['track'] ?? null,
                    'academic_year' => $currentAcademicYear,
                    'enrollment_status' => 'enrolled',
                    'is_paid' => true,
                    'is_active' => true,
                    'contact_number' => '09' . str_pad($studentCounter, 9, '0', STR_PAD_LEFT),
                    'address' => "Test Address {$studentCounter}",
                    'date_of_birth' => '2005-01-01',
                    'gender' => $studentCounter % 2 == 0 ? 'Female' : 'Male',
                    'student_type' => 'new',
                    'guardian_name' => "Test Guardian {$studentCounter}",
                    'guardian_contact' => '09' . str_pad($studentCounter + 1000, 9, '0', STR_PAD_LEFT),
                    'emergency_contact' => '09' . str_pad($studentCounter + 2000, 9, '0', STR_PAD_LEFT),
                    'total_fee' => 0.00,
                    'paid_amount' => 0.00,
                    'balance' => 0.00,
                    'religion' => 'Catholic',
                    'nationality' => 'Filipino',
                    'civil_status' => 'Single',
                    'place_of_birth' => 'Test City',
                ]);
                
                $classDisplay = $classData['grade'] . ' - ' . $section;
                if (isset($classData['strand'])) {
                    $classDisplay .= ' - ' . $classData['strand'];
                    if (isset($classData['track'])) {
                        $classDisplay .= ' - ' . $classData['track'];
                    }
                }
                
                $createdStudents[] = [
                    'student_id' => $studentId,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'class' => $classDisplay
                ];
                
                $studentCounter++;
            }
        }
        
            $totalCreated = $studentCounter - 1;
            $this->info("Created {$totalCreated} test students across all grade levels, sections, strands, and tracks.");
            
            // Display summary
            $this->table(
                ['Student ID', 'Name', 'Class'],
                array_slice($createdStudents, 0, 20) // Show first 20 for brevity
            );
            
            if (count($createdStudents) > 20) {
                $this->info("... and " . (count($createdStudents) - 20) . " more students.");
            }
            
            DB::commit();
            
            $this->info("\nTest data setup complete! You can now test the class list logic.");
            $this->info("Run 'php artisan test:class-logic' to test the filtering and display logic.");
            
        } catch (Exception $e) {
            DB::rollBack();
            $this->error("Error creating test data: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
}
