<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Admin;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Enrollee;
use App\Models\Guidance;
use App\Models\Discipline;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserManagementController extends Controller
{
    public function __construct()
    {
        // Middleware is handled at route level
    }

    /**
     * Display the user management page
     */
    public function index()
    {
        $users = User::with(['roles', 'admin', 'teacher', 'student', 'guidance', 'discipline'])->get();
        $enrollees = Enrollee::all(); // Get all enrollees separately since they use different auth model
        $roles = Role::with(['permissions', 'users'])->get();
        $permissions = Permission::with('roles')->get();
        
        // Add enrollee count to applicant role
        $applicantRole = $roles->where('name', 'applicant')->first();
        if ($applicantRole) {
            $applicantRole->enrollee_count = $enrollees->count();
        }
        
        return view('admin.user_management', compact('users', 'enrollees', 'roles', 'permissions'));
    }

    /**
     * Store a new admin user
     */
    public function storeAdmin(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'employee_id' => 'nullable|string|max:255|unique:admins,employee_id',
                'department' => 'nullable|string|max:255',
                'admin_level' => 'required|string|in:super_admin,admin,moderator'
            ]);

            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('admin123'), // Default password
                'email_verified_at' => now(),
            ]);

            // Create admin record
            Admin::create([
                'user_id' => $user->id,
                'employee_id' => $request->employee_id,
                'department' => $request->department,
                'admin_level' => $request->admin_level,
            ]);

            // Assign roles based on admin level
            $this->ensureRoleExists('admin');
            if ($request->admin_level === 'super_admin') {
                $this->ensureRoleExists('super_admin');
                $user->assignRole(['admin', 'super_admin']);
            } else {
                $user->assignRole('admin');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Admin user created successfully!',
                'user' => $user->load(['roles', 'admin'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating admin user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating admin user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new teacher user
     */
    public function storeTeacher(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'employee_id' => 'nullable|string|max:255|unique:teachers,employee_id',
                'department' => 'nullable|string|max:255',
                'position' => 'nullable|string|max:255',
                'specialization' => 'nullable|string|max:255',
                'hire_date' => 'nullable|date'
            ]);

            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('teacher123'), // Default password
                'email_verified_at' => now(),
            ]);

            // Create teacher record
            Teacher::create([
                'user_id' => $user->id,
                'employee_id' => $request->employee_id,
                'department' => $request->department,
                'position' => $request->position,
                'specialization' => $request->specialization,
                'hire_date' => $request->hire_date,
            ]);

            // Assign teacher role
            $this->ensureRoleExists('teacher');
            $user->assignRole('teacher');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Teacher user created successfully!',
                'user' => $user->load(['roles', 'teacher'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating teacher user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating teacher user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new guidance user
     */
    public function storeGuidance(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'employee_id' => 'nullable|string|max:255|unique:guidances,employee_id',
                'position' => 'nullable|string|max:255',
                'specialization' => 'nullable|string|max:255',
                'hire_date' => 'nullable|date'
            ]);

            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('guidance123'), // Default password
                'email_verified_at' => now(),
            ]);

            // Split name into first and last name
            $nameParts = explode(' ', $request->name, 2);
            $firstName = $nameParts[0];
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

            // Create guidance record
            Guidance::create([
                'user_id' => $user->id,
                'employee_id' => $request->employee_id ?: 'GDN' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'position' => $request->position,
                'specialization' => $request->specialization ?: 'guidance_counselor',
                'hire_date' => $request->hire_date,
                'is_active' => true
            ]);

            // Assign guidance counselor role (updated to match RolePermissionSeeder)
            $this->ensureRoleExists('guidance_counselor');
            $user->assignRole('guidance_counselor');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Guidance user created successfully!',
                'user' => $user->load(['roles', 'guidance', 'discipline'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating guidance user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating guidance user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new discipline user
     */
    public function storeDiscipline(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'employee_id' => 'nullable|string|max:255|unique:disciplines,employee_id',
                'position' => 'nullable|string|max:255',
                'specialization' => 'nullable|string|max:255',
                'hire_date' => 'nullable|date'
            ]);

            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('discipline123'), // Default password
                'email_verified_at' => now(),
            ]);

            // Split name into first and last name
            $nameParts = explode(' ', $request->name, 2);
            $firstName = $nameParts[0];
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

            // Create discipline record
            Discipline::create([
                'user_id' => $user->id,
                'employee_id' => $request->employee_id ?: 'DSC' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'position' => $request->position,
                'specialization' => $request->specialization ?: 'discipline_head',
                'hire_date' => $request->hire_date,
                'is_active' => true
            ]);

            // Assign discipline role
            $this->ensureRoleExists('discipline');
            $user->assignRole('discipline');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Discipline user created successfully!',
                'user' => $user->load(['roles', 'guidance', 'discipline'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating discipline user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating discipline user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new guidance counselor user
     */
    public function createGuidanceCounselor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'employee_id' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('guidance123'),
                'email_verified_at' => now(),
            ]);

            // Create guidance record
            Guidance::create([
                'user_id' => $user->id,
                'employee_id' => $request->employee_id ?: 'GC' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                'first_name' => explode(' ', $request->name, 2)[0],
                'last_name' => explode(' ', $request->name, 2)[1] ?? '',
                'position' => $request->position,
                'specialization' => $request->specialization ?: 'guidance_counselor',
                'hire_date' => $request->hire_date,
                'is_active' => true
            ]);

            // Assign guidance counselor role
            $this->ensureRoleExists('guidance_counselor');
            $user->assignRole('guidance_counselor');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Guidance Counselor created successfully!',
                'user' => $user->load(['roles', 'guidance', 'discipline'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating guidance counselor: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating guidance counselor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new discipline head user
     */
    public function createDisciplineHead(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'employee_id' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('discipline123'),
                'email_verified_at' => now(),
            ]);

            // Create discipline record
            Discipline::create([
                'user_id' => $user->id,
                'employee_id' => $request->employee_id ?: 'DH' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                'first_name' => explode(' ', $request->name, 2)[0],
                'last_name' => explode(' ', $request->name, 2)[1] ?? '',
                'position' => $request->position,
                'specialization' => $request->specialization ?: 'discipline_head',
                'hire_date' => $request->hire_date,
                'is_active' => true
            ]);

            // Assign discipline head role
            $this->ensureRoleExists('discipline_head');
            $user->assignRole('discipline_head');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Discipline Head created successfully!',
                'user' => $user->load(['roles', 'guidance', 'discipline'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating discipline head: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating discipline head: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new discipline officer user
     */
    public function createDisciplineOfficer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'employee_id' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('discipline123'),
                'email_verified_at' => now(),
            ]);

            // Create discipline record
            Discipline::create([
                'user_id' => $user->id,
                'employee_id' => $request->employee_id ?: 'DO' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                'first_name' => explode(' ', $request->name, 2)[0],
                'last_name' => explode(' ', $request->name, 2)[1] ?? '',
                'position' => $request->position,
                'specialization' => $request->specialization ?: 'discipline_officer',
                'hire_date' => $request->hire_date,
                'is_active' => true
            ]);

            // Assign discipline officer role
            $this->ensureRoleExists('discipline_officer');
            $user->assignRole('discipline_officer');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Discipline Officer created successfully!',
                'user' => $user->load(['roles', 'guidance', 'discipline'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating discipline officer: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating discipline officer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new cashier user
     */
    public function createCashier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'employee_id' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('cashier123'),
                'email_verified_at' => now(),
            ]);

            // Create admin record for cashier
            Admin::create([
                'user_id' => $user->id,
                'employee_id' => $request->employee_id,
                'department' => 'Finance',
                'position' => $request->position,
                'admin_level' => 'staff',
            ]);

            // Assign cashier role
            $this->ensureRoleExists('cashier');
            $user->assignRole('cashier');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cashier created successfully!',
                'user' => $user->load(['roles', 'admin'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating cashier: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating cashier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new faculty head user
     */
    public function createFacultyHead(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'employee_id' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('faculty123'),
                'email_verified_at' => now(),
            ]);

            // Create teacher record
            Teacher::create([
                'user_id' => $user->id,
                'employee_id' => $request->employee_id,
                'department' => $request->department,
                'position' => $request->position,
                'specialization' => $request->specialization,
                'hire_date' => $request->hire_date,
            ]);

            // Assign faculty head role
            $this->ensureRoleExists('faculty_head');
            $user->assignRole('faculty_head');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Faculty Head created successfully!',
                'user' => $user->load(['roles', 'teacher'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating faculty head: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating faculty head: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show user details
     */
    public function show($id)
    {
        try {
            $user = User::with(['roles', 'admin', 'teacher', 'guidance', 'discipline'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::with(['roles', 'admin', 'teacher', 'guidance', 'discipline'])->findOrFail($id);
            
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            ]);

            DB::beginTransaction();

            // Update user
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Update role-specific data
            if ($user->hasRole('admin') && $user->admin) {
                $user->admin->update([
                    'employee_id' => $request->employee_id,
                    'department' => $request->department,
                    'admin_level' => $request->admin_level,
                ]);
            } elseif ($user->hasRole('teacher') && $user->teacher) {
                $user->teacher->update([
                    'employee_id' => $request->employee_id,
                    'department' => $request->department,
                    'position' => $request->position,
                    'specialization' => $request->specialization,
                    'hire_date' => $request->hire_date,
                ]);
            } elseif ($user->hasRole('guidance') && $user->guidance) {
                $user->guidance->update([
                    'employee_id' => $request->employee_id,
                    'position' => $request->position,
                    'specialization' => $request->specialization,
                    'hire_date' => $request->hire_date,
                ]);
            } elseif ($user->hasRole('discipline') && $user->discipline) {
                $user->discipline->update([
                    'employee_id' => $request->employee_id,
                    'position' => $request->position,
                    'specialization' => $request->specialization,
                    'hire_date' => $request->hire_date,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!',
                'user' => $user->fresh(['roles', 'admin', 'teacher', 'guidance', 'discipline'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deletion of current user
            if ($user->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 400);
            }

            DB::beginTransaction();

            // Delete related records
            if ($user->admin) {
                $user->admin->delete();
            }
            if ($user->teacher) {
                $user->teacher->delete();
            }
            if ($user->guidance) {
                $user->guidance->delete();
            }
            if ($user->discipline) {
                $user->discipline->delete();
            }

            // Delete user
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user statistics
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'admins' => User::role('admin')->count(),
                'teachers' => User::role('teacher')->count(),
                'guidance' => User::role('guidance')->count(),
                'discipline' => User::role('discipline')->count(),
                'active_users' => User::where('status', 'active')->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics'
            ], 500);
        }
    }

    /**
     * Ensure a role exists, create it if it doesn't
     */
    private function ensureRoleExists($roleName)
    {
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            Role::create([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);
        }
        return $role;
    }
}
