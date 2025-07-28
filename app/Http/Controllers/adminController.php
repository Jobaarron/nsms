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
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    use AdminAuthentication;
    
    public function index()
    {
        $totalUsers = User::count();
        $totalRoles = Role::count();

        if ($response = $this->checkAdminAuth()) {
            return $response;
        }
        $activeUsers = User::where('status', 'active')->count();
        $recentUsers = User::where('created_at', '>=', now()->subDays(30))->count();

         return view('admin.index', compact(
            'totalUsers',
            'totalRoles',
            'activeUsers',
            'recentUsers'
        ));
    }
    
    public function getStats()
    {
        $stats = [
            'total_users' => User::count(),
            'total_roles' => Role::count(),
            'active_users' => User::where('status', 'active')->count(),
            'recent_users' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];
        
        return response()->json($stats);
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
    $users = User::with('roles')->get();
    $roles = Role::with(['permissions', 'users'])->get();
    $permissions = Permission::with('roles')->get();
    
    return view('admin.roles_access', compact('users', 'roles', 'permissions'));
}

// AJAX Methods for Role Management
public function assignRole(Request $request)
{
    try {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|exists:roles,name'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->assignRole($request->role);

        return response()->json([
            'success' => true,
            'message' => "Role '{$request->role}' assigned successfully to {$user->name}."
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error assigning role: ' . $e->getMessage()
        ], 500);
    }
}

public function removeRole(Request $request)
{
    try {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->removeRole($request->role);

        return response()->json([
            'success' => true,
            'message' => "Role '{$request->role}' removed successfully from {$user->name}."
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error removing role: ' . $e->getMessage()
        ], 500);
    }
}

public function createRole(Request $request)
{
    try {
        $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'permissions' => 'array'
        ]);

        $role = Role::create(['name' => $request->name]);
        
        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'success' => true,
            'message' => "Role '{$request->name}' created successfully."
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error creating role: ' . $e->getMessage()
        ], 500);
    }
}

public function updateRole(Request $request, $id)
{
    try {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'array'
        ]);

        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'success' => true,
            'message' => "Role '{$request->name}' updated successfully."
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error updating role: ' . $e->getMessage()
        ], 500);
    }
}

public function deleteRole($id)
{
    try {
        $role = Role::findOrFail($id);
        
        // Prevent deletion of system roles
        $systemRoles = ['admin', 'super_admin', 'teacher', 'student', 'guidance', 'discipline'];
        if (in_array($role->name, $systemRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete system role.'
            ], 403);
        }

        $roleName = $role->name;
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => "Role '{$roleName}' deleted successfully."
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error deleting role: ' . $e->getMessage()
        ], 500);
    }
}

public function createPermission(Request $request)
{
    try {
        $request->validate([
            'name' => 'required|string|unique:permissions,name|max:255'
        ]);

        Permission::create(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => "Permission '{$request->name}' created successfully."
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error creating permission: ' . $e->getMessage()
        ], 500);
    }
}

public function updatePermission(Request $request, $id)
{
    try {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id
        ]);

        $permission = Permission::findOrFail($id);
        $permission->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => "Permission '{$request->name}' updated successfully."
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error updating permission: ' . $e->getMessage()
        ], 500);
    }
}

public function deletePermission($id)
{
    try {
        $permission = Permission::findOrFail($id);
        
        // Prevent deletion of system permissions
        $systemPermissions = ['Dashboard', 'Manage Users', 'Manage Enrollments', 'Manage Students', 'View Reports', 'Roles & Access', 'System Settings', 'Manage Roles'];
        if (in_array($permission->name, $systemPermissions)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete system permission.'
            ], 403);
        }

        $permissionName = $permission->name;
        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => "Permission '{$permissionName}' deleted successfully."
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error deleting permission: ' . $e->getMessage()
        ], 500);
    }
}

