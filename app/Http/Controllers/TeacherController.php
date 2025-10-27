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
            $recentSubmissions = GradeSubmission::where('teacher_id', $teacher->id)
                ->where('academic_year', $currentAcademicYear)
                ->with(['subject'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
                
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

        return response()->json([
            'active' => $isActive,
            'quarters' => $quarterSettings
        ]);
    }

    /**
     * Get dashboard statistics for AJAX requests
     */
    public function getDashboardStats()
    {
        $teacher = Auth::user();
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get teacher's assignments
        $assignments = FacultyAssignment::where('teacher_id', $teacher->id)
            ->where('academic_year', $currentAcademicYear)
            ->where('status', 'active')
            ->get();
        
        // Calculate real-time statistics
        $stats = [
            'total_classes' => $assignments->count(),
            'total_students' => $assignments->sum('student_count') ?: 0,
            'grade_submissions' => GradeSubmission::where('teacher_id', $teacher->id)
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
}
