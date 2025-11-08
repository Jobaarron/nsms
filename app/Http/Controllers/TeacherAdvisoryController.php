<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FacultyAssignment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Payment;

class TeacherAdvisoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:teacher|faculty_head']);
    }

    /**
     * Display students in teacher's advisory class only
     */
    public function advisory()
    {
        $teacher = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get teacher's advisory assignment (class adviser)
        $advisoryAssignment = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
            ->where('assignment_type', 'class_adviser')
            ->where('academic_year', $currentAcademicYear)
            ->where('status', 'active')
            ->first();
        
        $students = collect();
        $className = '';
        
        if ($advisoryAssignment) {
            // Build class name
            $className = $advisoryAssignment->grade_level . ' - ' . $advisoryAssignment->section;
            if ($advisoryAssignment->strand) {
                $className .= ' - ' . $advisoryAssignment->strand;
                if ($advisoryAssignment->track) {
                    $className .= ' - ' . $advisoryAssignment->track;
                }
            }
            
            // Get students in advisory class (include quarterly and monthly payers)
            $studentsQuery = Student::where('grade_level', $advisoryAssignment->grade_level)
                                   ->where('section', $advisoryAssignment->section)
                                   ->where('academic_year', $currentAcademicYear)
                                   ->where('is_active', true)
                                   ->where('enrollment_status', 'enrolled');
            
            // Add strand filter if assignment has strand
            if ($advisoryAssignment->strand) {
                $studentsQuery->where('strand', $advisoryAssignment->strand);
            }
            
            // Add track filter if assignment has track
            if ($advisoryAssignment->track) {
                $studentsQuery->where('track', $advisoryAssignment->track);
            }
            
            $students = $studentsQuery->orderBy('last_name')
                                    ->orderBy('first_name')
                                    ->get();
        }
        
        return view('teacher.advisory', compact(
            'advisoryAssignment',
            'students', 
            'className',
            'currentAcademicYear'
        ));
    }

    /**
     * Get individual student grades for advisory
     */
    public function getStudentGrades(Student $student)
    {
        try {
            $teacher = Auth::user();
            $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
            
            // Verify this teacher is the student's adviser
            $advisoryAssignment = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
                ->where('grade_level', $student->grade_level)
                ->where('section', $student->section)
                ->where('assignment_type', 'class_adviser')
                ->where('academic_year', $currentAcademicYear)
                ->where('status', 'active')
                ->first();
                
            if (!$advisoryAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not the adviser for this student.'
                ]);
            }
            
            // Get student's subjects based on grade level, strand, and track
            $subjects = Subject::where('grade_level', $student->grade_level)
                ->where('academic_year', $currentAcademicYear)
                ->where('is_active', true);
                
            if ($student->strand) {
                $subjects->where(function($query) use ($student) {
                    $query->whereNull('strand')
                          ->orWhere('strand', $student->strand);
                });
            }
            
            if ($student->track) {
                $subjects->where(function($query) use ($student) {
                    $query->whereNull('track')
                          ->orWhere('track', $student->track);
                });
            }
            
            $subjects = $subjects->get();
            
            // Get grades for each subject and quarter
            $gradesData = [];
            $quarters = ['1st', '2nd', '3rd', '4th'];
            
            foreach ($subjects as $subject) {
                $subjectGrades = [
                    'subject_name' => $subject->subject_name,
                    'quarters' => []
                ];
                
                foreach ($quarters as $quarter) {
                    $grade = Grade::where('student_id', $student->id)
                        ->where('subject_id', $subject->id)
                        ->where('quarter', $quarter)
                        ->where('academic_year', $currentAcademicYear)
                        ->first();
                        
                    $subjectGrades['quarters'][$quarter] = $grade ? $grade->grade : null;
                }
                
                $gradesData[] = $subjectGrades;
            }
            
            // Calculate averages per quarter
            $quarterAverages = [];
            foreach ($quarters as $quarter) {
                $quarterGrades = [];
                foreach ($gradesData as $subjectData) {
                    if ($subjectData['quarters'][$quarter] !== null) {
                        $quarterGrades[] = $subjectData['quarters'][$quarter];
                    }
                }
                $quarterAverages[$quarter] = !empty($quarterGrades) ? round(array_sum($quarterGrades) / count($quarterGrades), 2) : null;
            }
            
            $html = view('teacher.student-grades', [
                'student' => $student,
                'gradesData' => $gradesData,
                'quarterAverages' => $quarterAverages,
                'currentAcademicYear' => $currentAcademicYear
            ])->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading student grades: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get all advisory students grades
     */
    public function getAllAdvisoryGrades()
    {
        try {
            $teacher = Auth::user();
            $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
            
            // Get teacher's advisory assignment
            $advisoryAssignment = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
                ->where('assignment_type', 'class_adviser')
                ->where('academic_year', $currentAcademicYear)
                ->where('status', 'active')
                ->first();
                
            if (!$advisoryAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have an advisory class assigned.'
                ]);
            }
            
            // Get all students in advisory class (include quarterly and monthly payers)
            $students = Student::where('grade_level', $advisoryAssignment->grade_level)
                ->where('section', $advisoryAssignment->section)
                ->where('academic_year', $currentAcademicYear)
                ->where('enrollment_status', 'enrolled')
                ->where('is_active', true)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
                
            // Get subjects for this grade level/strand/track
            $subjects = Subject::where('grade_level', $advisoryAssignment->grade_level)
                ->where('academic_year', $currentAcademicYear)
                ->where('is_active', true);
                
            if ($advisoryAssignment->strand) {
                $subjects->where(function($query) use ($advisoryAssignment) {
                    $query->whereNull('strand')
                          ->orWhere('strand', $advisoryAssignment->strand);
                });
            }
            
            if ($advisoryAssignment->track) {
                $subjects->where(function($query) use ($advisoryAssignment) {
                    $query->whereNull('track')
                          ->orWhere('track', $advisoryAssignment->track);
                });
            }
            
            $subjects = $subjects->get();
            
            // Get grades for all students (1st quarter for now)
            $studentsData = [];
            
            foreach ($students as $student) {
                $studentGrades = [];
                $studentTotal = 0;
                $subjectCount = 0;
                
                foreach ($subjects as $subject) {
                    $grade = Grade::where('student_id', $student->id)
                        ->where('subject_id', $subject->id)
                        ->where('quarter', '1st')
                        ->where('academic_year', $currentAcademicYear)
                        ->first();
                        
                    if ($grade) {
                        $studentGrades[$subject->id] = $grade->grade;
                        $studentTotal += $grade->grade;
                        $subjectCount++;
                    } else {
                        $studentGrades[$subject->id] = null;
                    }
                }
                
                $studentAverage = $subjectCount > 0 ? round($studentTotal / $subjectCount, 2) : null;
                
                $studentsData[] = [
                    'student' => $student,
                    'grades' => $studentGrades,
                    'average' => $studentAverage
                ];
            }
            
            // Sort by average (highest first) and assign rankings
            $studentsData = collect($studentsData)->sortByDesc('average')->values()->all();
            foreach ($studentsData as $index => &$studentData) {
                $studentData['ranking'] = $studentData['average'] !== null ? $index + 1 : null;
            }
            
            // Calculate class statistics
            $allAverages = collect($studentsData)->pluck('average')->filter()->values();
            $classAverage = $allAverages->count() > 0 ? round($allAverages->avg(), 2) : 0;
            $highestGrade = $allAverages->count() > 0 ? $allAverages->max() : 0;
            $topStudent = collect($studentsData)->where('average', $highestGrade)->first();
            
            $className = $advisoryAssignment->grade_level . ' - ' . $advisoryAssignment->section;
            if ($advisoryAssignment->strand) {
                $className .= ' - ' . $advisoryAssignment->strand;
                if ($advisoryAssignment->track) {
                    $className .= ' - ' . $advisoryAssignment->track;
                }
            }
            
            $html = view('teacher.all-advisory-grades', [
                'studentsData' => $studentsData,
                'subjects' => $subjects,
                'className' => $className,
                'classAverage' => $classAverage,
                'highestGrade' => $highestGrade,
                'topStudent' => $topStudent,
                'totalStudents' => count($studentsData),
                'gradedStudents' => $allAverages->count(),
                'currentAcademicYear' => $currentAcademicYear
            ])->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading advisory grades: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Generate individual student report card PDF
     */
    public function generateStudentReportCard(Student $student)
    {
        // For now, return a simple response
        // In production, this would generate an actual PDF
        return response()->json([
            'success' => false,
            'message' => 'PDF generation not implemented yet. This would generate a report card for ' . $student->full_name
        ]);
    }
    
    /**
     * Generate all advisory students report cards PDF
     */
    public function generateAllReportCards()
    {
        // For now, return a simple response
        // In production, this would generate PDFs for all advisory students
        return response()->json([
            'success' => false,
            'message' => 'Bulk PDF generation not implemented yet. This would generate report cards for all advisory students.'
        ]);
    }
}
