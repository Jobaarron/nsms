<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\CounselingSession;
use App\Models\FacultyAssignment;
use App\Models\GradeSubmission;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TeacherController extends Controller
{
    /**
     * Show the teacher dashboard.
     */
    public function index()
    {
        $teacher = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        try {
            // Get teacher record first
            $teacherRecord = Teacher::where('user_id', $teacher->id)->first();
            
            // Get teacher's assignments
            $assignments = collect();
            if ($teacherRecord) {
                $assignments = FacultyAssignment::where('teacher_id', $teacherRecord->id)
                    ->where('academic_year', $currentAcademicYear)
                    ->where('status', 'active')
                    ->with(['subject', 'teacher.user'])
                    ->get();
            }
            
            // Get recent grade submissions
            $recentSubmissions = collect();
            if ($teacherRecord) {
                $recentSubmissions = GradeSubmission::where('teacher_id', $teacherRecord->id)
                    ->where('academic_year', $currentAcademicYear)
                    ->with(['subject'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            }
                
            // Calculate statistics
            $stats = [
                'total_classes' => $assignments->count(),
                'total_students' => $assignments->sum('student_count') ?: 0,
                'grade_submissions' => $recentSubmissions->count(),
                'weekly_hours' => $assignments->sum('weekly_hours') ?: 0,
            ];
        } catch (\Exception $e) {
            // Handle case where tables don't exist yet
            $assignments = collect();
            $recentSubmissions = collect();
            $stats = [
                'total_classes' => 0,
                'total_students' => 0,
                'grade_submissions' => 0,
                'weekly_hours' => 0,
            ];
        }
        
        // Get grade submission status
        $gradeSubmissionActive = \App\Models\Setting::get('grade_submission_active', false);
        $quarterSettings = [
            'q1_active' => \App\Models\Setting::get('grade_submission_q1_active', false),
            'q2_active' => \App\Models\Setting::get('grade_submission_q2_active', false),
            'q3_active' => \App\Models\Setting::get('grade_submission_q3_active', false),
            'q4_active' => \App\Models\Setting::get('grade_submission_q4_active', false),
        ];
        
        return view('teacher.index', compact(
            'assignments',
            'stats',
            'recentSubmissions',
            'currentAcademicYear',
            'gradeSubmissionActive',
            'quarterSettings'
        ));
    }

    /**
     * Check grade submission status for AJAX requests
     */
    public function checkSubmissionStatus()
    {
        $isActive = \App\Models\Setting::get('grade_submission_active', false);
        $quarterSettings = [
            'q1_active' => \App\Models\Setting::get('grade_submission_q1_active', false),
            'q2_active' => \App\Models\Setting::get('grade_submission_q2_active', false),
            'q3_active' => \App\Models\Setting::get('grade_submission_q3_active', false),
            'q4_active' => \App\Models\Setting::get('grade_submission_q4_active', false),
        ];

        // Get list of active quarters for JavaScript
        $activeQuarters = [];
        if ($quarterSettings['q1_active']) $activeQuarters[] = '1st';
        if ($quarterSettings['q2_active']) $activeQuarters[] = '2nd';
        if ($quarterSettings['q3_active']) $activeQuarters[] = '3rd';
        if ($quarterSettings['q4_active']) $activeQuarters[] = '4th';

        return response()->json([
            'active' => $isActive,
            'quarters' => $quarterSettings,
            'active_quarters' => $activeQuarters
        ]);
    }

    /**
     * Get dashboard statistics for AJAX requests
     */
    public function getDashboardStats()
    {
        $user = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Check if user has teacher profile
        if (!$user->teacher) {
            return response()->json([
                'total_classes' => 0,
                'total_students' => 0,
                'grade_submissions' => 0,
                'weekly_hours' => 0,
            ]);
        }
        
        $teacherId = $user->teacher->id;
        
        // Get teacher's assignments
        $assignments = FacultyAssignment::where('teacher_id', $teacherId)
            ->where('academic_year', $currentAcademicYear)
            ->where('status', 'active')
            ->get();
        
        // Calculate real-time statistics
        $stats = [
            'total_classes' => $assignments->count(),
            'total_students' => $assignments->sum('student_count') ?: 0,
            'grade_submissions' => GradeSubmission::where('teacher_id', $teacherId)
                ->where('academic_year', $currentAcademicYear)
                ->count(),
            'weekly_hours' => $assignments->sum('weekly_hours') ?: 0,
        ];
        
        return response()->json($stats);
    }

    // REMOVED: generateTeacher() method
    // This functionality has been moved to UserManagementController->storeTeacher()
    // The teacher-generator.blade.php view is no longer needed as teacher creation
    // is now handled through the centralized user management system

    // REMOVED: setupTeacherRoleAndPermissions() method
    // Role and permission management is now handled by RolePermissionSeeder
    // All teacher roles and permissions are centrally managed

    /**
     * Show the teacher login form.
     */
    public function showLoginForm()
    {
        // If already logged in and is teacher, redirect to dashboard
        if (Auth::check() && Auth::user()->hasRole('teacher')) {
            return redirect()->route('teacher.dashboard');
        }
        
        return view('teacher.login');
    }

    /**
     * Handle teacher login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Check if user has teacher role
            if (Auth::user()->hasRole('teacher')) {
                return redirect()->intended(route('teacher.dashboard'));
            }
            
            // If not a teacher, log them out
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return back()->withErrors([
                'email' => 'You do not have permission to access the teacher dashboard.',
            ]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Log the teacher out.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('teacher.login');
    }

    /**
     * Show the form to recommend a student to counselling.
     */
    public function showRecommendForm()
    {
        $teacher = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get teacher record
        $teacherRecord = Teacher::where('user_id', $teacher->id)->first();
        
        if (!$teacherRecord) {
            return view('teacher.recommend-counseling', [
                'students' => collect(),
                'message' => 'Teacher record not found.'
            ]);
        }
        
        // Get teacher's advisory assignment (class adviser)
        $advisoryAssignment = FacultyAssignment::where('teacher_id', $teacherRecord->id)
            ->where('assignment_type', 'class_adviser')
            ->where('academic_year', $currentAcademicYear)
            ->where('status', 'active')
            ->first();
        
        $students = collect();
        $message = '';
        
        if ($advisoryAssignment) {
            // Get students in advisory class only
            $studentsQuery = Student::select('id', 'first_name', 'last_name', 'student_id')
                ->where('grade_level', $advisoryAssignment->grade_level)
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
            
            $students = $studentsQuery->orderBy('last_name', 'asc')
                ->orderBy('first_name', 'asc')
                ->get();
                
            // Build class name for display
            $className = $advisoryAssignment->grade_level . ' - ' . $advisoryAssignment->section;
            if ($advisoryAssignment->strand) {
                $className .= ' - ' . $advisoryAssignment->strand;
                if ($advisoryAssignment->track) {
                    $className .= ' - ' . $advisoryAssignment->track;
                }
            }
            
            $message = "Showing students from your advisory class: {$className}";
        } else {
            $message = 'You are not assigned as a class adviser for the current academic year.';
        }

        // Get scheduled counseling sessions recommended by this teacher
        $scheduledSessions = CounselingSession::with(['student', 'counselor'])
            ->where('recommended_by', $teacher->id)
            ->where('status', 'scheduled')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('teacher.recommend-counseling', compact('students', 'message', 'scheduledSessions'));
    }

    /**
     * Recommend a student to counselling.
     */
    public function recommendToCounseling(Request $request)
    {
        $validatedData = $request->validate([
            'student_id' => 'required|exists:students,id',
            'referral_academic' => 'nullable|array',
            'referral_academic_other' => 'nullable|string',
            'referral_social' => 'nullable|array',
            'referral_social_other' => 'nullable|string',
            'incident_description' => 'nullable|string',
        ]);

        $counselingSession = CounselingSession::create([
            'student_id' => $validatedData['student_id'],
            'recommended_by' => Auth::id(),
            'referral_academic' => isset($validatedData['referral_academic']) ? json_encode($validatedData['referral_academic']) : null,
            'referral_academic_other' => $validatedData['referral_academic_other'] ?? null,
            'referral_social' => isset($validatedData['referral_social']) ? json_encode($validatedData['referral_social']) : null,
            'referral_social_other' => $validatedData['referral_social_other'] ?? null,
            'incident_description' => $validatedData['incident_description'] ?? null,
            'status' => 'recommended',
        ]);

        // Create notification for guidance about teacher recommendation
        try {
            $teacherName = Auth::user()->name ?? 'Teacher';
            $student = \App\Models\Student::find($validatedData['student_id']);
            $studentName = $student ? $student->full_name : 'Student';
            
            // Get referral reasons
            $academicReasons = $validatedData['referral_academic'] ?? [];
            $socialReasons = $validatedData['referral_social'] ?? [];
            $allReasons = array_merge($academicReasons, $socialReasons);
            $reasonsText = !empty($allReasons) ? implode(', ', $allReasons) : 'General counseling need';
            
            \App\Models\Notice::createGlobal(
                "Student Recommended for Counseling",
                "Teacher {$teacherName} has recommended {$studentName} for counseling. Concerns: {$reasonsText}. Please review and schedule the counseling session.",
                null, // created_by will be null for system-generated notices
                null, // target_status
                null  // target_grade_level
            );
            
            \Log::info('Notification created for teacher counseling recommendation', [
                'counseling_session_id' => $counselingSession->id,
                'teacher_name' => $teacherName,
                'student_name' => $studentName,
                'reasons' => $reasonsText
            ]);
        } catch (\Exception $notificationError) {
            // Log notification error but don't fail the main operation
            \Log::error('Failed to create notification for teacher counseling recommendation', [
                'counseling_session_id' => $counselingSession->id,
                'error' => $notificationError->getMessage()
            ]);
        }

        return redirect()->route('teacher.dashboard')
            ->with('success', 'Student has been recommended for counseling. Guidance will review the recommendation.');
    }

    /**
     * Show the Observation Report page.
     * Route: teacher.observationreport
     */
    public function showObservationReport()
    {
        $currentUser = Auth::user();
        $teacherRecord = Teacher::where('user_id', $currentUser->id)->first();
        
        if (!$teacherRecord) {
            return view('teacher.observationreport', ['reports' => collect()]);
        }
        
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get only case meetings for students that this teacher is the class adviser of
        $reports = \App\Models\CaseMeeting::with(['student', 'violation', 'counselor'])
            ->whereIn('status', ['scheduled', 'pre_completed'])
            ->where(function($query) use ($currentUser, $teacherRecord, $currentAcademicYear) {
                // Case 1: Direct adviser_id match
                $query->where('adviser_id', $currentUser->id)
                      // Case 2: OR check if teacher is class adviser for the student
                      ->orWhereHas('student', function($studentQuery) use ($teacherRecord, $currentAcademicYear) {
                          $studentQuery->whereExists(function($advisoryQuery) use ($teacherRecord, $currentAcademicYear) {
                              $advisoryQuery->select(DB::raw(1))
                                          ->from('faculty_assignments')
                                          ->whereColumn('faculty_assignments.grade_level', 'students.grade_level')
                                          ->whereColumn('faculty_assignments.section', 'students.section')
                                          ->where('faculty_assignments.teacher_id', $teacherRecord->id)
                                          ->where('faculty_assignments.academic_year', $currentAcademicYear)
                                          ->where('faculty_assignments.assignment_type', 'class_adviser')
                                          ->where('faculty_assignments.status', 'active');
                          });
                      });
            })
            ->orderByDesc('scheduled_date')
            ->get();

        return view('teacher.observationreport', compact('reports'));
    }

        /**
     * Serve the Teacher Observation Report PDF
     */
    public function serveObservationReportPdf()
    {
        $path = storage_path('app/public/Teacher-Report/Teacher-Observation-Report.pdf');
        if (!file_exists($path)) {
            abort(404, 'PDF not found');
        }
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Teacher-Observation-Report.pdf"',
        ]);
    }
        /**
     * Handle teacher reply for observation report (update case meeting)
     * Only allows the assigned adviser to reply
     */
    public function submitObservationReply(Request $request, $caseMeetingId)
    {
        $request->validate([
            'teacher_statement' => 'required|string',
            'action_plan' => 'required|string',
        ]);

        $caseMeeting = \App\Models\CaseMeeting::with(['student', 'adviser'])->findOrFail($caseMeetingId);
        $currentUser = Auth::user();
        
        // Check if current user is the assigned adviser for this case meeting
        if (!$caseMeeting->adviser_id || $caseMeeting->adviser_id !== $currentUser->id) {
            // If no adviser_id is set, check if user is the class adviser for this student
            if ($caseMeeting->student) {
                $student = $caseMeeting->student;
                $teacherRecord = Teacher::where('user_id', $currentUser->id)->first();
                
                if (!$teacherRecord) {
                    abort(403, 'You are not authorized to reply to this case meeting.');
                }
                
                $advisoryAssignment = \App\Models\FacultyAssignment::where('teacher_id', $teacherRecord->id)
                    ->where('grade_level', $student->grade_level)
                    ->where('section', $student->section)
                    ->where('academic_year', $student->academic_year)
                    ->where('assignment_type', 'class_adviser')
                    ->where('status', 'active')
                    ->first();
                    
                if (!$advisoryAssignment) {
                    abort(403, 'You are not the assigned adviser for this student.');
                }
            } else {
                abort(403, 'You are not authorized to reply to this case meeting.');
            }
        }

        try {
            // Log the attempt for debugging
            \Log::info('Attempting to submit teacher observation reply', [
                'case_meeting_id' => $caseMeetingId,
                'teacher_id' => $currentUser->id,
                'teacher_statement_length' => strlen($request->teacher_statement),
                'action_plan_length' => strlen($request->action_plan)
            ]);

            $caseMeeting->teacher_statement = $request->teacher_statement;
            $caseMeeting->action_plan = $request->action_plan;
            
            // Only set timestamp if the column exists in the table
            if (\Schema::hasColumn('case_meetings', 'teacher_reply_submitted_at')) {
                $caseMeeting->teacher_reply_submitted_at = now();
            }
            
            // Check if the case meeting can be saved
            if (!$caseMeeting->save()) {
                throw new \Exception('Failed to save case meeting - save() returned false');
            }

            // Create notification for guidance counselor about teacher reply
            try {
                $teacherName = $currentUser->name ?? 'Teacher';
                $studentName = $caseMeeting->student ? $caseMeeting->student->full_name : 'Student';
                
                \App\Models\Notice::createGlobal(
                    "Teacher Reply Received - Case Meeting",
                    "Teacher {$teacherName} has submitted their reply for the case meeting regarding {$studentName}. Please review the teacher's statement and action plan in the case meeting details.",
                    null, // created_by will be null for system-generated notices
                    null, // target_status
                    null  // target_grade_level
                );
                
                \Log::info('Notification created for teacher reply', [
                    'case_meeting_id' => $caseMeetingId,
                    'teacher_name' => $teacherName,
                    'student_name' => $studentName
                ]);
            } catch (\Exception $notificationError) {
                // Log notification error but don't fail the main operation
                \Log::error('Failed to create notification for teacher reply', [
                    'case_meeting_id' => $caseMeetingId,
                    'teacher_id' => $currentUser->id,
                    'error' => $notificationError->getMessage()
                ]);
            }

            \Log::info('Teacher observation reply submitted successfully', [
                'case_meeting_id' => $caseMeetingId,
                'teacher_id' => $currentUser->id
            ]);

            return redirect()->back()->with('success', 'Your observation report reply has been successfully submitted. The guidance office will review your response .');
            
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error while submitting teacher observation reply', [
                'case_meeting_id' => $caseMeetingId,
                'teacher_id' => $currentUser->id,
                'error' => $e->getMessage(),
                'sql_code' => $e->getCode()
            ]);
            
            return redirect()->back()->with('error', 'Database error occurred. Please check if all required fields are properly filled and try again.');
            
        } catch (\Exception $e) {
            \Log::error('Failed to submit teacher observation reply', [
                'case_meeting_id' => $caseMeetingId,
                'teacher_id' => $currentUser->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // In development, show detailed error; in production, show generic message
            if (config('app.debug')) {
                return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
            } else {
                return redirect()->back()->with('error', 'Failed to submit your reply. Please try again or contact the administrator if the problem persists.');
            }
        }
    }
}
