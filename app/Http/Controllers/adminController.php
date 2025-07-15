<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use App\Models\Student;
use App\Traits\AdminAuthentication;

class AdminController extends Controller
{
    use AdminAuthentication;
    
    public function index()
    {
        if ($response = $this->checkAdminAuth()) {
            return $response;
        }
        
        return view('admin.index');
    } 
    
    public function showLoginForm()
    {
        // If already logged in and is admin, redirect to dashboard
        if (Auth::check() && Auth::user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.login');
    }

    public function rolesAccess()
    {
        return view('admin.roles_access');
    }
    
        public function manageUsers()
    {
        return view('admin.manage_users');
    }

    public function enrollments(Request $request)
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $query = Student::query();

    // Apply filters
    if ($request->filled('status')) {
        $query->where('enrollment_status', $request->status);
    }

    if ($request->filled('grade_level')) {
        $query->where('grade_level', $request->grade_level);
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('first_name', 'LIKE', "%{$search}%")
              ->orWhere('last_name', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('lrn', 'LIKE', "%{$search}%");
        });
    }

    $enrollments = $query->orderBy('created_at', 'desc')->paginate(15);

    // Get counts for filter cards
    $pendingCount = Student::where('enrollment_status', 'pending')->count();
    $approvedCount = Student::where('enrollment_status', 'enrolled')->count();
    $rejectedCount = Student::where('enrollment_status', 'rejected')->count();
    $totalCount = Student::count();

    return view('admin.enrollments', compact(
        'enrollments',
        'pendingCount',
        'approvedCount', 
        'rejectedCount',
        'totalCount'
    ));
}

public function approveEnrollment($id)
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $student = Student::findOrFail($id);
    $student->update([
        'enrollment_status' => 'enrolled',
        'approved_at' => now(),
        'approved_by' => Auth::id()
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Student enrollment approved successfully!'
    ]);
}

public function rejectEnrollment($id)
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $student = Student::findOrFail($id);
    $student->update([
        'enrollment_status' => 'rejected',
        'rejected_at' => now(),
        'rejected_by' => Auth::id()
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Student enrollment rejected.'
    ]);
}

public function bulkApprove(Request $request)
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $studentIds = $request->input('student_ids', []);
    
    Student::whereIn('id', $studentIds)->update([
        'enrollment_status' => 'enrolled',
        'approved_at' => now(),
        'approved_by' => Auth::id()
    ]);

    return response()->json([
        'success' => true,
        'message' => count($studentIds) . ' students approved successfully!'
    ]);
}

public function bulkReject(Request $request)
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $studentIds = $request->input('student_ids', []);
    
    Student::whereIn('id', $studentIds)->update([
        'enrollment_status' => 'rejected',
        'rejected_at' => now(),
        'rejected_by' => Auth::id()
    ]);

    return response()->json([
        'success' => true,
        'message' => count($studentIds) . ' students rejected.'
    ]);
}

public function deleteEnrollment($id)
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $student = Student::findOrFail($id);
    $student->delete();

    return response()->json([
        'success' => true,
        'message' => 'Student record deleted successfully!'
    ]);
}

public function bulkDelete(Request $request)
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $studentIds = $request->input('student_ids', []);
    Student::whereIn('id', $studentIds)->delete();

    return response()->json([
        'success' => true,
        'message' => count($studentIds) . ' student records deleted successfully!'
    ]);
}

