<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminController extends Controller
{
        public function index()
    {
        return view('admin.index');
    }
    /**
     * Show admin login form
     */
    public function showLoginForm()
    {
        // If already logged in and is admin, redirect to dashboard
        if (Auth::check() && Auth::user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.login');
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
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Check if user has admin role
            if (Auth::user()->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            } else {
                Auth::logout();
                return back()->with('error', 'You do not have admin privileges.');
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
        
        return redirect()->route('admin.login');
    }
    
    /**
     * Show admin generator form
     */
    public function showGeneratorForm()
    {
        // Check if admin role exists
        $adminRoleExists = Role::where('name', 'admin')->exists();
        
        // Check if any admin users exist (only if the role exists)
        $adminExists = false;
        if ($adminRoleExists) {
            $adminExists = User::role('admin')->exists();
        }
        
        return view('admin.admin-generator', compact('adminRoleExists', 'adminExists'));
    }
    
    /**
     * Generate admin user
     */
    public function generateAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        // Check if admin role exists, if not create it
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            // Create admin role
            $adminRole = Role::create(['name' => 'admin']);
            
            // Create basic permissions
            $permissions = [
                'Dashboard',
                'Roles & Access',
                'Manage users',
                'Enrollments'
            ];
            
            foreach ($permissions as $permissionName) {
                $permission = Permission::firstOrCreate(['name' => $permissionName]);
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
