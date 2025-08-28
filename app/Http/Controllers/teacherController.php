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

    /**
     * Show the teacher account generator form.
     */
    public function showGeneratorForm()
    {
        // Check if teacher role exists
        $teacherRoleExists = Role::where('name', 'teacher')->where('guard_name', 'web')->exists();
        
        // Check if any teacher exists
        $teacherExists = User::whereHas('roles', function($query) {
            $query->where('name', 'teacher');
        })->exists();

        return view('teacher.teacher-generator', [
            'teacherRoleExists' => $teacherRoleExists,
            'teacherExists' => $teacherExists,
        ]);
    }

    /**
     * Generate a new teacher account.
     */
    public function generateTeacher(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'employee_id' => 'required|string|max:50|unique:teachers,employee_id',
            'department' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'hire_date' => 'required|date',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        
        try {
            // Create user first
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'active',
            ]);

            // Create teacher record
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'employee_id' => $request->employee_id,
                'department' => $request->department,
                'position' => $request->position,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'qualifications' => $request->qualifications,
                'hire_date' => $request->hire_date,
                'is_active' => true,
            ]);

            // Setup roles and permissions
            $this->setupTeacherRoleAndPermissions($user);

            DB::commit();

            return redirect()->route('teacher.generator')
                ->with('success', 'Teacher account created successfully for ' . $teacher->user->name . '. You can now login to the system.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error creating teacher: ' . $e->getMessage());
        }
    }

    private function setupTeacherRoleAndPermissions(User $user)
    {
        // Create or get the teacher role
        $teacherRole = Role::firstOrCreate([
            'name' => 'teacher',
            'guard_name' => 'web'
        ]);

        // Define teacher permissions
        $permissions = [
            'View Students',
            'Manage Grades',
            'View Classes',
            'Manage Attendance',
            'View Reports',
        ];

        // Create permissions if they don't exist and assign to teacher role
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);
            
            // Assign permission to teacher role if not already assigned
            if (!$teacherRole->hasPermissionTo($permission)) {
                $teacherRole->givePermissionTo($permission);
            }
        }

        // Assign role to user
        $user->assignRole($teacherRole);
    }

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