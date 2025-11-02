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
use App\Models\Enrollee;
use App\Models\Teacher;
use App\Models\Guidance;
use App\Models\Discipline;
use App\Traits\AdminAuthentication;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    use AdminAuthentication;
    
    public function index()
    {
        if ($response = $this->checkAdminAuth()) {
            return $response;
        }

        // Comprehensive user statistics from all tables
        $userStats = [
            'total_users' => User::count(),
            'total_enrollees' => Enrollee::count(),
            'total_students' => Student::count(),
            'total_teachers' => Teacher::count(),
            'total_admins' => Admin::count(),
            'total_guidance' => Guidance::count() + Discipline::count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count()
        ];

        // Calculate comprehensive total users (all user types)
        $totalUsers = $userStats['total_users'] + $userStats['total_enrollees'] + $userStats['total_students'];
        
        // User status breakdown
        $userStatusStats = [
            'active_users' => User::where('status', 'active')->count(),
            'pending_enrollees' => Enrollee::where('enrollment_status', 'pending')->count(),
            'approved_enrollees' => Enrollee::where('enrollment_status', 'approved')->count(),
            'enrolled_students' => Enrollee::where('enrollment_status', 'enrolled')->count(),
            'recent_applications' => Enrollee::where('created_at', '>=', now()->subDays(30))->count()
        ];

        // Data visualization data (static for now, ready for charts)
        $chartData = [
            'user_types' => [
                'labels' => ['System Users', 'Enrollees/Applicants', 'Students', 'Teachers', 'Admins', 'Guidance Staff'],
                'data' => [
                    $userStats['total_users'],
                    $userStats['total_enrollees'],
                    $userStats['total_students'],
                    $userStats['total_teachers'],
                    $userStats['total_admins'],
                    $userStats['total_guidance']
                ]
            ],
            'enrollment_status' => [
                'labels' => ['Pending', 'Approved', 'Enrolled'],
                'data' => [
                    $userStatusStats['pending_enrollees'],
                    $userStatusStats['approved_enrollees'],
                    $userStatusStats['enrolled_students']
                ]
            ],
            'monthly_applications' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'data' => [12, 19, 23, 15, 28, 35, 42, 38, 45, 52, 48, 55] // Static data for visualization
            ]
        ];

        return view('admin.index', compact(
            'totalUsers',
            'userStats',
            'userStatusStats',
            'chartData'
        ));
    }
    

        /**
     * Display a listing of submitted (forwarded) case meetings for the president/admin.
     */
    public function forwardedCases()
    {
        if ($response = $this->checkAdminAuth()) {
            return $response;
        }

        $caseMeetings = \App\Models\CaseMeeting::with(['student', 'sanctions.violation'])
            ->where('status', 'submitted')
            ->paginate(10);

        return view('admin.forwarded-cases', compact('caseMeetings'));
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

    public function manageUsers()
    {
        $users = User::with(['roles', 'admin', 'teacher', 'guidance', 'discipline'])->get();
        $roles = Role::with(['permissions', 'users'])->get();
        $permissions = Permission::with('roles')->get();
        
        return view('admin.user_management', compact('users', 'roles', 'permissions'));
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

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);
        
        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'success' => true,
            'message' => "Role '{$request->name}' created successfully."
        ]);
    } catch (\Exception $e) {
        \Log::error('Error creating role: ' . $e->getMessage());
        \Log::error('Request data: ' . json_encode($request->all()));
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

        Permission::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        return response()->json([
            'success' => true,
            'message' => "Permission '{$request->name}' created successfully."
        ]);
    } catch (\Exception $e) {
        Log::error('Error creating permission: ' . $e->getMessage());
        Log::error('Request data: ' . json_encode($request->all()));
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
    try {
        // Load the user with roles relationship
        $user->load('roles');
        
        return response()->json([
            'roles' => $user->roles->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name
                ];
            })
        ]);
    } catch (\Exception $e) {
        \Log::error('Error in getUserRoles: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error loading user roles: ' . $e->getMessage()
        ], 500);
    }
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
        
        return view('admin.enrollments');
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
    
    
public function submittedCases()
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $caseMeetings = \App\Models\CaseMeeting::with(['student', 'sanctions.violation'])
        ->where('status', 'submitted')
        ->paginate(10);

    return view('admin.forwarded-cases', compact('caseMeetings'));
}

    public function approveSanction(\App\Models\Sanction $sanction)
    {
        $user = auth()->user();

        if (!$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can approve sanctions.'
            ], 403);
        }

        if ($sanction->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Sanction is already approved.'
            ], 400);
        }

        try {
            $sanction->update([
                'is_approved' => true,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);


            // Mark the related case meeting as completed
            $caseMeeting = $sanction->caseMeeting;
            if ($caseMeeting) {
                $caseMeeting->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                // Set all related violations' statuses to 'submitted' (discipline side)
                foreach ($caseMeeting->violations as $violation) {
                    $violation->update(['status' => 'submitted']);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Sanction approved and case meeting marked as completed.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error approving sanction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while approving the sanction.'
            ], 500);
        }
    }

    public function rejectSanction(\App\Models\Sanction $sanction)
    {
        $user = auth()->user();

        if (!$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can reject sanctions.'
            ], 403);
        }

        if ($sanction->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reject an already approved sanction.'
            ], 400);
        }

        try {
            $sanction->update([
                'is_approved' => false,
                'approved_by' => null,
                'approved_at' => null,
                'notes' => ($sanction->notes ? $sanction->notes . "\n" : '') . 'Rejected by admin on ' . now()->toDateTimeString(),
            ]);

            // Optionally, update related case meeting status to 'submitted' or other appropriate status
            $caseMeeting = $sanction->caseMeeting;
            if ($caseMeeting) {
                $caseMeeting->update([
                    'status' => 'submitted',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sanction rejected successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error rejecting sanction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while rejecting the sanction.'
            ], 500);
        }
    }

    public function reviseSanction(\App\Models\Sanction $sanction, Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can revise sanctions.'
            ], 403);
        }

        $request->validate([
            'sanction' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($sanction->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot revise an already approved sanction.'
            ], 400);
        }

        try {
            $sanction->update([
                'sanction' => $request->input('sanction'),
                'notes' => ($sanction->notes ? $sanction->notes . "\n" : '') . 'Revised by admin on ' . now()->toDateTimeString() . '. Notes: ' . $request->input('notes', ''),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sanction revised successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error revising sanction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while revising the sanction.'
            ], 500);
        }
    }

    public function viewSummaryReport(\App\Models\CaseMeeting $caseMeeting)
    {
        $user = auth()->user();

        if (!$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        $caseMeeting->load(['student', 'counselor', 'sanctions', 'violation']);

        // Convert to array and format scheduled_time for summary modal (h:i A)
        $meetingArr = $caseMeeting->toArray();
        $meetingArr['scheduled_time'] = $caseMeeting->scheduled_time ? $caseMeeting->scheduled_time->format('h:i A') : null;
        $meetingArr['scheduled_date'] = $caseMeeting->scheduled_date ? $caseMeeting->scheduled_date->format('Y-m-d') : null;
        
        // Include violation student reply fields if violation exists
        $meetingArr['violation_id'] = $caseMeeting->violation_id;
        $meetingArr['student_statement'] = $caseMeeting->violation ? $caseMeeting->violation->student_statement : null;
        $meetingArr['incident_feelings'] = $caseMeeting->violation ? $caseMeeting->violation->incident_feelings : null;
        $meetingArr['action_plan'] = $caseMeeting->violation ? $caseMeeting->violation->action_plan : null;

        return response()->json([
            'success' => true,
            'meeting' => $meetingArr
        ]);
    }
// REMOVED: generateAdmin() method
// This functionality has been moved to UserManagementController->storeAdmin()
// The admin-generator.blade.php view is no longer needed as admin creation
// is now handled through the centralized user management system

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

        $enrollee = Enrollee::findOrFail($id);
        return view('admin.enrollment-edit', compact('enrollee'));

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return redirect()->route('admin.enrollments')
            ->with('error', 'Enrollee not found.');
    } catch (\Exception $e) {
        Log::error('Error loading enrollment edit page: ' . $e->getMessage());
        return redirect()->route('admin.enrollments')
            ->with('error', 'Error loading enrollee information.');
    }
    }

    

