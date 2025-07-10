<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminGeneratorController extends Controller
{
    public function showForm()
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
    
    public function generateAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        // Check if admin role exists, if not create it
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        
        if (!$adminRole) {
            // Create admin role with all permissions
            $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
            
            
           // Create basic permissions if they don't exist
            // $permissions = [
            //     'Dashboard',
            //     'Roles & Access',
            //     'Manage users',
            //     'Enrollments',
            //     'view students',
            //     'create students',
            //     'edit students',
            //     'delete students',
            //     'view teachers',
            //     'create teachers',
            //     'edit teachers',
            //     'delete teachers'
            // Backup for now ];

            $permissions = [
                'Dashboard',
                'Roles & Access',
                'Manage users',
                'Enrollments'
               
            ];
            
            foreach ($permissions as $permissionName) {
                $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
                $adminRole->givePermissionTo($permission);
            }
        }
        
        // Create admin user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        // Assign admin role
        $user->assignRole('admin');
        
        return redirect()->back()->with('success', 'Admin user created successfully! You can now log in with these credentials.');
    }
}
