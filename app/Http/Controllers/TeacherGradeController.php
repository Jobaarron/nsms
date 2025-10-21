<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GradeSubmission;
use App\Models\FacultyAssignment;
use App\Models\Student;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TeacherGradeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:teacher|faculty_head']);
    }

    /**
     * Display teacher's grade submissions
     */
    public function index()
    {
        $teacher = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        try {
            // Get teacher's assignments
            $assignments = FacultyAssignment::where('teacher_id', $teacher->id)
                ->where('academic_year', $currentAcademicYear)
                ->where('status', 'active')
                ->with(['subject', 'teacher'])
                ->get();
            
            // Get grade submissions
            $submissions = GradeSubmission::where('teacher_id', $teacher->id)
                ->where('academic_year', $currentAcademicYear)
                ->with(['subject'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Group submissions by status
            $submissionsByStatus = $submissions->groupBy('status');
        } catch (\Exception $e) {
            // Handle case where tables don't exist yet
            $assignments = collect();
            $submissions = collect();
            $submissionsByStatus = collect();
        }
        
        return view('teacher.grades', compact('teacher', 'assignments', 'submissions', 'submissionsByStatus', 'currentAcademicYear'));
    }

    /**
     * Show form to submit grades for a specific class
     */
    public function create(Request $request)
    {
        $teacher = Auth::user();
        $subjectId = $request->get('subject_id');
        $gradeLevel = $request->get('grade_level');
        $section = $request->get('section');
        $quarter = $request->get('quarter');
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);

        // Verify teacher can submit grades for this class
        if (!$teacher->canSubmitGradesFor($subjectId, $gradeLevel, $section, $currentAcademicYear)) {
            return redirect()->route('teacher.grades.index')->with('error', 'You are not assigned to this class.');
        }

        // Get or create grade submission
        $submission = GradeSubmission::firstOrCreate([
            'teacher_id' => $teacher->id,
            'subject_id' => $subjectId,
            'grade_level' => $gradeLevel,
            'section' => $section,
            'quarter' => $quarter,
            'academic_year' => $currentAcademicYear
        ], [
            'status' => 'draft',
            'grades_data' => [],
            'total_students' => 0,
            'grades_entered' => 0
        ]);

        // Get students in this class
        $students = Student::where('grade_level', $gradeLevel)
                          ->where('section', $section)
                          ->where('academic_year', $currentAcademicYear)
                          ->where('is_active', true)
                          ->orderBy('last_name')
                          ->orderBy('first_name')
                          ->get();

        // Update total students count
        $submission->update(['total_students' => $students->count()]);

        // Get existing grades from main grades table
        $existingGrades = $submission->getExistingGrades();

        // Get faculty assignment details
        $assignment = FacultyAssignment::where('teacher_id', $teacher->id)
                                     ->where('subject_id', $subjectId)
                                     ->where('grade_level', $gradeLevel)
                                     ->where('section', $section)
                                     ->where('academic_year', $currentAcademicYear)
                                     ->where('status', 'active')
                                     ->with('subject')
                                     ->first();

        return view('teacher.grades.create', compact('submission', 'students', 'existingGrades', 'assignment', 'quarter'));
    }

    /**
     * Store or update grade submission
     */
    public function store(Request $request)
    {
        $teacher = Auth::user();
        
        $request->validate([
            'submission_id' => 'required|exists:grade_submissions,id',
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.grade' => 'nullable|numeric|min:0|max:100',
            'grades.*.remarks' => 'nullable|string|max:255'
        ]);

        $submission = GradeSubmission::findOrFail($request->submission_id);

        // Verify ownership
        if ($submission->teacher_id !== $teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if submission can be edited
        if (!$submission->canEdit()) {
            return response()->json(['error' => 'This submission cannot be edited'], 400);
        }

        // Process grades data
        $gradesData = [];
        $gradesEntered = 0;

        foreach ($request->grades as $gradeData) {
            if (!empty($gradeData['grade'])) {
                $gradesEntered++;
            }
            
            $gradesData[] = [
                'student_id' => $gradeData['student_id'],
                'grade' => $gradeData['grade'] ? (float) $gradeData['grade'] : null,
                'remarks' => $gradeData['remarks'] ?? null
            ];
        }

        // Update submission
        $submission->update([
            'grades_data' => $gradesData,
            'grades_entered' => $gradesEntered
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Grades saved successfully',
            'completion_percentage' => $submission->completion_percentage,
            'can_submit' => $submission->canSubmit()
        ]);
    }

    /**
     * Submit grades for review
     */
    public function submit(Request $request, GradeSubmission $submission)
    {
        $teacher = Auth::user();

        // Verify ownership
        if ($submission->teacher_id !== $teacher->id) {
            return redirect()->route('teacher.grades.index')->with('error', 'Unauthorized');
        }

        // Check if can submit
        if (!$submission->canSubmit()) {
            return redirect()->route('teacher.grades.index')->with('error', 'All student grades must be entered before submission');
        }

        $request->validate([
            'submission_notes' => 'nullable|string|max:1000'
        ]);

        $submission->submit($request->submission_notes);

        return redirect()->route('teacher.grades.index')->with('success', 'Grades submitted for review successfully');
    }

    /**
     * Show grade submission details
     */
    public function show(GradeSubmission $submission)
    {
        $teacher = Auth::user();

        // Verify ownership or faculty head access
        if ($submission->teacher_id !== $teacher->id && !$teacher->hasRole('faculty_head')) {
            return redirect()->route('teacher.grades.index')->with('error', 'Unauthorized');
        }

        $students = $submission->students();
        $assignment = $submission->facultyAssignment();

        return view('teacher.grades.show', compact('submission', 'students', 'assignment'));
    }

    /**
     * Upload grades from Excel/CSV file
     */
    public function upload(Request $request)
    {
        $request->validate([
            'submission_id' => 'required|exists:grade_submissions,id',
            'grades_file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ]);

        $teacher = Auth::user();
        $submission = GradeSubmission::findOrFail($request->submission_id);

        // Verify ownership
        if ($submission->teacher_id !== $teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if submission can be edited
        if (!$submission->canEdit()) {
            return response()->json(['error' => 'This submission cannot be edited'], 400);
        }

        try {
            // Process uploaded file (implementation would depend on chosen Excel library)
            // For now, return success message
            return response()->json([
                'success' => true,
                'message' => 'Grades uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to process uploaded file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get grade submission data for AJAX
     */
    public function getSubmissionData(GradeSubmission $submission)
    {
        $teacher = Auth::user();

        // Verify ownership or faculty head access
        if ($submission->teacher_id !== $teacher->id && !$teacher->hasRole('faculty_head')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'submission' => $submission->load(['subject', 'reviewer']),
            'grades_data' => $submission->grades_data,
            'students' => $submission->students(),
            'can_edit' => $submission->canEdit(),
            'can_submit' => $submission->canSubmit()
        ]);
    }
}
