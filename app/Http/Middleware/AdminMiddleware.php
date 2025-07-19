<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in
        if (!Auth::check()) {
            // Log unauthorized access attempt
            Log::warning('Unauthorized admin access attempt', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            
            // Handle AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to access the admin area.',
                    'redirect' => route('admin.login')
                ], 401);
            }
            
            return redirect()->route('admin.login')
                ->with('error', 'You must be logged in to access the admin area.');
        }
        
        $user = Auth::user();
        
        // Check if the required tables exist
        if (!Schema::hasTable('roles') || !Schema::hasTable('model_has_roles')) {
            Log::error('Required permission tables do not exist', [
                'user_id' => $user->id,
                'tables_missing' => [
                    'roles' => !Schema::hasTable('roles'),
                    'model_has_roles' => !Schema::hasTable('model_has_roles')
                ]
            ]);
            
            // Temporarily allow access if tables don't exist (development only)
            if (app()->environment('local', 'development')) {
                return $next($request);
            }
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'System configuration error. Please contact the administrator.',
                    'redirect' => route('index')
                ], 500);
            }
            
            return redirect()->route('index')
                ->with('error', 'System configuration error. Please contact the administrator.');
        }
        
        // Check if the admin role exists
        $adminRoleExists = Role::where('name', 'admin')->exists();
        if (!$adminRoleExists) {
            Log::error('Admin role does not exist', [
                'user_id' => $user->id
            ]);
            
            // Temporarily allow access if admin role doesn't exist (development only)
            if (app()->environment('local', 'development')) {
                return $next($request);
            }
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin role does not exist. Please set up the admin role first.',
                    'redirect' => route('show.admin.generator')
                ], 500);
            }
            
            return redirect()->route('show.admin.generator')
                ->with('error', 'Admin role does not exist. Please set up the admin role first.');
        }
        
        // Check if user has admin role
        try {
            if (!$user->hasRole('admin')) {
                Log::warning('Non-admin user attempted to access admin area', [
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);
                
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to access the admin area.',
                        'redirect' => route('index')
                    ], 403);
                }
                
                return redirect()->route('index')
                    ->with('error', 'You do not have permission to access the admin area.');
            }
        } catch (\Exception $e) {
            Log::error('Error checking admin role', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            // Temporarily allow access if there's an error (development only)
            if (app()->environment('local', 'development')) {
                return $next($request);
            }
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'System error checking permissions. Please contact the administrator.',
                    'redirect' => route('index')
                ], 500);
            }
            
            return redirect()->route('index')
                ->with('error', 'System error checking permissions. Please contact the administrator.');
        }
        
        return $next($request);
    }
}
