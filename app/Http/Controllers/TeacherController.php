<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\CounselingSession;
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
        return view('teacher.index');
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
