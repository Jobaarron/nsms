<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;

class StudentGradeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student');
    }

    /**
     * Display student's grades
     */
    public function index()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }

        // Payment check is handled in the main grades view

        // Get current academic year
        $currentAcademicYear = $student->academic_year;
        
        // Get available quarters with grades
        $availableQuarters = $this->getAvailableQuarters($student, $currentAcademicYear);
        
        // Get academic performance summary
        $performance = $student->getAcademicPerformance($currentAcademicYear);
        
        return view('student.grades', compact('student', 'availableQuarters', 'performance', 'currentAcademicYear'));
    }

    /**
     * Display grades for a specific quarter
     */
    public function quarter(Request $request, $quarter)
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }

        // Validate quarter
        if (!in_array($quarter, ['1st', '2nd', '3rd', '4th'])) {
            return redirect()->route('student.grades.index')->with('error', 'Invalid quarter selected.');
        }

        // Check payment for this quarter
        if (!$student->hasPaidForQuarter($quarter)) {
            return view('student.grades', compact('student', 'quarter'));
        }

        // Get grades for the quarter
        $grades = $student->getGradesForQuarter($quarter, $student->academic_year);
        
        // Calculate quarter statistics
        $stats = $this->calculateQuarterStats($grades);
        
        // Get grading system (quarterly vs semester)
        $gradingSystem = Grade::getGradingSystem($student->grade_level);
        
        return view('student.grades', compact('student', 'grades', 'quarter', 'stats', 'gradingSystem'));
    }

    /**
     * Get grades data for AJAX requests
     */
    public function getGradesData(Request $request)
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $quarter = $request->get('quarter');
        
        if (!$student->hasPaidForQuarter($quarter)) {
            return response()->json(['error' => 'Payment required for this quarter'], 403);
        }

        $grades = $student->getGradesForQuarter($quarter, $student->academic_year);
        
        $gradesData = $grades->map(function ($grade) {
            return [
                'subject' => $grade->subject->subject_name,
                'teacher' => $grade->teacher->name,
                'grade' => $grade->grade,
                'remarks' => $grade->remarks,
                'status' => $grade->getPassingStatus(),
                'is_passing' => $grade->isPassing(),
                'submitted_at' => $grade->submitted_at ? $grade->submitted_at->format('M d, Y') : null
            ];
        });

        return response()->json([
            'grades' => $gradesData,
            'stats' => $this->calculateQuarterStats($grades)
        ]);
    }

    /**
     * Display grade report (printable)
     */
    public function report($quarter = null)
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('student.login');
        }

        // If no quarter specified, show all available quarters
        if (!$quarter) {
            $performance = $student->getAcademicPerformance($student->academic_year);
            $allGrades = [];
            
            foreach (['1st', '2nd', '3rd', '4th'] as $q) {
                if ($student->hasPaidForQuarter($q)) {
                    $allGrades[$q] = $student->getGradesForQuarter($q, $student->academic_year);
                }
            }
            
            return view('student.grades', compact('student', 'allGrades', 'performance'));
        }

        // Single quarter report
        if (!$student->hasPaidForQuarter($quarter)) {
            return redirect()->route('student.grades.index')->with('error', 'Payment required for this quarter.');
        }

        $grades = $student->getGradesForQuarter($quarter, $student->academic_year);
        $stats = $this->calculateQuarterStats($grades);
        
        return view('student.grades', compact('student', 'grades', 'quarter', 'stats'));
    }

    /**
     * Get available quarters with grades
     */
    private function getAvailableQuarters($student, $academicYear)
    {
        $quarters = [];
        
        foreach (['1st', '2nd', '3rd', '4th'] as $quarter) {
            $hasGrades = Grade::where('student_id', $student->id)
                            ->where('quarter', $quarter)
                            ->where('academic_year', $academicYear)
                            ->where('is_final', true)
                            ->exists();
                            
            $hasPaid = $student->hasPaidForQuarter($quarter);
            
            if ($hasGrades && $hasPaid) {
                $quarters[] = [
                    'quarter' => $quarter,
                    'has_grades' => true,
                    'has_paid' => true,
                    'grade_count' => Grade::where('student_id', $student->id)
                                        ->where('quarter', $quarter)
                                        ->where('academic_year', $academicYear)
                                        ->where('is_final', true)
                                        ->count()
                ];
            }
        }
        
        return $quarters;
    }

    /**
     * Calculate quarter statistics
     */
    private function calculateQuarterStats($grades)
    {
        if ($grades->isEmpty()) {
            return [
                'total_subjects' => 0,
                'average_grade' => 0,
                'passing_count' => 0,
                'failing_count' => 0,
                'highest_grade' => 0,
                'lowest_grade' => 0
            ];
        }

        return [
            'total_subjects' => $grades->count(),
            'average_grade' => round($grades->avg('grade'), 2),
            'passing_count' => $grades->where('grade', '>=', 75)->count(),
            'failing_count' => $grades->where('grade', '<', 75)->count(),
            'highest_grade' => $grades->max('grade'),
            'lowest_grade' => $grades->min('grade')
        ];
    }
}
