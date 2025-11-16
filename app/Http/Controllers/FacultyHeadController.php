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
use App\Models\Section;
use App\Models\Payment;
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
                                          ->with(['teacher.user', 'subject'])
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
     * Unified Faculty Assignments (Teachers & Advisers)
     */
    public function assignFaculty()
    {
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get all teachers
        $teachers = User::role('teacher')->with('teacher')->get();
        
        // Get all subjects
        $subjects = Subject::where('academic_year', $currentAcademicYear)
                          ->where('is_active', true)
                          ->orderBy('grade_level')
                          ->orderBy('subject_name')
                          ->get();
        
        // Get all sections
        $sections = Section::where('academic_year', $currentAcademicYear)
                          ->where('is_active', true)
                          ->orderBy('grade_level')
                          ->orderBy('section_name')
                          ->get();
        
        // Get current subject teacher assignments
        $assignments = FacultyAssignment::where('academic_year', $currentAcademicYear)
                                       ->where('assignment_type', 'subject_teacher')
                                       ->where('status', 'active')
                                       ->with(['teacher.user', 'subject'])
                                       ->get();
        
        // Get current class advisers
        $advisers = FacultyAssignment::where('academic_year', $currentAcademicYear)
                                   ->where('assignment_type', 'class_adviser')
                                   ->where('status', 'active')
                                   ->with(['teacher.user'])
                                   ->get();

        return view('faculty-head.assign-faculty', compact('teachers', 'subjects', 'sections', 'assignments', 'advisers', 'currentAcademicYear'));
    }

    /**
     * Get fresh faculty data for AJAX updates
     */
    public function getFacultyData()
    {
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get all teachers
        $teachers = User::role('teacher')->with('teacher')->get();
        
        // Get all subjects
        $subjects = Subject::where('academic_year', $currentAcademicYear)
                          ->where('is_active', true)
                          ->orderBy('grade_level')
                          ->orderBy('subject_name')
                          ->get();
        
        // Get all sections
        $sections = Section::where('academic_year', $currentAcademicYear)
                          ->where('is_active', true)
                          ->orderBy('grade_level')
                          ->orderBy('section_name')
                          ->get();
        
        // Get current subject teacher assignments
        $assignments = FacultyAssignment::where('academic_year', $currentAcademicYear)
                                       ->where('assignment_type', 'subject_teacher')
                                       ->where('status', 'active')
                                       ->with(['teacher.user', 'subject'])
                                       ->get();
        
        // Get current class advisers
        $advisers = FacultyAssignment::where('academic_year', $currentAcademicYear)
                                   ->where('assignment_type', 'class_adviser')
                                   ->where('status', 'active')
                                   ->with(['teacher.user'])
                                   ->get();

        return response()->json([
            'success' => true,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'sections' => $sections,
            'assignments' => $assignments,
            'advisers' => $advisers
        ]);
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
        $sections = Section::where('academic_year', $currentAcademicYear)
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

        return redirect()->route('faculty-head.assign-faculty');
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
        $sections = Section::where('academic_year', $currentAcademicYear)
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

        return redirect()->route('faculty-head.assign-faculty');
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
        
        // If activating, automatically determine and activate the current quarter
        $activatedQuarter = null;
        if ($isActive) {
            $currentQuarter = $this->getCurrentQuarter();
            if ($currentQuarter) {
                \App\Models\Setting::set(
                    "grade_submission_{$currentQuarter}_active",
                    true,
                    'boolean',
                    "Auto-activated for current quarter",
                    'grade_submission'
                );
                $activatedQuarter = $currentQuarter;
            }
        } else {
            // If deactivating, deactivate all quarters
            foreach (['q1', 'q2', 'q3', 'q4'] as $quarter) {
                \App\Models\Setting::set(
                    "grade_submission_{$quarter}_active",
                    false,
                    'boolean',
                    "Auto-deactivated with main system",
                    'grade_submission'
                );
            }
        }
        
        // Log the change
        \Log::info('Grade submission status changed', [
            'changed_by' => Auth::user()->name,
            'new_status' => $isActive ? 'active' : 'inactive',
            'activated_quarter' => $activatedQuarter,
            'timestamp' => now()
        ]);
        
        // Get updated quarter settings
        $quarterSettings = [
            'q1' => \App\Models\Setting::get('grade_submission_q1_active', false),
            'q2' => \App\Models\Setting::get('grade_submission_q2_active', false),
            'q3' => \App\Models\Setting::get('grade_submission_q3_active', false),
            'q4' => \App\Models\Setting::get('grade_submission_q4_active', false),
        ];
        
        $message = $isActive ? 'Grade submission activated successfully' : 'Grade submission deactivated successfully';
        if ($isActive && $activatedQuarter) {
            $quarterNames = [
                'q1' => '1st Quarter',
                'q2' => '2nd Quarter', 
                'q3' => '3rd Quarter',
                'q4' => '4th Quarter'
            ];
            $message .= " ({$quarterNames[$activatedQuarter]} auto-activated)";
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'active' => $isActive,
            'quarters' => $quarterSettings,
            'activated_quarter' => $activatedQuarter
        ]);
    }

    /**
     * Determine current quarter based on date
     */
    private function getCurrentQuarter()
    {
        $currentMonth = (int) date('n'); // 1-12
        
        // School year quarters (Philippines academic calendar)
        if ($currentMonth >= 8 && $currentMonth <= 10) {
            return 'q1'; // August - October
        } elseif ($currentMonth >= 11 || $currentMonth <= 1) {
            return 'q2'; // November - January
        } elseif ($currentMonth >= 2 && $currentMonth <= 4) {
            return 'q3'; // February - April
        } elseif ($currentMonth >= 5 && $currentMonth <= 7) {
            return 'q4'; // May - July
        }
        
        return 'q1'; // Default to Q1 if uncertain
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
            'rejected' => GradeSubmission::whereIn('status', ['rejected', 'revision_requested'])->count()
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
            'strand' => 'nullable|string|in:STEM,ABM,HUMSS,TVL',
            'track' => 'nullable|string|in:ICT,H.E.',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'replace_assignment_id' => 'nullable|exists:faculty_assignments,id'
        ]);

        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);

        try {
            // If this is a replacement, deactivate the old assignment
            if ($request->replace_assignment_id) {
                $oldAssignment = FacultyAssignment::find($request->replace_assignment_id);
                if ($oldAssignment) {
                    $oldAssignment->update([
                        'status' => 'inactive',
                        'end_date' => now()
                    ]);
                }
            }

            // Create class adviser assignment
            $assignment = FacultyAssignment::create([
                'teacher_id' => $request->teacher_id,
                'subject_id' => null, // Class adviser doesn't need specific subject
                'assigned_by' => $currentUser->id,
                'grade_level' => $request->grade_level,
                'section' => $request->section,
                'strand' => $request->strand,
                'track' => $request->track,
                'academic_year' => $currentAcademicYear,
                'assignment_type' => 'class_adviser',
                'status' => 'active',
                'assigned_date' => now(),
                'effective_date' => $request->effective_date,
                'notes' => $request->notes
            ]);

            // Load relationships for response
            $assignment->load(['teacher.user']);

            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Class adviser assigned successfully.',
                    'assignment' => $assignment
                ]);
            }

            return redirect()->route('faculty-head.assign-faculty')->with('success', 'Class adviser assigned successfully.');
        } catch (\Exception $e) {
            // Return error response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
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
            'strand' => 'nullable|string|in:STEM,ABM,HUMSS,TVL',
            'track' => 'nullable|string|in:ICT,H.E.',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'replace_assignment_id' => 'nullable|exists:faculty_assignments,id'
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

        // If this is a replacement, deactivate the old assignment
        if ($request->replace_assignment_id) {
            $oldAssignment = FacultyAssignment::find($request->replace_assignment_id);
            if ($oldAssignment) {
                $oldAssignment->update([
                    'status' => 'inactive',
                    'end_date' => now()
                ]);
            }
        }

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
            
            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'assignment' => $assignment
                ]);
            }
            
            return redirect()->route('faculty-head.assign-faculty')->with('success', $successMessage);
            
        } catch (\Exception $e) {
            // Handle model validation errors (including schedule conflicts)
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Show grade submissions for review
     */
    public function viewGrades()
    {
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get all grade submissions for filtering
        $allSubmissions = GradeSubmission::whereIn('status', ['submitted', 'approved', 'rejected', 'revision_requested'])
            ->where('academic_year', $currentAcademicYear)
            ->with(['teacher.user', 'subject', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all grade submissions pending review (for backward compatibility)
        $pendingSubmissions = $allSubmissions->where('status', 'submitted');

        // Get recently reviewed submissions (for backward compatibility)
        $recentlyReviewed = $allSubmissions->whereIn('status', ['approved', 'rejected', 'revision_requested'])->take(10);

        // Get submission statistics
        $stats = [
            'pending' => GradeSubmission::where('status', 'submitted')->where('academic_year', $currentAcademicYear)->count(),
            'approved' => GradeSubmission::where('status', 'approved')->where('academic_year', $currentAcademicYear)->count(),
            'rejected' => GradeSubmission::whereIn('status', ['rejected', 'revision_requested'])->where('academic_year', $currentAcademicYear)->count(),
            'draft' => GradeSubmission::where('status', 'draft')->where('academic_year', $currentAcademicYear)->count(),
        ];

        return view('faculty-head.view-grades', compact(
            'allSubmissions',
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
            'action' => 'required|in:approve,request_revision',
            'review_notes' => 'nullable|string|max:1000'
        ]);

        $currentUser = Auth::user();

        try {
            switch ($request->action) {
                case 'approve':
                    $submission->approve($currentUser->id, $request->review_notes);
                    $message = "Grades approved successfully! Students can now view their grades.";
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

    /**
     * Get subjects for AJAX filtering
     */
    public function getSubjects()
    {
        try {
            $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
            
            $subjects = Subject::where('academic_year', $currentAcademicYear)
                              ->where('is_active', true)
                              ->select('id', 'subject_name', 'grade_level', 'strand', 'track')
                              ->orderBy('grade_level')
                              ->orderBy('subject_name')
                              ->get();

            return response()->json([
                'success' => true,
                'subjects' => $subjects
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading subjects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sections for AJAX filtering
     */
    public function getSections()
    {
        try {
            $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
            
            $sections = Section::where('academic_year', $currentAcademicYear)
                              ->where('is_active', true)
                              ->select('section_name', 'grade_level')
                              ->orderBy('grade_level')
                              ->orderBy('section_name')
                              ->get();

            return response()->json([
                'success' => true,
                'sections' => $sections
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading sections: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get section details including students, adviser, and subject teachers
     */
    public function getSectionDetails(Request $request)
    {
        try {
            $gradeLevel = $request->get('grade_level');
            $sectionName = $request->get('section');
            $strand = $request->get('strand');
            $track = $request->get('track');
            $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
            
            
            
            if (!$gradeLevel || !$sectionName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grade level and section are required'
                ], 400);
            }
            
            // Build student query - include all enrolled students (full, quarterly, monthly payers)
            $studentQuery = Student::where('grade_level', $gradeLevel)
                                  ->where('section', $sectionName)
                                  ->where('academic_year', $currentAcademicYear)
                                  ->where('is_active', true)
                                  ->where('enrollment_status', 'enrolled');
            
            // Add strand filter if provided
            if ($strand) {
                $studentQuery->where('strand', $strand);
            }
            
            // Add track filter if provided
            if ($track) {
                $studentQuery->where('track', $track);
            }
            
            // Get students with full details including payment status
            $students = $studentQuery->select(
                'id', 'student_id', 'first_name', 'middle_name', 'last_name', 'suffix',
                'grade_level', 'section', 'strand', 'track', 'contact_number', 'is_active', 'is_paid', 'academic_year'
            )->get();
            
            
            
            $students = $students->map(function($student) use ($currentAcademicYear) {
                // Determine payment status display
                $paymentStatus = $student->is_paid ? 'Paid' : 'Pending';
                $paymentType = 'Full'; // Default assumption for paid students
                
                // Check for quarterly/monthly payments if not fully paid
                if (!$student->is_paid) {
                    $hasPayments = Payment::where('payable_type', 'App\\Models\\Student')
                        ->where('payable_id', $student->id)
                        ->where('status', 'paid')
                        ->exists();
                    
                    if ($hasPayments) {
                        $paymentType = 'Partial (Q/M)'; // Quarterly/Monthly
                        $paymentStatus = 'Partial';
                    } else {
                        $paymentType = 'None';
                    }
                }
                
                return [
                    'student_id' => $student->student_id,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'suffix' => $student->suffix,
                    'grade_level' => $student->grade_level,
                    'section' => $student->section,
                    'strand' => $student->strand,
                    'track' => $student->track,
                    'contact_number' => $student->contact_number,
                    'is_active' => $student->is_active,
                    'payment_status' => $paymentStatus,
                    'payment_type' => $paymentType
                ];
            });
            
            // Build adviser query
            $adviserQuery = FacultyAssignment::where('academic_year', $currentAcademicYear)
                                           ->where('assignment_type', 'class_adviser')
                                           ->where('grade_level', $gradeLevel)
                                           ->where('section', $sectionName)
                                           ->where('status', 'active');
            
            // Add strand and track filters for adviser
            if ($strand) {
                $adviserQuery->where('strand', $strand);
            }
            if ($track) {
                $adviserQuery->where('track', $track);
            }
            
            $adviser = $adviserQuery->with(['teacher.user'])->first();
            
            $adviserData = null;
            if ($adviser) {
                $adviserData = [
                    'name' => $adviser->teacher->user->name ?? 'Unknown',
                    'assigned_date' => $adviser->assigned_date ? $adviser->assigned_date->format('M d, Y') : 'N/A'
                ];
            }
            
            // Get subject teachers for this class
            $subjectTeachersQuery = FacultyAssignment::where('academic_year', $currentAcademicYear)
                                                   ->where('assignment_type', 'subject_teacher')
                                                   ->where('grade_level', $gradeLevel)
                                                   ->where('section', $sectionName)
                                                   ->where('status', 'active');
            
            // Add strand and track filters for subject teachers
            if ($strand) {
                $subjectTeachersQuery->where('strand', $strand);
            }
            if ($track) {
                $subjectTeachersQuery->where('track', $track);
            }
            
            $subjectTeachers = $subjectTeachersQuery->with(['teacher.user', 'subject'])
                                                  ->get()
                                                  ->map(function($assignment) {
                                                      return [
                                                          'teacher_name' => $assignment->teacher->user->name ?? 'Unknown',
                                                          'subject_name' => $assignment->subject->subject_name ?? 'Unknown Subject',
                                                          'subject_code' => $assignment->subject->subject_code ?? null
                                                      ];
                                                  });
            
            return response()->json([
                'success' => true,
                'details' => [
                    'students' => $students,
                    'adviser' => $adviserData,
                    'subject_teachers' => $subjectTeachers,
                    'grade_level' => $gradeLevel,
                    'section' => $sectionName,
                    'strand' => $strand,
                    'track' => $track
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getSectionDetails', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_params' => $request->all(),
                'grade_level' => $request->get('grade_level'),
                'section' => $request->get('section'),
                'strand' => $request->get('strand'),
                'track' => $request->get('track')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading section details: ' . $e->getMessage()
            ], 500);
        }
    }

}
