<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

trait AdminAuthentication
{
    protected function checkAdminAuth()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('admin.login')
                ->with('error', 'You must be logged in to access the admin area.');
        }

        $user = Auth::user();

        // Check if permission tables exist (same as your AdminMiddleware)
        if (!Schema::hasTable('roles') || !Schema::hasTable('model_has_roles')) {
            // Temporarily allow access if tables don't exist (development only)
            if (app()->environment('local', 'development')) {
                return null;
            }
            
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Permission system not set up'], 500);
            }
            return redirect()->route('show.admin.generator')
                ->with('error', 'System configuration error. Please contact the administrator.');
        }

        // Check if the admin role exists (same as your AdminMiddleware)
        $adminRoleExists = Role::where('name', 'admin')->exists();
        if (!$adminRoleExists) {
            // Temporarily allow access if admin role doesn't exist (development only)
            if (app()->environment('local', 'development')) {
                return null;
            }
            
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Admin role not found'], 500);
            }
            return redirect()->route('show.admin.generator')
                ->with('error', 'Admin role does not exist. Please set up the admin role first.');
        }

        // Check if user has admin role (same logic as your AdminMiddleware)
        try {
            if (!$user->hasRole('admin')) {
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'Insufficient privileges'], 403);
                }
                return redirect()->route('index')
                    ->with('error', 'You do not have permission to access the admin area.');
            }
        } catch (\Exception $e) {
            // Temporarily allow access if there's an error (development only)
            if (app()->environment('local', 'development')) {
                return null;
            }
            
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Permission check failed'], 500);
            }
            return redirect()->route('index')
                ->with('error', 'System error checking permissions. Please contact the administrator.');
        }

        return null; // No redirect needed, user is properly authenticated
    }
}