public function viewEnrollment($id)
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $student = Student::findOrFail($id);
    return view('admin.enrollment-details', compact('student'));
}

    
    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $remember = $request->has('remember');
        
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Check if the required tables exist
            if (!Schema::hasTable('roles') || !Schema::hasTable('model_has_roles')) {
                // Redirect to admin generator if tables don't exist
                return redirect()->route('show.admin.generator')
                    ->with('error', 'Permission tables do not exist. Please set up the admin role first.');
            }
            
            // Check if admin role exists
            $adminRoleExists = Role::where('name', 'admin')->exists();
            if (!$adminRoleExists) {
                // Redirect to admin generator if admin role doesn't exist
                return redirect()->route('show.admin.generator')
                    ->with('error', 'Admin role does not exist. Please set up the admin role first.');
            }
            
            // Check if user has admin role
            try {
                if ($user->hasRole('admin')) {
                    // Redirect to intended URL or dashboard
                    return redirect()->intended(route('admin.dashboard'))
                        ->with('success', 'Welcome to the admin dashboard!');
                } else {
                    Auth::logout();
                    return back()->with('error', 'You do not have admin privileges.');
                }
            } catch (\Exception $e) {
                Auth::logout();
                return redirect()->route('show.admin.generator')
                    ->with('error', 'Error checking admin role: ' . $e->getMessage());
            }
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
    
    /**
     * Handle admin logout
     */
    public function logout(Request $request)
{
    Auth::logout();
    
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    return redirect()->route('admin.login')
        ->with('success', 'You have been successfully logged out.');
}
    
    
    // Generate admin user
    public function generateAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'employee_id' => 'nullable|string|unique:admins',
            'department' => 'nullable|string|max:255',
            'admin_level' => 'required|in:super_admin,admin,moderator',
        ]);

        // Check if permission tables exist
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions')) {
            try {
                Artisan::call('vendor:publish', [
                    '--provider' => 'Spatie\Permission\PermissionServiceProvider',
                    '--tag' => 'migrations'
                ]);
                
                Artisan::call('migrate');
                
                if (!Schema::hasTable('roles') || !Schema::hasTable('permissions')) {
                    return back()->with('error', 'Failed to create permission tables. Please run migrations manually.');
                }
            } catch (\Exception $e) {
                return back()->with('error', 'Error running migrations: ' . $e->getMessage());
            }
        }

        DB::beginTransaction();
        
        try {
            // Create user first
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Create admin record
            $admin = Admin::create([
                'user_id' => $user->id,
                'employee_id' => $request->employee_id,
                'department' => $request->department,
                'admin_level' => $request->admin_level,
                'is_active' => true,
            ]);

            // Setup roles and permissions
            $this->setupAdminRoleAndPermissions($user, $request->admin_level);

            DB::commit();

            // Log in the new admin user
            Auth::login($user);

            return redirect()->route('admin.dashboard')
                ->with('success', 'Admin user created successfully! You are now logged in as an administrator.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error creating admin: ' . $e->getMessage());
        }
    }

    private function setupAdminRoleAndPermissions(User $user, string $adminLevel)
    {
        // Create or get admin role
        $adminRole = Role::firstOrCreate(['name' => $adminLevel]);

        // Define permissions based on admin level
        $permissions = $this->getPermissionsByLevel($adminLevel);

        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            $adminRole->givePermissionTo($permission);
        }

        // Assign role to user
        $user->assignRole($adminLevel);
    }

    private function getPermissionsByLevel(string $level): array
    {
        $basePermissions = [
            'Dashboard',
            'View Reports',
        ];

        $adminPermissions = [
            'Manage Users',
            'Manage Enrollments',
            'Manage Students',
            'View Analytics',
        ];

        $superAdminPermissions = [
            'Roles & Access',
            'System Settings',
            'Manage Admins',
            'Database Management',
            'Backup & Restore',
        ];

        return match($level) {
            'moderator' => $basePermissions,
            'admin' => array_merge($basePermissions, $adminPermissions),
            'super_admin' => array_merge($basePermissions, $adminPermissions, $superAdminPermissions),
            default => $basePermissions,
        };
    }
     
    
//     public function __construct()
// {
//     // Apply auth middleware to all methods except showLoginForm and login
//     $this->middleware('auth')->except(['showLoginForm', 'login', 'showGeneratorForm', 'generateAdmin']);
    
//     // Once the admin middleware is working, you can uncomment this:
//     // $this->middleware('admin')->except(['showLoginForm', 'login', 'showGeneratorForm', 'generateAdmin']);
// }

/**
 * Show the admin generator form
 */
public function showGeneratorForm()
{
    // Check if admin role exists
    $adminRoleExists = Role::where('name', 'admin')->where('guard_name', 'web')->exists();
    
    // Check if any admin users exist (only if the role exists)
    $adminExists = false;
    if ($adminRoleExists) {
        $adminExists = User::role('admin')->exists();
    }
    
    return view('admin.admin-generator', compact('adminRoleExists', 'adminExists'));
}


 public function users()
    {
        if ($response = $this->checkAdminAuth()) {
            return $response;
        }
        
        $users = User::with('roles')->get();
        return view('admin.users.index', compact('users'));
    }

public function roles()
{
    // Double-check authentication
    if (!Auth::check()) {
        return redirect()->route('admin.login')
            ->with('error', 'You must be logged in to access the admin area.');
    }
    
    // Get all roles with their permissions
    $roles = Role::with('permissions')->get();
    
    return view('admin.roles.index', compact('roles'));
}
}
