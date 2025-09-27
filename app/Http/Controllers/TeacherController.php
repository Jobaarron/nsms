<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Teacher;
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
}