public function getUserRoles(User $user)
{
    return response()->json([
        'roles' => $user->roles
    ]);
}
    
 public function manageUsers()
    {
        $users = User::with(['roles', 'permissions'])->get();
        $roles = Role::all();
        return view('admin.manage_users', compact('users', 'roles'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'roles' => 'array'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        if ($request->roles) {
            $user->assignRole($request->roles);
        }

        return response()->json([
            'success' => true,
            'message' => "User '{$request->name}' created successfully!"
        ]);
    }

    public function showUser($id)
    {
        $user = User::with(['roles', 'permissions'])->findOrFail($id);
        return response()->json($user);
    }

    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8|confirmed',
            'roles' => 'array'
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        if ($request->has('roles')) {
            $user->syncRoles($request->roles ?? []);
        }

        return response()->json([
            'success' => true,
            'message' => "User '{$request->name}' updated successfully!"
        ]);
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deletion of admin user if it's the only one
        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the only admin user'
            ], 400);
        }

        $userName = $user->name;
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => "User '{$userName}' deleted successfully!"
        ]);
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
        try {
            if ($response = $this->checkAdminAuth()) {
                return $response;
            }

            $student = Student::findOrFail($id);
            
            $updated = $student->update([
                'enrollment_status' => 'enrolled',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
                'status_updated_at' => now(),
                'status_updated_by' => Auth::id()
            ]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student enrollment approved successfully!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update student status.'
                ], 500);
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Student not found for approval: ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error approving enrollment: ' . $e->getMessage(), [
                'student_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while approving the student. Please try again.'
            ], 500);
        }
    }

public function rejectEnrollment($id)
{
    try {
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

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Student not found.'
        ], 404);
    } catch (\Exception $e) {
        Log::error('Error rejecting enrollment: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error rejecting student: ' . $e->getMessage()
        ], 500);
    }
}

public function deleteEnrollment($id)
{
    try {
        if ($response = $this->checkAdminAuth()) {
            return $response;
        }

        $student = Student::findOrFail($id);
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student record deleted successfully!'
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Student not found.'
        ], 404);
    } catch (\Exception $e) {
        Log::error('Error deleting enrollment: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error deleting student: ' . $e->getMessage()
        ], 500);
    }
}

public function bulkApprove(Request $request)
{
    try {
        if ($response = $this->checkAdminAuth()) {
            return $response;
        }

        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        $studentIds = $request->input('student_ids', []);
        
        $updated = Student::whereIn('id', $studentIds)->update([
            'enrollment_status' => 'enrolled',
            'approved_at' => now(),
            'approved_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$updated} students approved successfully!"
        ]);

    } catch (\Exception $e) {
        Log::error('Error in bulk approve: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Bulk approve failed: ' . $e->getMessage()
        ], 500);
    }
}

public function bulkReject(Request $request)
{
    try {
        if ($response = $this->checkAdminAuth()) {
            return $response;
        }

        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        $studentIds = $request->input('student_ids', []);
        
        $updated = Student::whereIn('id', $studentIds)->update([
            'enrollment_status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$updated} students rejected."
        ]);

    } catch (\Exception $e) {
        Log::error('Error in bulk reject: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Bulk reject failed: ' . $e->getMessage()
        ], 500);
    }
}

