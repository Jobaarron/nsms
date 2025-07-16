<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserRoleController extends Controller
{
    /**
     * Display a listing of users with their roles.
     */
    public function index()
    {
        $users = User::with('roles')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user with roles.
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user with roles.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'roles' => 'required|array',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $user->assignRole($request->input('roles'));

        return redirect()->route('users.index')
            ->with('success', 'User created successfully');
    }

    /**
     * Show the form for editing user roles.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update user roles.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'roles' => 'required|array',
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        
        if($request->input('password')) {
            $request->validate([
                'password' => 'min:8|confirmed',
            ]);
            $user->password = Hash::make($request->input('password'));
        }
        
        $user->save();
        
        $user->syncRoles($request->input('roles'));

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user.
     */
    public function destroy($id)
    {
        // Prevent deletion of admin user
        $user = User::findOrFail($id);
        
        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            return redirect()->route('users.index')
                ->with('error', 'Cannot delete the only admin user');
        }
        
        $user->delete();
        
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }
}
