<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleAccessController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'permissions'])->get();
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        
        return view('admin.roles_access', compact('users', 'roles', 'permissions'));
    }

    // User Role/Permission Management
    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->assignRole($request->role);

        return response()->json([
            'success' => true,
            'message' => "Role '{$request->role}' assigned to {$user->name} successfully!"
        ]);
    }

    public function removeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->removeRole($request->role);

        return response()->json([
            'success' => true,
            'message' => "Role '{$request->role}' removed from {$user->name} successfully!"
        ]);
    }

    public function assignPermission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|exists:permissions,name'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->givePermissionTo($request->permission);

        return response()->json([
            'success' => true,
            'message' => "Permission '{$request->permission}' assigned to {$user->name} successfully!"
        ]);
    }

    public function removePermission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|string'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->revokePermissionTo($request->permission);

        return response()->json([
            'success' => true,
            'message' => "Permission '{$request->permission}' removed from {$user->name} successfully!"
        ]);
    }

    // Role Management
    public function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array'
        ]);

        $role = Role::create(['name' => $request->name]);
        
        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'success' => true,
            'message' => "Role '{$request->name}' created successfully!"
        ]);
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'permissions' => 'array'
        ]);

        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions ?? []);
        }

        return response()->json([
            'success' => true,
            'message' => "Role '{$request->name}' updated successfully!"
        ]);
    }

    public function deleteRole($id)
    {
        $protectedRoles = ['admin', 'teacher', 'student', 'guidance', 'discipline'];
        $role = Role::findOrFail($id);

        if (in_array($role->name, $protectedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete essential system roles'
            ], 400);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => "Role '{$role->name}' deleted successfully!"
        ]);
    }

    // Permission Management
    public function createPermission(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name'
        ]);

        Permission::create(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => "Permission '{$request->name}' created successfully!"
        ]);
    }

    public function updatePermission(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $id
        ]);

        $permission = Permission::findOrFail($id);
        $permission->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => "Permission '{$request->name}' updated successfully!"
        ]);
    }

    public function deletePermission($id)
    {
        $systemPermissions = [
            'Dashboard', 'Manage Users', 'Manage Enrollments', 'Manage Students',
            'View Reports', 'Roles & Access', 'System Settings'
        ];

        $permission = Permission::findOrFail($id);

        if (in_array($permission->name, $systemPermissions)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete essential system permissions'
            ], 400);
        }

        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => "Permission '{$permission->name}' deleted successfully!"
        ]);
    }

    // User Management
    public function createUser(Request $request)
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

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // Prevent deletion of admin user if it's the only one
        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the only admin user'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => "User '{$user->name}' deleted successfully!"
        ]);
    }
}