public function bulkDelete(Request $request)
{
    try {
        if ($response = $this->checkAdminAuth()) {
            return $response;
        }

        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        $studentIds = $request->input('student_ids', []);
        $deleted = Student::whereIn('id', $studentIds)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} student records deleted successfully!"
        ]);

    } catch (\Exception $e) {
        Log::error('Error in bulk delete: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Bulk delete failed: ' . $e->getMessage()
        ], 500);
    }
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

        // Setup roles and permissions with proper model_has_permissions population
        $this->setupAdminRoleAndPermissions($user, $request->admin_level);

        // Clear permission cache to ensure fresh permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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
    // Create or get the admin role (using 'admin' as the primary role name as per your middleware)
    $adminRole = Role::firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web'
    ]);

    // Also create the specific level role if it's different
    if ($adminLevel !== 'admin') {
        $levelRole = Role::firstOrCreate([
            'name' => $adminLevel,
            'guard_name' => 'web'
        ]);
    }

    // Define permissions based on admin level
    $permissions = $this->getPermissionsByLevel($adminLevel);

    // Create permissions if they don't exist and assign to admin role
    foreach ($permissions as $permissionName) {
        $permission = Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web'
        ]);
        
        // Assign permission to admin role if not already assigned
        if (!$adminRole->hasPermissionTo($permission)) {
            $adminRole->givePermissionTo($permission);
        }

        // Also assign to level-specific role if it exists
        if (isset($levelRole) && !$levelRole->hasPermissionTo($permission)) {
            $levelRole->givePermissionTo($permission);
        }
    }

    // Assign the main 'admin' role to user (this is what your middleware checks for)
    if (!$user->hasRole('admin')) {
        $user->assignRole('admin');
    }

    // Also assign the level-specific role if different
    if ($adminLevel !== 'admin' && !$user->hasRole($adminLevel)) {
        $user->assignRole($adminLevel);
    }

    // IMPORTANT: Also directly assign permissions to user 
    // This populates the model_has_permissions table
    foreach ($permissions as $permissionName) {
        $permission = Permission::where('name', $permissionName)->where('guard_name', 'web')->first();
        if ($permission && !$user->hasDirectPermission($permission)) {
            $user->givePermissionTo($permission);
        }
    }

    // Refresh user permissions and roles
    $user->load('permissions', 'roles');
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
        'Manage Roles', // This permission is referenced in your role management methods
    ];

    return match($level) {
        'moderator' => $basePermissions,
        'admin' => array_merge($basePermissions, $adminPermissions),
        'super_admin' => array_merge($basePermissions, $adminPermissions, $superAdminPermissions),
        default => array_merge($basePermissions, $adminPermissions), // Default to admin level
    };
}
    
    public function fixAdminPermissions()
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    // Only super admins can fix permissions
    if (!Auth::user()->hasRole('super_admin') && !Auth::user()->hasPermissionTo('Manage Roles')) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    try {
        // Get all users with admin role
        $adminUsers = User::role('admin')->get();
        
        $fixed = 0;
        foreach ($adminUsers as $user) {
            // Try to determine admin level from existing roles
            $adminLevel = 'admin'; // default
            
            if ($user->hasRole('super_admin')) {
                $adminLevel = 'super_admin';
            } elseif ($user->hasRole('moderator')) {
                $adminLevel = 'moderator';
            }
            
            // Re-setup permissions
            $this->setupAdminRoleAndPermissions($user, $adminLevel);
            $fixed++;
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => "Fixed permissions for {$fixed} admin users."
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fixing permissions: ' . $e->getMessage()
        ], 500);
    }
}

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

    public function editEnrollment($id)
    {
    try {
        if ($response = $this->checkAdminAuth()) {
            return $response;
        }

        $student = Student::findOrFail($id);
        return view('admin.enrollment-edit', compact('student'));

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return redirect()->route('admin.enrollments')
            ->with('error', 'Student not found.');
    } catch (\Exception $e) {
        Log::error('Error loading enrollment edit page: ' . $e->getMessage());
        return redirect()->route('admin.enrollments')
            ->with('error', 'Error loading student information.');
    }
    }

    

public function updateEnrollmentStatus(Request $request, $id)
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $request->validate([
        'status' => 'required|in:pending,enrolled,rejected',
        'reason' => 'nullable|string|max:500'
    ]);

    $student = Student::findOrFail($id);
    $student->update([
        'enrollment_status' => $request->status,
        'status_reason' => $request->reason,
        'status_updated_at' => now(),
        'status_updated_by' => Auth::id()
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Student status updated successfully!'
    ]);
}

