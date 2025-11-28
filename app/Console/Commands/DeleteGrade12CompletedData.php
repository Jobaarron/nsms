<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Grade;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeleteGrade12CompletedData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-grade12-completed-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Grade 12 student data after 3 days of completing all grades in all quarters';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Grade 12 completed data deletion process...');

        try {
            // Get all Grade 12 students
            $grade12Students = Student::where('grade_level', 'Grade 12')
                ->where('enrollment_status', 'enrolled')
                ->get();

            $deletedCount = 0;
            $currentAcademicYear = $this->getCurrentAcademicYear();

            foreach ($grade12Students as $student) {
                // Check if student has completed all grades in all quarters
                if ($this->hasCompletedAllQuarters($student, $currentAcademicYear)) {
                    // Check if 3 days have passed since completion
                    $completionTime = $this->getLastGradeCompletionTime($student, $currentAcademicYear);
                    
                    if ($completionTime && $completionTime->addDays(3)->isPast()) {
                        $this->deleteStudentData($student);
                        $deletedCount++;
                        
                        Log::info("Deleted Grade 12 student data", [
                            'student_id' => $student->id,
                            'student_name' => $student->full_name,
                            'completion_time' => $completionTime
                        ]);
                    }
                }
            }

            $this->info("Grade 12 data deletion completed. Deleted: {$deletedCount} student records.");
            Log::info("Grade 12 data deletion completed", ['deleted_count' => $deletedCount]);

        } catch (\Exception $e) {
            $this->error('Error during Grade 12 data deletion: ' . $e->getMessage());
            Log::error('Grade 12 data deletion error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Check if student has completed all grades in all quarters
     * First semester: 1st and 2nd grading/quarter
     * Second semester: 3rd and 4th quarter
     */
    private function hasCompletedAllQuarters($student, $academicYear)
    {
        $quarters = ['1st', '2nd', '3rd', '4th'];
        
        foreach ($quarters as $quarter) {
            // Get grades for this quarter
            $grades = Grade::where('student_id', $student->id)
                ->where('quarter', $quarter)
                ->where('academic_year', $academicYear)
                ->where('is_final', true)
                ->get();

            // Check if student has grades in all subjects for this quarter
            $totalSubjects = $student->classSchedules()->count();
            
            if ($grades->count() < $totalSubjects) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the timestamp of the last grade completion
     */
    private function getLastGradeCompletionTime($student, $academicYear)
    {
        $lastGrade = Grade::where('student_id', $student->id)
            ->where('academic_year', $academicYear)
            ->where('is_final', true)
            ->orderBy('updated_at', 'desc')
            ->first();

        return $lastGrade ? $lastGrade->updated_at : null;
    }

    /**
     * Delete student data and related records
     */
    private function deleteStudentData($student)
    {
        try {
            // Delete grades
            Grade::where('student_id', $student->id)->delete();

            // Delete payments
            $student->payments()->delete();

            // Delete the student record
            $student->delete();

            Log::info("Successfully deleted Grade 12 student record", [
                'student_id' => $student->id,
                'student_name' => $student->full_name
            ]);

        } catch (\Exception $e) {
            Log::error("Error deleting Grade 12 student data", [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get current academic year in Philippine format
     */
    private function getCurrentAcademicYear()
    {
        $currentYear = date('Y');
        $currentMonth = date('n');

        if ($currentMonth >= 1 && $currentMonth <= 5) {
            return ($currentYear - 1) . '-' . $currentYear;
        } else {
            return $currentYear . '-' . ($currentYear + 1);
        }
    }
}
