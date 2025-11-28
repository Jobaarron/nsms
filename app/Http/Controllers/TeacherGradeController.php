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
            // Get teacher record first
            $teacherRecord = \App\Models\Teacher::where('user_id', $teacher->id)->first();
            
            // Get teacher's assignments
            $assignments = collect();
            $submissions = collect();
            
            if ($teacherRecord) {
                $assignments = FacultyAssignment::where('teacher_id', $teacherRecord->id)
                    ->where('academic_year', $currentAcademicYear)
                    ->where('status', 'active')
                    ->with(['subject', 'teacher.user'])
                    ->get();
                
                // Get grade submissions
                $submissions = GradeSubmission::where('teacher_id', $teacherRecord->id)
                    ->where('academic_year', $currentAcademicYear)
                    ->with(['subject'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
            
            // Group submissions by status
            $submissionsByStatus = $submissions->groupBy('status');
            
            // Calculate real-time statistics
            $stats = [
                'pending' => $submissions->where('status', 'draft')->count(),
                'submitted' => $submissions->where('status', 'submitted')->count(), 
                'approved' => $submissions->where('status', 'approved')->count(),
                'revised' => $submissions->where('status', 'rejected')->count(),
            ];
        } catch (\Exception $e) {
            // Handle case where tables don't exist yet
            $assignments = collect();
            $submissions = collect();
            $submissionsByStatus = collect();
            $stats = [
                'pending' => 0,
                'submitted' => 0,
                'approved' => 0,
                'revised' => 0,
            ];
        }
        
        return view('teacher.grades', compact('assignments', 'submissions', 'submissionsByStatus', 'stats', 'currentAcademicYear'));
    }

    /**
     * Show grade entry form for specific assignment
     */
    public function showGradeEntry(FacultyAssignment $assignment)
    {
        $teacher = Auth::user();
        $teacherRecord = \App\Models\Teacher::where('user_id', $teacher->id)->first();
        
        // Verify this assignment belongs to the current teacher
        if (!$teacherRecord || $assignment->teacher_id !== $teacherRecord->id) {
            abort(403, 'Unauthorized access to this assignment.');
        }

        // Check if grade submission is active
        $isActive = \App\Models\Setting::get('grade_submission_active', false);
        if (!$isActive) {
            return redirect()->route('teacher.grades')->with('error', 'Grade submission is currently disabled by the faculty head.');
        }

        // Validate that the requested quarter is active
        $requestedQuarter = request('quarter', '1st');
        $quarterKey = strtolower(str_replace(['st', 'nd', 'rd', 'th'], '', $requestedQuarter));
        $isQuarterActive = \App\Models\Setting::get("grade_submission_q{$quarterKey}_active", false);
        
        if (!$isQuarterActive) {
            return redirect()->route('teacher.grades')->with('error', "The {$requestedQuarter} quarter is not currently active for grade submission. Please contact the faculty head.");
        }

        // Get students automatically enrolled in this class
        $studentsQuery = Student::where('grade_level', $assignment->grade_level)
                               ->where('section', $assignment->section)
                               ->where('academic_year', $assignment->academic_year)
                               ->where('is_active', true);

        // For Senior High School, match strand and track
        if (in_array($assignment->grade_level, ['Grade 11', 'Grade 12'])) {
            if ($assignment->strand) {
                $studentsQuery->where('strand', $assignment->strand);
            }
            
            if ($assignment->track) {
                $studentsQuery->where('track', $assignment->track);
            }
        }

        $students = $studentsQuery->orderBy('last_name')
                                 ->orderBy('first_name')
                                 ->get();

        // Get or create grade submission record
        $submission = GradeSubmission::firstOrCreate(
            [
                'teacher_id' => $teacherRecord->id,
                'subject_id' => $assignment->subject_id,
                'grade_level' => $assignment->grade_level,
                'section' => $assignment->section,
                'academic_year' => $assignment->academic_year,
                'quarter' => request('quarter', '1st') // Default to 1st quarter
            ],
            [
                'status' => 'draft',
                'total_students' => $students->count(),
                'grades_entered' => 0,
                'grades_data' => []
            ]
        );

        // Get existing grades for this submission
        $existingGrades = [];
        if ($submission->grades_data) {
            foreach ($submission->grades_data as $gradeData) {
                $existingGrades[$gradeData['student_id']] = $gradeData;
            }
        }

        return view('teacher.grade-entry', compact(
            'assignment', 
            'students', 
            'submission', 
            'existingGrades'
        ));
    }

    /**
     * Submit grades for review
     */
    public function submitGrades(Request $request, FacultyAssignment $assignment)
    {
        $teacher = Auth::user();
        $teacherRecord = \App\Models\Teacher::where('user_id', $teacher->id)->first();
        
        // Verify this assignment belongs to the current teacher
        if (!$teacherRecord || $assignment->teacher_id !== $teacherRecord->id) {
            abort(403, 'Unauthorized access to this assignment.');
        }

        $request->validate([
            'quarter' => 'required|in:1st,2nd,3rd,4th',
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.grade' => 'nullable|numeric|min:0|max:100',
            'grades.*.remarks' => 'nullable|string|max:255',
            'action' => 'required|in:save_draft,submit_for_review'
        ]);

        // Validate that the submitted quarter is active (prevent form tampering)
        $submittedQuarter = $request->quarter;
        $quarterKey = strtolower(str_replace(['st', 'nd', 'rd', 'th'], '', $submittedQuarter));
        $isQuarterActive = \App\Models\Setting::get("grade_submission_q{$quarterKey}_active", false);
        
        if (!$isQuarterActive) {
            return redirect()->route('teacher.grades')->with('error', "The {$submittedQuarter} quarter is not currently active for grade submission. Unauthorized attempt detected.");
        }

        // Get or update grade submission
        $submission = GradeSubmission::updateOrCreate(
            [
                'teacher_id' => $teacherRecord->id,
                'subject_id' => $assignment->subject_id,
                'grade_level' => $assignment->grade_level,
                'section' => $assignment->section,
                'academic_year' => $assignment->academic_year,
                'quarter' => $request->quarter
            ],
            [
                'grades_data' => $request->grades,
                'total_students' => count($request->grades),
                'grades_entered' => count(array_filter($request->grades, function($grade) {
                    return !empty($grade['grade']);
                })),
                'submission_notes' => $request->notes,
                'status' => $request->action === 'submit_for_review' ? 'submitted' : 'draft',
                'submitted_at' => $request->action === 'submit_for_review' ? now() : null
            ]
        );

        $message = $request->action === 'submit_for_review' 
            ? 'Grades submitted successfully for faculty head review!'
            : 'Grades saved as draft successfully!';

        return redirect()->route('teacher.grades')->with('success', $message);
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
            return redirect()->route('teacher.grades')->with('error', 'You are not assigned to this class.');
        }

        // Get or create grade submission
        $submission = GradeSubmission::firstOrCreate([
            'teacher_id' => $teacher->teacher->id,
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
        $assignment = FacultyAssignment::where('teacher_id', $teacher->teacher->id)
                                     ->where('subject_id', $subjectId)
                                     ->where('grade_level', $gradeLevel)
                                     ->where('section', $section)
                                     ->where('academic_year', $currentAcademicYear)
                                     ->where('status', 'active')
                                     ->with('subject')
                                     ->first();

        return view('teacher.grades', compact('submission', 'students', 'existingGrades', 'assignment', 'quarter'));
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
        if ($submission->teacher_id !== $teacher->teacher->id) {
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
        if ($submission->teacher_id !== $teacher->teacher->id) {
            return redirect()->route('teacher.grades')->with('error', 'Unauthorized');
        }

        // Check if can submit
        if (!$submission->canSubmit()) {
            return redirect()->route('teacher.grades')->with('error', 'All student grades must be entered before submission');
        }

        $request->validate([
            'submission_notes' => 'nullable|string|max:1000'
        ]);

        $submission->submit($request->submission_notes);

        return redirect()->route('teacher.grades')->with('success', 'Grades submitted for review successfully');
    }

    /**
     * Show grade submission details
     */
    public function show(GradeSubmission $submission)
    {
        $teacher = Auth::user();

        // Verify ownership or faculty head access
        if ($submission->teacher_id !== $teacher->teacher->id && !$teacher->hasRole('faculty_head')) {
            return redirect()->route('teacher.grades')->with('error', 'Unauthorized');
        }

        $students = $submission->students();
        $assignment = $submission->facultyAssignment();

        return view('teacher.grades', compact('submission', 'students', 'assignment'));
    }

    /**
     * Upload grades from Excel/CSV file
     */
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'submission_id' => 'required|exists:grade_submissions,id',
                'grades_file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        $teacher = Auth::user();
        $teacherRecord = \App\Models\Teacher::where('user_id', $teacher->id)->first();
        $submission = GradeSubmission::findOrFail($request->submission_id);

        // Verify ownership
        if ($submission->teacher_id !== $teacherRecord->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if submission can be edited
        if (!$submission->canEdit()) {
            return response()->json(['error' => 'This submission cannot be edited'], 400);
        }

        try {
            // Get expected students for this submission using student_id (NS-25XXX format)
            // Use the same filtering logic as the grade entry form
            $studentsQuery = Student::where('grade_level', $submission->grade_level)
                                   ->where('section', $submission->section)
                                   ->where('academic_year', $submission->academic_year)
                                   ->where('is_active', true);

            // Get the faculty assignment to check for strand/track filtering
            $assignment = FacultyAssignment::where('teacher_id', $submission->teacher_id)
                                         ->where('subject_id', $submission->subject_id)
                                         ->where('grade_level', $submission->grade_level)
                                         ->where('section', $submission->section)
                                         ->where('academic_year', $submission->academic_year)
                                         ->first();

            // For Senior High School, match strand and track if assignment has them
            if ($assignment && in_array($submission->grade_level, ['Grade 11', 'Grade 12'])) {
                if ($assignment->strand) {
                    $studentsQuery->where('strand', $assignment->strand);
                }
                
                if ($assignment->track) {
                    $studentsQuery->where('track', $assignment->track);
                }
            }

            $expectedStudents = $studentsQuery->get()->keyBy('student_id'); // Key by student_id (NS-25XXX)

            // Process Excel/CSV file
            $file = $request->file('grades_file');
            $fileExtension = strtolower($file->getClientOriginalExtension());
            
            // Simple CSV parsing for now (can be enhanced with Excel library later)
            if ($fileExtension === 'csv') {
                $handle = fopen($file->getPathname(), 'r');
                $data = [];
                while (($row = fgetcsv($handle)) !== false) {
                    $data[] = $row;
                }
                fclose($handle);
            } else {
                // For Excel files, we'll need the Excel library
                return response()->json([
                    'error' => 'Excel files not supported yet. Please use CSV format.'
                ], 400);
            }
            
            $gradesData = [];
            $errors = [];
            $processed = 0;
            
            foreach ($data as $index => $row) {
                // Skip header row
                if ($index === 0) continue;
                
                // Map columns according to new format
                $studentId = $row[0] ?? null;      // NS-25XXX format
                $lastName = $row[1] ?? null;       // Last name
                $firstName = $row[2] ?? null;      // First name
                $middleName = $row[3] ?? null;     // Middle name (optional)
                $grade = $row[4] ?? null;          // Grade
                $remarks = $row[5] ?? null;        // Remarks (optional)
                
                $rowNumber = $index + 1;
                
                // Validate required fields (grade is optional for draft submissions)
                if (empty($studentId) || empty($lastName) || empty($firstName)) {
                    $errors[] = "Row {$rowNumber}: Missing required fields (student_id, last_name, first_name)";
                    continue;
                }
                
                // Validate student_id format (NS-25XXX)
                if (!preg_match('/^NS-25\d{3}$/', $studentId)) {
                    $errors[] = "Row {$rowNumber}: Invalid student_id format '{$studentId}'. Must be NS-25XXX (e.g., NS-25001)";
                    continue;
                }
                
                // Validate grade range (if grade is provided)
                if (!empty($grade) && (!is_numeric($grade) || $grade < 0 || $grade > 100)) {
                    $errors[] = "Row {$rowNumber}: Invalid grade '{$grade}'. Must be 0-100";
                    continue;
                }
                
                // Check if student exists in expected list
                if (!isset($expectedStudents[$studentId])) {
                    // Add more detailed error information
                    $availableIds = $expectedStudents->keys()->take(5)->implode(', ');
                    $totalStudents = $expectedStudents->count();
                    $classInfo = $submission->grade_level . ' - ' . $submission->section;
                    if ($assignment && $assignment->strand) {
                        $classInfo .= ' - ' . $assignment->strand;
                        if ($assignment->track) {
                            $classInfo .= ' - ' . $assignment->track;
                        }
                    }
                    
                    $errors[] = "Row {$rowNumber}: Student ID '{$studentId}' not found in class {$classInfo}. " .
                               "Found {$totalStudents} students. " .
                               ($totalStudents > 0 ? "Sample IDs: {$availableIds}" . ($totalStudents > 5 ? '...' : '') : "No students found in this class.");
                    continue;
                }
                
                $student = $expectedStudents[$studentId];
                
                // Verify student name details match
                $nameMatch = true;
                $nameErrors = [];
                
                if (trim(strtolower($student->last_name)) !== trim(strtolower($lastName))) {
                    $nameMatch = false;
                    $nameErrors[] = "last name mismatch";
                }
                
                if (trim(strtolower($student->first_name)) !== trim(strtolower($firstName))) {
                    $nameMatch = false;
                    $nameErrors[] = "first name mismatch";
                }
                
                // Check middle name only if both Excel and database have values
                if (!empty($middleName) && !empty($student->middle_name)) {
                    if (trim(strtolower($student->middle_name)) !== trim(strtolower($middleName))) {
                        $nameMatch = false;
                        $nameErrors[] = "middle name mismatch";
                    }
                }
                
                if (!$nameMatch) {
                    $errors[] = "Row {$rowNumber}: Student name mismatch for ID '{$studentId}'. " . 
                               "Expected: {$student->first_name} " . 
                               ($student->middle_name ? $student->middle_name . ' ' : '') . 
                               "{$student->last_name}. Issues: " . implode(', ', $nameErrors);
                    continue;
                }
                
                // Add to grades data
                $gradesData[] = [
                    'student_id' => $student->id, // Use database primary key ID
                    'grade' => (float) $grade,
                    'remarks' => $remarks ?: null
                ];
                
                $processed++;
            }
            
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File contains errors. Please check the details below and correct your file.',
                    'errors' => $errors,
                    'processed' => $processed,
                    'total_rows' => count($data) - 1 // Exclude header row
                ], 400);
            }
            
            if (empty($gradesData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid grade data found in file'
                ], 400);
            }
            
            // Update submission with uploaded grades
            $submission->update([
                'grades_data' => $gradesData,
                'total_students' => $expectedStudents->count(),
                'grades_entered' => count($gradesData)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Successfully uploaded {$processed} grades",
                'processed' => $processed,
                'total_expected' => $expectedStudents->count(),
                'completion_percentage' => $submission->completion_percentage
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
        if ($submission->teacher_id !== $teacher->teacher->id && !$teacher->hasRole('faculty_head')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'submission' => $submission->load(['subject', 'reviewer']),
            'grades_data' => $submission->grades_data,
            'total_students' => $submission->total_students
        ]);
    }

    /**
     * Get submission statistics for AJAX real-time updates
     */
    public function getSubmissionStats()
    {
        $teacher = Auth::user();
        $teacherRecord = \App\Models\Teacher::where('user_id', $teacher->id)->first();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);

        if (!$teacherRecord) {
            return response()->json(['error' => 'Teacher record not found'], 404);
        }

        try {
            // Get all submissions for this teacher
            $submissions = GradeSubmission::where('teacher_id', $teacherRecord->id)
                ->where('academic_year', $currentAcademicYear)
                ->get();

            // Group submissions by status and count them
            $stats = [
                'pending' => $submissions->where('status', 'draft')->count(),
                'submitted' => $submissions->where('status', 'submitted')->count(),
                'approved' => $submissions->where('status', 'approved')->count(),
                'revised' => $submissions->where('status', 'rejected')->count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'pending' => 0,
                'submitted' => 0,
                'approved' => 0,
                'revised' => 0,
            ]);
        }
    }

    /**
     * Finalize approved grades (makes them visible to students)
     */
    public function finalizeGrades(GradeSubmission $submission)
    {
        $teacher = Auth::user();
        $teacherRecord = \App\Models\Teacher::where('user_id', $teacher->id)->first();

        if (!$teacherRecord || $submission->teacher_id !== $teacherRecord->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($submission->status !== 'approved') {
            return response()->json(['error' => 'Only approved grades can be finalized'], 400);
        }

        try {
            $submission->finalizeByTeacher();
            return response()->json(['success' => true, 'message' => 'Grades finalized and visible to students']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
