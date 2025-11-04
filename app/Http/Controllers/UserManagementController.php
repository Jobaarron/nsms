<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Enrollee;
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
}