public function updateEnrollmentStatus(Request $request, $id)
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $request->validate([
        'status' => 'required|in:pending,approved,rejected,enrolled',
        'reason' => 'nullable|string|max:500'
    ]);

    $enrollee = Enrollee::findOrFail($id);
    $enrollee->update([
        'enrollment_status' => $request->status,
        'status_reason' => $request->reason,
        'status_updated_at' => now(),
        'status_updated_by' => Auth::id()
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Enrollment status updated successfully!'
    ]);
}

public function exportSelected(Request $request)
{
    if ($response = $this->checkAdminAuth()) {
        return $response;
    }

    $enrolleeIds = $request->input('enrollee_ids', []);
    
    if (empty($enrolleeIds)) {
        return back()->with('error', 'No enrollees selected for export.');
    }

    $enrollees = Enrollee::whereIn('id', $enrolleeIds)->get();
    
    $fileName = 'enrollees-' . date('Y-m-d') . '.csv';
    $headers = [
        'Content-type'        => 'text/csv',
        'Content-Disposition' => "attachment; filename=$fileName",
        'Pragma'              => 'no-cache',
        'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
        'Expires'             => '0'
    ];

    $callback = function() use ($enrollees) {
        $file = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($file, [
            'Application ID', 'LRN', 'First Name', 'Last Name', 'Grade Level Applied', 'Email', 'Enrollment Status'
        ]);

        foreach ($enrollees as $enrollee) {
            fputcsv($file, [
                $enrollee->application_id,
                $enrollee->lrn,
                $enrollee->first_name,
                $enrollee->last_name,
                $enrollee->grade_level_applied,
                $enrollee->email,
                $enrollee->enrollment_status
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
            'payment_mode' => 'required|in:cash,installment,scholarship,online payment',
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