public function exportSelected(Request $request)
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $studentIds = $request->input('student_ids', []);
    $students = Student::whereIn('id', $studentIds)->get();

    // Create CSV export
    $filename = 'selected_enrollments_' . date('Y-m-d_H-i-s') . '.csv';
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ];

    $callback = function() use ($students) {
        $file = fopen('php://output', 'w');
        fputcsv($file, ['Name', 'Email', 'Grade Level', 'Status', 'Guardian', 'Contact', 'Applied Date']);

        foreach ($students as $student) {
            fputcsv($file, [
                $student->first_name . ' ' . $student->last_name,
                $student->email,
                $student->grade_level,
                $student->enrollment_status,
                $student->guardian_name,
                $student->guardian_contact,
                $student->created_at->format('Y-m-d H:i:s')
            ]);
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function exportAll(Request $request)
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $query = Student::query();

    // Apply same filters as the main page
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
              ->orWhere('email', 'LIKE', "%{$search}%");
        });
    }

    $students = $query->get();
    $format = $request->input('format', 'excel');

    if ($format === 'pdf') {
        // PDF export logic here
        return $this->exportToPDF($students);
    }

    // Excel/CSV export
    $filename = 'all_enrollments_' . date('Y-m-d_H-i-s') . '.csv';
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ];

    $callback = function() use ($students) {
        $file = fopen('php://output', 'w');
        fputcsv($file, ['Name', 'Email', 'Grade Level', 'Status', 'Guardian', 'Contact', 'Applied Date']);

        foreach ($students as $student) {
            fputcsv($file, [
                $student->first_name . ' ' . $student->last_name,
                $student->email,
                $student->grade_level,
                $student->enrollment_status,
                $student->guardian_name,
                $student->guardian_contact,
                $student->created_at->format('Y-m-d H:i:s')
            ]);
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function sendBulkNotification(Request $request)
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $request->validate([
        'student_ids' => 'required|array',
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
        'type' => 'required|in:email,sms,both'
    ]);

    $students = Student::whereIn('id', $request->student_ids)->get();
    $sent = 0;

    foreach ($students as $student) {
        if ($request->type === 'email' || $request->type === 'both') {
            // Send email notification
            // Mail::to($student->email)->send(new EnrollmentNotification($request->subject, $request->message));
            $sent++;
        }
        
        if ($request->type === 'sms' || $request->type === 'both') {
            // Send SMS notification
            // SMS::send($student->guardian_contact, $request->message);
            $sent++;
        }
    }

    return response()->json([
        'success' => true,
        'message' => "Notifications sent to {$sent} recipients!"
    ]);
}

public function printEnrollments(Request $request)
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $studentIds = $request->input('student_ids', []);
    $students = Student::whereIn('id', $studentIds)->get();

    return view('admin.enrollments-print', compact('students'));
}

private function exportToPDF($students)
{
    // PDF export implementation
    // You can use libraries like DomPDF or TCPDF
    return response()->json([
        'success' => false,
        'message' => 'PDF export not implemented yet'
    ]);
}

public function updateEnrollment(Request $request, $id)
{
    try {
        if ($response = $this->checkAdminAuth()) {
            return $response;
        }

        $student = Student::findOrFail($id);

        // Validate the request
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'email' => 'required|email|max:255|unique:students,email,' . $id,
            'contact_number' => 'required|string|max:20',
            'lrn' => 'nullable|string|max:20|unique:students,lrn,' . $id,
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'religion' => 'nullable|string|max:100',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'zip_code' => 'required|string|max:10',
            'grade_level' => 'required|string|max:50',
            'strand' => 'nullable|string|max:100',
            'enrollment_status' => 'required|in:pending,enrolled,rejected',
            'guardian_name' => 'required|string|max:255',
            'guardian_contact' => 'required|string|max:20',
            'father_name' => 'nullable|string|max:255',
            'father_occupation' => 'nullable|string|max:255',
            'father_contact' => 'nullable|string|max:20',
            'mother_name' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|string|max:255',
            'mother_contact' => 'nullable|string|max:20',
            'last_school_type' => 'nullable|in:public,private',
            'last_school_name' => 'nullable|string|max:255',
            'medical_history' => 'nullable|string|max:1000',
            'payment_mode' => 'required|in:cash,installment,scholarship',
            'new_password' => 'nullable|string|min:6|max:255',
        ]);

        // Add update tracking fields
        $validatedData['updated_by'] = Auth::id();
        $validatedData['updated_at'] = now();

        // If status is being changed to enrolled, add approval fields
        if ($validatedData['enrollment_status'] === 'enrolled' && $student->enrollment_status !== 'enrolled') {
            $validatedData['approved_at'] = now();
            $validatedData['approved_by'] = Auth::id();
        }

        // If status is being changed to rejected, add rejection fields
        if ($validatedData['enrollment_status'] === 'rejected' && $student->enrollment_status !== 'rejected') {
            $validatedData['rejected_at'] = now();
            $validatedData['rejected_by'] = Auth::id();
        }

        // Handle password update if provided
        if (!empty($validatedData['new_password'])) {
            $validatedData['password'] = Hash::make($validatedData['new_password']);
        }
        
        // Remove new_password from the array since it's not a database field
        unset($validatedData['new_password']);

        // Update the student record
        $student->update($validatedData);

        return redirect()->route('admin.enrollments')
            ->with('success', 'Student information updated successfully!');

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return redirect()->route('admin.enrollments')
            ->with('error', 'Student not found.');
    } catch (\Illuminate\Validation\ValidationException $e) {
        return back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        Log::error('Error updating enrollment: ' . $e->getMessage());
        return back()->with('error', 'Error updating student information. Please try again.')
            ->withInput();
    }
}




}
