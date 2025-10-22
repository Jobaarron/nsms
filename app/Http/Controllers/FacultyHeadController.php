<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FacultyAssignment;
use App\Models\GradeSubmission;
use App\Models\ClassSchedule;
use App\Models\User;
use App\Models\Subject;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class FacultyHeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:faculty_head');
    }

    /**
     * Faculty Head Dashboard
     */
    public function index()
    {
        $facultyHead = Auth::guard('faculty_head')->user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get statistics
        $stats = [
            'total_teachers' => User::role('teacher')->count(),
            'total_assignments' => FacultyAssignment::where('academic_year', $currentAcademicYear)->where('status', 'active')->count(),
            'pending_submissions' => GradeSubmission::where('status', 'submitted')->count(),
            'total_subjects' => Subject::where('academic_year', $currentAcademicYear)->where('is_active', true)->count()
        ];

        // Get recent grade submissions for review
        $recentSubmissions = GradeSubmission::where('status', 'submitted')
                                          ->with(['teacher', 'subject'])
                                          ->orderBy('submitted_at', 'desc')
                                          ->limit(10)
                                          ->get();

        // Get recent assignments made
        $recentAssignments = $facultyHead->assignmentsMade()
                                       ->where('academic_year', $currentAcademicYear)
                                       ->with(['teacher', 'subject'])
                                       ->orderBy('created_at', 'desc')
                                       ->limit(10)
                                       ->get();

        return view('faculty-head.index', compact('facultyHead', 'stats', 'recentSubmissions', 'recentAssignments', 'currentAcademicYear'));
    }

    /**
     * Show login form for Faculty Head
     */
    public function showLoginForm()
    {
        return view('faculty-head.login');
    }

    /**
     * Handle Faculty Head login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'employee_id' => 'required|string',
            'password' => 'required',
        ]);

        if (Auth::guard('faculty_head')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('faculty-head.dashboard'));
        }

        return back()->withErrors([
            'employee_id' => 'The provided credentials do not match our records.',
        ])->onlyInput('employee_id');
    }

    /**
     * Logout Faculty Head
     */
    public function logout(Request $request)
    {
        Auth::guard('faculty_head')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('faculty-head.login');
    }

    /**
     * Assign adviser per class
     */
    public function assignAdviser()
    {
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get all teachers
        $teachers = User::role('teacher')->with('teacher')->get();
        
        // Get all class sections (grade_level + section combinations)
        $classes = Student::where('academic_year', $currentAcademicYear)
                         ->where('is_active', true)
                         ->select('grade_level', 'section')
                         ->distinct()
                         ->orderBy('grade_level')
                         ->orderBy('section')
                         ->get();
        
        // Get current class advisers
        $advisers = FacultyAssignment::where('academic_year', $currentAcademicYear)
                                   ->where('assignment_type', 'class_adviser')
                                   ->where('status', 'active')
                                   ->with(['teacher', 'subject'])
                                   ->get();

        return view('faculty-head.assign-adviser', compact('teachers', 'classes', 'advisers', 'currentAcademicYear'));
    }

    /**
     * Show assign teacher form
     */
    public function assignTeacherForm()
    {
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get all teachers
        $teachers = User::role('teacher')->with('teacher')->get();
        
        // Get all subjects
        $subjects = Subject::where('academic_year', $currentAcademicYear)->where('is_active', true)->get();
        
        // Get all assignments
        $assignments = FacultyAssignment::where('academic_year', $currentAcademicYear)
                                      ->with(['teacher', 'subject', 'assignedBy'])
                                      ->orderBy('grade_level')
                                      ->orderBy('section')
                                      ->get();

        // Group assignments by grade and section
        $assignmentsByClass = $assignments->groupBy(function($assignment) {
            return $assignment->grade_level . ' - ' . $assignment->section;
        });

        return view('faculty-head.assign-teacher', compact('teachers', 'subjects', 'assignments', 'assignmentsByClass', 'currentAcademicYear'));
    }

    /**
     * View submitted grades from teachers
     */
    public function viewGrades()
    {
        $submissions = GradeSubmission::with(['teacher', 'subject', 'reviewer'])
                                    ->whereIn('status', ['submitted', 'approved', 'rejected'])
                                    ->orderBy('status')
                                    ->orderBy('submitted_at', 'desc')
                                    ->get();

        $submissionsByStatus = $submissions->groupBy('status');

        return view('faculty-head.view-grades', compact('submissions', 'submissionsByStatus'));
    }

    /**
     * Approve/reject submitted grades from teachers
     */
    public function approveGrades()
    {
        $pendingSubmissions = GradeSubmission::where('status', 'submitted')
                                           ->with(['teacher', 'subject'])
                                           ->orderBy('submitted_at', 'desc')
                                           ->get();

        return view('faculty-head.approve-grades', compact('pendingSubmissions'));
    }

    /**
     * Activate grade submission
     */
    public function activateSubmission()
    {
        // Get current grade submission status (this would typically be stored in settings)
        $isActive = true; // This should come from a settings table or cache
        
        return view('faculty-head.activate-submission', compact('isActive'));
    }

    /**
     * Manage teacher assignments (legacy method)
     */
    public function assignments()
    {
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get all teachers
        $teachers = User::role('teacher')->with('teacher')->get();
        
        // Get all subjects
        $subjects = Subject::where('academic_year', $currentAcademicYear)->where('is_active', true)->get();
        
        // Get all assignments
        $assignments = FacultyAssignment::where('academic_year', $currentAcademicYear)
                                      ->with(['teacher', 'subject', 'assignedBy'])
                                      ->orderBy('grade_level')
                                      ->orderBy('section')
                                      ->get();

        // Group assignments by grade and section
        $assignmentsByClass = $assignments->groupBy(function($assignment) {
            return $assignment->grade_level . ' - ' . $assignment->section;
        });

        return view('faculty-head.assign-teacher', compact('teachers', 'subjects', 'assignments', 'assignmentsByClass', 'currentAcademicYear'));
    }

    /**
     * Assign teacher to subject/section
     */
    public function assignTeacher(Request $request)
    {
        $facultyHead = Auth::user();
        
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'grade_level' => 'required|string',
            'section' => 'required|string',
            'assignment_type' => 'required|in:subject_teacher,class_adviser',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);

        // Check if assignment already exists
        $existingAssignment = FacultyAssignment::isTeacherAssigned(
            $request->teacher_id,
            $request->subject_id,
            $request->grade_level,
            $request->section,
            $currentAcademicYear
        );

        if ($existingAssignment) {
            return redirect()->back()->with('error', 'Teacher is already assigned to this class.');
        }

        // Create assignment
        FacultyAssignment::create([
            'teacher_id' => $request->teacher_id,
            'subject_id' => $request->subject_id,
            'assigned_by' => $facultyHead->id,
            'grade_level' => $request->grade_level,
            'section' => $request->section,
            'academic_year' => $currentAcademicYear,
            'assignment_type' => $request->assignment_type,
            'status' => 'active',
            'assigned_date' => now(),
            'effective_date' => $request->effective_date,
            'notes' => $request->notes
        ]);

        return redirect()->route('faculty-head.assignments')->with('success', 'Teacher assigned successfully.');
    }

    /**
     * Remove teacher assignment
     */
    public function removeAssignment(FacultyAssignment $assignment)
    {
        $assignment->update(['status' => 'inactive', 'end_date' => now()]);
        
        return redirect()->route('faculty-head.assignments')->with('success', 'Teacher assignment removed successfully.');
    }

    /**
     * Grade submissions management
     */
    public function gradeSubmissions()
    {
        $submissions = GradeSubmission::with(['teacher', 'subject', 'reviewer'])
                                    ->orderBy('status')
                                    ->orderBy('submitted_at', 'desc')
                                    ->get();

        $submissionsByStatus = $submissions->groupBy('status');

        return view('faculty-head.view-grades', compact('submissions', 'submissionsByStatus'));
    }

    /**
     * Review grade submission
     */
    public function reviewSubmission(GradeSubmission $submission)
    {
        $students = $submission->students();
        $assignment = $submission->facultyAssignment();
        
        return view('faculty-head.approve-grades', compact('submission', 'students', 'assignment'));
    }

    /**
     * Approve grade submission
     */
    public function approveSubmission(Request $request, GradeSubmission $submission)
    {
        $request->validate([
            'review_notes' => 'nullable|string|max:1000'
        ]);

        $submission->approve(Auth::id(), $request->review_notes);

        return redirect()->route('faculty-head.grade-submissions')->with('success', 'Grade submission approved successfully.');
    }

    /**
     * Reject grade submission
     */
    public function rejectSubmission(Request $request, GradeSubmission $submission)
    {
        $request->validate([
            'review_notes' => 'required|string|max:1000'
        ]);

        $submission->reject(Auth::id(), $request->review_notes);

        return redirect()->route('faculty-head.grade-submissions')->with('success', 'Grade submission rejected.');
    }

    /**
     * Request revision for grade submission
     */
    public function requestRevision(Request $request, GradeSubmission $submission)
    {
        $request->validate([
            'review_notes' => 'required|string|max:1000'
        ]);

        $submission->requestRevision(Auth::id(), $request->review_notes);

        return redirect()->route('faculty-head.grade-submissions')->with('success', 'Revision requested for grade submission.');
    }

    /**
     * Class schedule management
     */
    public function schedules()
    {
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        $schedules = ClassSchedule::where('academic_year', $currentAcademicYear)
                                 ->with(['teacher', 'subject'])
                                 ->orderBy('day_of_week')
                                 ->orderBy('start_time')
                                 ->get();

        $schedulesByDay = $schedules->groupBy('day_of_week');

        return view('faculty-head.index', compact('schedules', 'schedulesByDay', 'currentAcademicYear'));
    }

    /**
     * Get assignment data for AJAX
     */
    public function getAssignmentData(Request $request)
    {
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        $assignments = FacultyAssignment::where('academic_year', $currentAcademicYear)
                                      ->where('status', 'active')
                                      ->with(['teacher', 'subject'])
                                      ->get();

        return response()->json(['assignments' => $assignments]);
    }

    /**
     * Get grade submission statistics
     */
    public function getSubmissionStats()
    {
        $stats = [
            'draft' => GradeSubmission::draft()->count(),
            'submitted' => GradeSubmission::submitted()->count(),
            'approved' => GradeSubmission::approved()->count(),
            'rejected' => GradeSubmission::rejected()->count()
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Store adviser assignment
     */
    public function storeAdviser(Request $request)
    {
        $facultyHead = Auth::guard('faculty_head')->user();
        
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'grade_level' => 'required|string',
            'section' => 'required|string',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);

        // Create class adviser assignment
        FacultyAssignment::create([
            'teacher_id' => $request->teacher_id,
            'subject_id' => null, // Class adviser doesn't need specific subject
            'assigned_by' => $facultyHead->user_id,
            'grade_level' => $request->grade_level,
            'section' => $request->section,
            'academic_year' => $currentAcademicYear,
            'assignment_type' => 'class_adviser',
            'status' => 'active',
            'assigned_date' => now(),
            'effective_date' => $request->effective_date,
            'notes' => $request->notes
        ]);

        return redirect()->route('faculty-head.assign-adviser')->with('success', 'Class adviser assigned successfully.');
    }

    /**
     * Store teacher assignment
     */
    public function storeTeacherAssignment(Request $request)
    {
        $facultyHead = Auth::guard('faculty_head')->user();
        
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'grade_level' => 'required|string',
            'section' => 'required|string',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);

        // Check if assignment already exists
        $existingAssignment = FacultyAssignment::isTeacherAssigned(
            $request->teacher_id,
            $request->subject_id,
            $request->grade_level,
            $request->section,
            $currentAcademicYear
        );

        if ($existingAssignment) {
            return redirect()->back()->with('error', 'Teacher is already assigned to this class.');
        }

        // Create subject teacher assignment
        FacultyAssignment::create([
            'teacher_id' => $request->teacher_id,
            'subject_id' => $request->subject_id,
            'assigned_by' => $facultyHead->user_id,
            'grade_level' => $request->grade_level,
            'section' => $request->section,
            'academic_year' => $currentAcademicYear,
            'assignment_type' => 'subject_teacher',
            'status' => 'active',
            'assigned_date' => now(),
            'effective_date' => $request->effective_date,
            'notes' => $request->notes
        ]);

        return redirect()->route('faculty-head.assign-teacher')->with('success', 'Teacher assigned successfully.');
    }

    /**
     * Activate/Deactivate grade submission period
     */
    public function toggleGradeSubmission(Request $request)
    {
        // This would typically be stored in a settings table
        // For now, we'll use a simple response
        $isActive = $request->get('active', false);
        
        // Implementation would depend on how you want to store this setting
        // Could be in a settings table, cache, or configuration
        
        return response()->json([
            'success' => true,
            'message' => $isActive ? 'Grade submission activated' : 'Grade submission deactivated',
            'active' => $isActive
        ]);
    }
}
