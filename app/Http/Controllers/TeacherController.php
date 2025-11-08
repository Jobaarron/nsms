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
        $students = Student::select('id', 'first_name', 'last_name', 'student_id')
            ->orderBy('last_name', 'asc')
            ->get();

        return view('teacher.recommend-counseling', compact('students'));
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

        CounselingSession::create([
            'student_id' => $validatedData['student_id'],
            'recommended_by' => Auth::id(),
            'referral_academic' => isset($validatedData['referral_academic']) ? json_encode($validatedData['referral_academic']) : null,
            'referral_academic_other' => $validatedData['referral_academic_other'] ?? null,
            'referral_social' => isset($validatedData['referral_social']) ? json_encode($validatedData['referral_social']) : null,
            'referral_social_other' => $validatedData['referral_social_other'] ?? null,
            'incident_description' => $validatedData['incident_description'] ?? null,
            'status' => 'recommended',
        ]);

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
            ->where('status', 'scheduled')
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

        $caseMeeting->teacher_statement = $request->teacher_statement;
        $caseMeeting->action_plan = $request->action_plan;
        $caseMeeting->save();

        return redirect()->back()->with('success', 'Your reply has been submitted.');
    }
}
