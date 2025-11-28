<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserManagementController extends Controller
{
    public function __construct()
    {
        // Middleware is handled at route level
    }

    /**
     * Display the user management page (roles & permissions only)
     */
    public function index()
    {
        $users = User::with(['roles', 'admin', 'teacher', 'guidance', 'discipline'])->get();
        $roles = Role::with(['permissions', 'users'])->get();
        $permissions = Permission::with('roles')->get();
        
        return view('admin.user_management', compact('users', 'roles', 'permissions'));
    }
}
