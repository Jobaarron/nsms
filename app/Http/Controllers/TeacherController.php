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



    public function markAlertViewed(Request $request)
    {
        $alertType = $request->input('alert_type');
        
        if ($alertType === 'grades') {
            session(['grades_alert_viewed' => true]);
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * Get real-time alert counts for teacher (grades only)
     */
    public function getAlertCounts()
    {
        try {
            $teacher = Auth::user();
            $teacherRecord = Teacher::where('user_id', $teacher->id)->first();
            
            $counts = [
                'draft_grades' => 0,
            ];
            
            if ($teacherRecord) {
                // Count draft grade submissions
                $counts['draft_grades'] = GradeSubmission::where('teacher_id', $teacherRecord->id)
                    ->where('status', 'draft')
                    ->count();
            }
            
            return response()->json([
                'success' => true,
                'counts' => $counts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching alert counts',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
