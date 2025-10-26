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
        $this->middleware('auth')->except(['showLoginForm', 'login']);
        $this->middleware('role:faculty_head')->except(['showLoginForm', 'login']);
    }


    /**
     * Faculty Head Dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $facultyHead = $user->facultyHead; // Get faculty head profile through relationship
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get statistics
        $stats = [
            'total_teachers' => User::role('teacher')->count(),
            'active_subject_assignments' => FacultyAssignment::where('academic_year', $currentAcademicYear)
                                                            ->where('status', 'active')
                                                            ->whereNotNull('subject_id')
                                                            ->count(),
            'active_adviser_assignments' => FacultyAssignment::where('academic_year', $currentAcademicYear)
                                                            ->where('status', 'active')
                                                            ->whereNull('subject_id')
                                                            ->count(),
            'pending_submissions' => GradeSubmission::where('status', 'submitted')->count(),
            'total_subjects' => Subject::where('academic_year', $currentAcademicYear)
                                     ->where('is_active', true)
                                     ->select('subject_name')
                                     ->distinct()
                                     ->get()
                                     ->count()
        ];

        // Get recent grade submissions for review
        $recentSubmissions = GradeSubmission::where('status', 'submitted')
                                          ->with(['teacher', 'subject'])
                                          ->orderBy('submitted_at', 'desc')
                                          ->limit(10)
                                          ->get();

        // Get recent subject teacher assignments (excluding class advisers)
        $recentSubjectAssignments = $facultyHead->assignmentsMade()
                                              ->where('academic_year', $currentAcademicYear)
                                              ->whereNotNull('subject_id') // Only subject assignments
                                              ->with(['teacher.user', 'subject'])
                                              ->orderBy('created_at', 'desc')
                                              ->limit(10)
                                              ->get();

        // Get recent adviser assignments (class advisers only)
        $recentAdviserAssignments = $facultyHead->assignmentsMade()
                                              ->where('academic_year', $currentAcademicYear)
                                              ->where('assignment_type', 'class_adviser') // Filter by assignment type
                                              ->with(['teacher.user'])
                                              ->orderBy('created_at', 'desc')
                                              ->limit(10)
                                              ->get();

        return view('faculty-head.index', compact('facultyHead', 'stats', 'recentSubmissions', 'recentSubjectAssignments', 'recentAdviserAssignments', 'currentAcademicYear'));
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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            
            // Check if user has faculty_head role
            if ($user->hasRole('faculty_head')) {
                $request->session()->regenerate();
                return redirect()->intended(route('faculty-head.dashboard'));
            } else {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'You do not have faculty head privileges.',
                ])->onlyInput('email');
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Logout Faculty Head
     */
    public function logout(Request $request)
    {
        Auth::logout();
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
        
        // Get all sections
        $sections = \App\Models\Section::where('academic_year', $currentAcademicYear)
                                     ->where('is_active', true)
                                     ->orderBy('grade_level')
                                     ->orderBy('section_name')
                                     ->get();
        
        // Get current class advisers
        $advisers = FacultyAssignment::where('academic_year', $currentAcademicYear)
                                   ->where('assignment_type', 'class_adviser')
                                   ->where('status', 'active')
                                   ->with(['teacher.user', 'subject'])
                                   ->get();

        return view('faculty-head.assign-adviser', compact('teachers', 'sections', 'advisers', 'currentAcademicYear'));
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
        
        // Get all sections
        $sections = \App\Models\Section::where('academic_year', $currentAcademicYear)
                                     ->where('is_active', true)
                                     ->orderBy('grade_level')
                                     ->orderBy('section_name')
                                     ->get();
        
        // Get all assignments
        $assignments = FacultyAssignment::where('academic_year', $currentAcademicYear)
                                      ->with(['teacher.user', 'subject', 'assignedBy'])
                                      ->orderBy('grade_level')
                                      ->orderBy('section')
                                      ->get();

        // Group assignments by grade and section
        $assignmentsByClass = $assignments->groupBy(function($assignment) {
            return $assignment->grade_level . ' - ' . $assignment->section;
        });

        return view('faculty-head.assign-teacher', compact('teachers', 'subjects', 'sections', 'assignments', 'assignmentsByClass', 'currentAcademicYear'));
    }



    /**
     * Show grade submission activation page
     */
    public function activateSubmission()
    {
        // Get current grade submission status from settings (default: inactive)
        $isActive = \App\Models\Setting::get('grade_submission_active', false);
        
        // Get quarter-specific settings (default: inactive)
        $quarterSettings = [
            'q1_active' => \App\Models\Setting::get('grade_submission_q1_active', false),
            'q2_active' => \App\Models\Setting::get('grade_submission_q2_active', false),
            'q3_active' => \App\Models\Setting::get('grade_submission_q3_active', false),
            'q4_active' => \App\Models\Setting::get('grade_submission_q4_active', false),
        ];
        
        return view('faculty-head.activate-submission', compact('isActive', 'quarterSettings'));
    }

    /**
     * Toggle grade submission activation
     */
    public function toggleGradeSubmissionStatus(Request $request)
    {
        $isActive = $request->boolean('active');
        
        // Update the setting
        \App\Models\Setting::set(
            'grade_submission_active',
            $isActive,
            'boolean',
            'Controls whether teachers can submit grades system-wide',
            'grade_submission'
        );
        
        // Log the change
        \Log::info('Grade submission status changed', [
            'changed_by' => Auth::user()->name,
            'new_status' => $isActive ? 'active' : 'inactive',
            'timestamp' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => $isActive ? 'Grade submission activated successfully' : 'Grade submission deactivated successfully',
            'active' => $isActive
        ]);
    }

    /**
     * Update quarter-specific grade submission settings
     */
    public function updateQuarterSettings(Request $request)
    {
        $request->validate([
            'quarter' => 'required|in:q1,q2,q3,q4',
            'active' => 'required|boolean'
        ]);
        
        $quarter = $request->quarter;
        $isActive = $request->boolean('active');
        
        $quarterNames = [
            'q1' => '1st Quarter',
            'q2' => '2nd Quarter', 
            'q3' => '3rd Quarter',
            'q4' => '4th Quarter'
        ];
        
        // Update the quarter setting
        \App\Models\Setting::set(
            "grade_submission_{$quarter}_active",
            $isActive,
            'boolean',
            "Controls grade submission for {$quarterNames[$quarter]}",
            'grade_submission'
        );
        
        return response()->json([
            'success' => true,
            'message' => "{$quarterNames[$quarter]} grade submission " . ($isActive ? 'enabled' : 'disabled'),
            'quarter' => $quarter,
            'active' => $isActive
        ]);
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
        
        return view('faculty-head.view-grades', compact('submission', 'students', 'assignment'));
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
        $currentUser = Auth::user();
        
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
            'assigned_by' => $currentUser->id,
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
        $currentUser = Auth::user();
        
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'grade_level' => 'required|string',
            'section' => 'required|string',
            'strand' => 'nullable|string|in:STEM,ABM,HUMSS,GAS,TVL',
            'track' => 'nullable|string|in:ICT,H.E.',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        // Validate strand and track requirements for Senior High School
        if (in_array($request->grade_level, ['Grade 11', 'Grade 12'])) {
            if (empty($request->strand)) {
                return redirect()->back()->with('error', 'Strand is required for Senior High School assignments.')->withInput();
            }
            
            if ($request->strand === 'TVL' && empty($request->track)) {
                return redirect()->back()->with('error', 'Track is required for TVL strand assignments.')->withInput();
            }
        }

        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);

        // Use Eloquent model validation - the model will handle conflict detection
        try {
            // Create subject teacher assignment
            $assignment = FacultyAssignment::create([
                'teacher_id' => $request->teacher_id,
                'subject_id' => $request->subject_id,
                'assigned_by' => $currentUser->id,
                'grade_level' => $request->grade_level,
                'section' => $request->section,
                'strand' => $request->strand,
                'track' => $request->track,
                'academic_year' => $currentAcademicYear,
                'assignment_type' => 'subject_teacher',
                'status' => 'active',
                'assigned_date' => now(),
                'effective_date' => $request->effective_date,
                'notes' => $request->notes
            ]);

            // Load relationships for success message
            $assignment->load(['teacher.user', 'subject']);
            
            $successMessage = "Successfully assigned {$assignment->teacher->user->name} to teach {$assignment->subject->subject_name} for {$assignment->grade_level} - {$assignment->section}.";
            
            return redirect()->route('faculty-head.assign-teacher')->with('success', $successMessage);
            
        } catch (\Exception $e) {
            // Handle model validation errors (including schedule conflicts)
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Show grade submissions for review
     */
    public function viewGrades()
    {
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get all grade submissions pending review
        $pendingSubmissions = GradeSubmission::where('status', 'submitted')
            ->where('academic_year', $currentAcademicYear)
            ->with(['teacher.user', 'subject'])
            ->orderBy('submitted_at', 'desc')
            ->get();

        // Get recently reviewed submissions
        $recentlyReviewed = GradeSubmission::whereIn('status', ['approved', 'rejected'])
            ->where('academic_year', $currentAcademicYear)
            ->with(['teacher.user', 'subject', 'reviewer'])
            ->orderBy('reviewed_at', 'desc')
            ->limit(10)
            ->get();

        // Get submission statistics
        $stats = [
            'pending' => GradeSubmission::where('status', 'submitted')->where('academic_year', $currentAcademicYear)->count(),
            'approved' => GradeSubmission::where('status', 'approved')->where('academic_year', $currentAcademicYear)->count(),
            'rejected' => GradeSubmission::where('status', 'rejected')->where('academic_year', $currentAcademicYear)->count(),
            'draft' => GradeSubmission::where('status', 'draft')->where('academic_year', $currentAcademicYear)->count(),
        ];

        return view('faculty-head.view-grades', compact(
            'pendingSubmissions', 
            'recentlyReviewed', 
            'stats', 
            'currentAcademicYear'
        ));
    }

    /**
     * Show detailed grade submission for review
     */
    public function approveGrades()
    {
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get submission ID from request
        $submissionId = request('submission');
        
        if ($submissionId) {
            $submission = GradeSubmission::with(['teacher.user', 'subject'])
                ->findOrFail($submissionId);
            
            // Get students for this submission
            $students = Student::where('grade_level', $submission->grade_level)
                              ->where('section', $submission->section)
                              ->where('academic_year', $submission->academic_year)
                              ->where('is_active', true);

            // For Senior High School, match strand and track
            if (in_array($submission->grade_level, ['Grade 11', 'Grade 12'])) {
                // Get the faculty assignment to check strand/track
                $assignment = FacultyAssignment::where('teacher_id', $submission->teacher_id)
                    ->where('subject_id', $submission->subject_id)
                    ->where('grade_level', $submission->grade_level)
                    ->where('section', $submission->section)
                    ->where('academic_year', $submission->academic_year)
                    ->first();

                if ($assignment && $assignment->strand) {
                    $students->where('strand', $assignment->strand);
                }
                
                if ($assignment && $assignment->track) {
                    $students->where('track', $assignment->track);
                }
            }

            $students = $students->orderBy('last_name')->orderBy('first_name')->get();
            
            return view('faculty-head.view-grades', compact('submission', 'students'));
        }

        // If no specific submission, show list of pending submissions
        return redirect()->route('faculty-head.view-grades');
    }

    /**
     * Process grade submission approval/rejection
     */
    public function approveSubmission(Request $request, GradeSubmission $submission)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,request_revision',
            'review_notes' => 'nullable|string|max:1000'
        ]);

        $currentUser = Auth::user();

        try {
            switch ($request->action) {
                case 'approve':
                    $submission->approve($currentUser->id, $request->review_notes);
                    $message = "Grades approved successfully! Students can now view their grades.";
                    break;
                    
                case 'reject':
                    $submission->reject($currentUser->id, $request->review_notes);
                    $message = "Grades rejected. Teacher has been notified.";
                    break;
                    
                case 'request_revision':
                    $submission->requestRevision($currentUser->id, $request->review_notes);
                    $message = "Revision requested. Teacher can now edit and resubmit the grades.";
                    break;
            }

            return redirect()->route('faculty-head.view-grades')
                           ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Failed to process grade submission: ' . $e->getMessage())
                           ->withInput();
        }
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

    /**
     * Check if grade submission is currently active (helper method for other controllers)
     */
    public static function isGradeSubmissionActive($quarter = null)
    {
        $generalActive = \App\Models\Setting::get('grade_submission_active', false);
        
        if (!$generalActive) {
            return false;
        }
        
        if ($quarter) {
            $quarterKey = "grade_submission_q{$quarter}_active";
            return \App\Models\Setting::get($quarterKey, false);
        }
        
        return true;
    }

    /**
     * Get grade submission status for API (used by teacher views)
     */
    public function getGradeSubmissionStatus()
    {
        $isActive = \App\Models\Setting::get('grade_submission_active', false);
        
        $quarterSettings = [
            'q1' => \App\Models\Setting::get('grade_submission_q1_active', false),
            'q2' => \App\Models\Setting::get('grade_submission_q2_active', false),
            'q3' => \App\Models\Setting::get('grade_submission_q3_active', false),
            'q4' => \App\Models\Setting::get('grade_submission_q4_active', false),
        ];
        
        return response()->json([
            'success' => true,
            'active' => $isActive,
            'quarters' => $quarterSettings
        ]);
    }

    /**
     * Get subjects by grade level (API endpoint for assign teacher form)
     */
    public function getSubjectsByGrade(Request $request)
    {
        $gradeLevel = $request->get('grade_level');
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        if (!$gradeLevel) {
            return response()->json([
                'success' => false,
                'message' => 'Grade level is required'
            ], 400);
        }
        
        $subjects = \App\Models\Subject::where('grade_level', $gradeLevel)
                                     ->where('academic_year', $currentAcademicYear)
                                     ->where('is_active', true)
                                     ->orderBy('subject_name')
                                     ->get(['id', 'subject_name', 'subject_code']);
        
        return response()->json([
            'success' => true,
            'subjects' => $subjects->map(function($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->subject_name,
                    'code' => $subject->subject_code ?? ''
                ];
            })
        ]);
    }

    /**
     * Remove teacher assignment
     */
    public function removeAssignment(FacultyAssignment $assignment)
    {
        try {
            // Check if the current user has permission to remove this assignment
            $facultyHead = Auth::user()->facultyHead;
            
            if (!$facultyHead) {
                // Return JSON for AJAX requests
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized action.'
                    ], 403);
                }
                return redirect()->back()->with('error', 'Unauthorized action.');
            }

            // Store assignment details for success message
            $teacherName = $assignment->teacher->user->name;
            $assignmentType = $assignment->isClassAdviser() ? 'class adviser' : 'subject teacher';
            $classInfo = $assignment->grade_level . ' - ' . $assignment->section;
            $subjectInfo = $assignment->subject ? ' for ' . $assignment->subject->subject_name : '';

            // Delete the assignment
            $assignment->delete();

            $message = "Successfully removed {$teacherName} as {$assignmentType} for {$classInfo}{$subjectInfo}.";
            
            // Return JSON for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
            
            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            // Return JSON for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove assignment. Please try again.'
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to remove assignment. Please try again.');
        }
    }

}
