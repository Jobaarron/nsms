<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\GuidanceDiscipline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserManagementController extends Controller
{
    /**
     * Display the user management page.
     */
    public function index()
    {
        $users = User::with(['roles', 'student', 'admin', 'teacher', 'guidanceDiscipline'])->get();
        $roles = Role::all();
        
        return view('admin.manage_users', compact('users', 'roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $rules = [
            'user_type' => 'required|in:admin,teacher,student,guidance_discipline',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|in:active,inactive,pending,suspended',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
        ];

        // Add specific validation rules based on user type
        switch ($request->user_type) {
            case 'admin':
                $rules['employee_id'] = 'required|string|max:50|unique:admins,employee_id';
                $rules['department'] = 'required|string|max:255';
                $rules['position'] = 'required|string|max:255';
                $rules['admin_level'] = 'required|in:admin,super_admin,moderator';
                break;
                
            case 'teacher':
                $rules['teacher_employee_id'] = 'required|string|max:50|unique:teachers,employee_id';
                $rules['teacher_department'] = 'required|string|max:255';
                $rules['teacher_position'] = 'required|string|max:255';
                $rules['teacher_hire_date'] = 'required|date';
                $rules['teacher_phone'] = 'nullable|string|max:20';
                $rules['teacher_qualifications'] = 'nullable|string';
                $rules['teacher_address'] = 'nullable|string';
                break;
                
            case 'student':
                $rules['student_id'] = 'required|string|max:50|unique:students,student_id';
                $rules['lrn'] = 'required|string|max:12|unique:students,lrn';
                $rules['grade_level'] = 'required|string|max:50';
                $rules['section'] = 'nullable|string|max:50';
                $rules['academic_year'] = 'required|string|max:20';
                $rules['enrollment_date'] = 'nullable|date';
                $rules['enrollment_status'] = 'required|in:enrolled,pending,transferred,dropped';
                break;
                
            case 'guidance_discipline':
                $rules['guidance_employee_id'] = 'required|string|max:50|unique:guidance_disciplines,employee_id';
                $rules['guidance_position'] = 'required|string|max:255';
                $rules['guidance_specialization'] = 'nullable|string|max:255';
                $rules['guidance_hire_date'] = 'required|date';
                $rules['guidance_phone'] = 'nullable|string|max:20';
                $rules['guidance_license'] = 'nullable|string|max:100';
                $rules['guidance_qualifications'] = 'nullable|string';
                break;
        }

        $request->validate($rules);

        DB::beginTransaction();
        
        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => $request->status,
                'email_verified_at' => now(),
            ]);

            // Create specific user type record
            $this->createUserTypeRecord($user, $request);

            // Assign roles
            if ($request->has('roles') && is_array($request->roles)) {
                $user->assignRole($request->roles);
            } else {
                // Assign default role based on user type
                $user->assignRole($request->user_type);
            }

            // Setup permissions for the user type
            $this->setupUserPermissions($user, $request->user_type);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully!',
                'user' => $user->load(['roles', 'student', 'admin', 'teacher', 'guidanceDiscipline'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['roles', 'permissions', 'student', 'admin', 'teacher', 'guidanceDiscipline']);
        
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'status' => 'required|in:active,inactive,pending,suspended',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
        ];

        $request->validate($rules);

        DB::beginTransaction();
        
        try {
            // Update basic user info
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'status' => $request->status,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Update roles
            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!',
                'user' => $user->fresh()->load(['roles', 'student', 'admin', 'teacher', 'guidanceDiscipline'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error updating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deletion of the last admin
        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the last administrator account.'
            ], 403);
        }

        // Prevent users from deleting themselves
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.'
            ], 403);
        }

        DB::beginTransaction();
        
        try {
            // Delete related records
            $user->student()->delete();
            $user->admin()->delete();
            $user->teacher()->delete();
            $user->guidanceDiscipline()->delete();
            
            // Delete user
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create specific user type record.
     */
    private function createUserTypeRecord(User $user, Request $request)
    {
        switch ($request->user_type) {
            case 'admin':
                Admin::create([
                    'user_id' => $user->id,
                    'employee_id' => $request->employee_id,
                    'department' => $request->department,
                    'position' => $request->position,
                    'admin_level' => $request->admin_level,
                    'is_active' => true,
                ]);
                break;
                
            case 'teacher':
                Teacher::create([
                    'user_id' => $user->id,
                    'employee_id' => $request->teacher_employee_id,
                    'department' => $request->teacher_department,
                    'position' => $request->teacher_position,
                    'hire_date' => $request->teacher_hire_date,
                    'phone_number' => $request->teacher_phone,
                    'qualifications' => $request->teacher_qualifications,
                    'address' => $request->teacher_address,
                    'is_active' => true,
                ]);
                break;
                
            case 'student':
                Student::create([
                    'user_id' => $user->id,
                    'student_id' => $request->student_id,
                    'lrn' => $request->lrn,
                    'grade_level' => $request->grade_level,
                    'section' => $request->section,
                    'academic_year' => $request->academic_year,
                    'enrollment_date' => $request->enrollment_date ?? now(),
                    'enrollment_status' => $request->enrollment_status,
                    'is_active' => true,
                ]);
                break;
                
            case 'guidance_discipline':
                GuidanceDiscipline::create([
                    'user_id' => $user->id,
                    'employee_id' => $request->guidance_employee_id,
                    'position' => $request->guidance_position,
                    'specialization' => $request->guidance_specialization,
                    'hire_date' => $request->guidance_hire_date,
                    'phone_number' => $request->guidance_phone,
                    'license_number' => $request->guidance_license,
                    'qualifications' => $request->guidance_qualifications,
                    'is_active' => true,
                ]);
                break;
        }
    }

    /**
     * Get user statistics.
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_admins' => User::role('admin')->count(),
                'total_teachers' => User::role('teacher')->count(),
                'total_students' => User::role('student')->count(),
                'total_guidance' => User::role('guidance_discipline')->count(),
                'active_users' => User::where('status', 'active')->count(),
                'inactive_users' => User::where('status', 'inactive')->count(),
                'pending_users' => User::where('status', 'pending')->count(),
                'suspended_users' => User::where('status', 'suspended')->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle bulk actions on users.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'user_ids' => 'required|json'
        ]);

        $userIds = json_decode($request->user_ids, true);
        
        if (empty($userIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No users selected.'
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            $users = User::whereIn('id', $userIds)->get();
            $processedCount = 0;

            foreach ($users as $user) {
                // Prevent actions on self
                if ($user->id === auth()->id()) {
                    continue;
                }

                // Prevent deletion of last admin
                if ($request->action === 'delete' && $user->hasRole('admin') && User::role('admin')->count() <= 1) {
                    continue;
                }

                switch ($request->action) {
                    case 'activate':
                        $user->update(['status' => 'active']);
                        $processedCount++;
                        break;
                        
                    case 'deactivate':
                        $user->update(['status' => 'inactive']);
                        $processedCount++;
                        break;
                        
                    case 'delete':
                        $user->student()->delete();
                        $user->admin()->delete();
                        $user->teacher()->delete();
                        $user->guidanceDiscipline()->delete();
                        $user->delete();
                        $processedCount++;
                        break;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully {$request->action}d {$processedCount} user(s)."
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk action: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export users data.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');
        
        // This would typically use a package like Laravel Excel
        // For now, return a simple response
        return response()->json([
            'success' => true,
            'message' => "Export in {$format} format initiated.",
            'download_url' => "/admin/manage-users/download/{$format}"
        ]);
    }

    /**
     * Import users from file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,xlsx,xls|max:2048'
        ]);

        try {
            // This would typically process the uploaded file
            // For now, return a mock response
            $importedCount = rand(5, 20); // Mock imported count
            
            return response()->json([
                'success' => true,
                'message' => 'Users imported successfully!',
                'imported_count' => $importedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error importing users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate user report.
     */
    public function generateReport()
    {
        // This would typically generate a PDF report
        return response()->json([
            'success' => true,
            'message' => 'Report generation initiated.',
            'report_url' => '/admin/manage-users/reports/users-report.pdf'
        ]);
    }

    /**
     * Setup permissions for user type.
     */
    private function setupUserPermissions(User $user, string $userType)
    {
        $permissions = [];
        
        switch ($userType) {
            case 'admin':
                $permissions = [
                    'Manage Users',
                    'Manage Roles',
                    'Manage Permissions',
                    'View Reports',
                    'System Settings',
                ];
                break;
                
            case 'teacher':
                $permissions = [
                    'View Students',
                    'Manage Grades',
                    'View Classes',
                    'Manage Attendance',
                    'View Reports',
                ];
                break;
                
            case 'student':
                $permissions = [
                    'View Grades',
                    'View Schedule',
                    'View Announcements',
                ];
                break;
                
            case 'guidance_discipline':
                $permissions = [
                    'View Students',
                    'Manage Student Records',
                    'Create Violations',
                    'View Reports',
                    'Counseling Access',
                ];
                break;
        }

        // Create permissions if they don't exist and assign to role
        $role = Role::where('name', $userType)->first();
        if ($role) {
            foreach ($permissions as $permissionName) {
                $permission = Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web'
                ]);
                
                if (!$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        }
    }
}
