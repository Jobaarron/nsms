<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create faculty_head role and permissions using Spatie Permission
        // This integrates with the existing role system
        
        // Create faculty head role
        $facultyHeadRole = Role::firstOrCreate(['name' => 'faculty_head', 'guard_name' => 'web']);
        
        // Create faculty head specific permissions
        $permissions = [
            'assign_teachers',
            'manage_class_assignments', 
            'review_grade_submissions',
            'approve_grades',
            'reject_grades',
            'view_all_grades',
            'manage_class_schedules',
            'view_teacher_reports'
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        
        // Assign permissions to faculty head role
        $facultyHeadRole->givePermissionTo($permissions);
        
        // Also give faculty heads all teacher permissions
        $teacherRole = Role::where('name', 'teacher')->first();
        if ($teacherRole) {
            $teacherPermissions = $teacherRole->permissions->pluck('name')->toArray();
            $facultyHeadRole->givePermissionTo($teacherPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove faculty head role and permissions
        $facultyHeadRole = Role::where('name', 'faculty_head')->first();
        if ($facultyHeadRole) {
            $facultyHeadRole->delete();
        }
        
        // Remove faculty head specific permissions
        $permissions = [
            'assign_teachers',
            'manage_class_assignments',
            'review_grade_submissions', 
            'approve_grades',
            'reject_grades',
            'view_all_grades',
            'manage_class_schedules',
            'view_teacher_reports'
        ];
        
        foreach ($permissions as $permission) {
            $perm = Permission::where('name', $permission)->first();
            if ($perm) {
                $perm->delete();
            }
        }
    }
};
