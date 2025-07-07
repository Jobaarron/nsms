<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for school management system
        $permissions = [
            // Student Management
            'view students',
            'create students',
            'edit students',
            'delete students',
            'approve student enrollment',
            'reject student enrollment',
            
            // Teacher Management
            'view teachers',
            'create teachers',
            'edit teachers',
            'delete teachers',
            'assign teacher subjects',
            
            // Academic Management
            'view grades',
            'create grades',
            'edit grades',
            'delete grades',
            'view class schedules',
            'manage class schedules',
            
            // Enrollment Management
            'view enrollments',
            'process enrollments',
            'approve enrollments',
            'reject enrollments',
            
            // Financial Management
            'view payments',
            'process payments',
            'update payment status',
            'generate financial reports',
            
            // Guidance Services
            'view student counseling records',
            'create counseling records',
            'edit counseling records',
            'schedule counseling sessions',
            'view guidance reports',
            'create guidance reports',
            
            // Discipline Management
            'view disciplinary records',
            'create disciplinary records',
            'edit disciplinary records',
            'delete disciplinary records',
            'issue disciplinary actions',
            'view discipline reports',
            
            // System Administration
            'manage users',
            'manage roles',
            'view system settings',
            'edit system settings',
            'backup system',
            'view audit logs',
            
            // Reports & Analytics
            'view reports',
            'generate reports',
            'export reports',
            'view analytics dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Admin - Full system access
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            // Student Management
            'view students', 'create students', 'edit students', 'delete students',
            'approve student enrollment', 'reject student enrollment',
            
            // Teacher Management
            'view teachers', 'create teachers', 'edit teachers', 'delete teachers',
            'assign teacher subjects',
            
            // Academic Management
            'view grades', 'create grades', 'edit grades', 'delete grades',
            'view class schedules', 'manage class schedules',
            
            // Enrollment Management
            'view enrollments', 'process enrollments', 'approve enrollments', 'reject enrollments',
            
            // Financial Management
            'view payments', 'process payments', 'update payment status', 'generate financial reports',
            
            // System Administration
            'manage users', 'manage roles', 'view system settings', 'edit system settings',
            'backup system', 'view audit logs',
            
            // Reports & Analytics
            'view reports', 'generate reports', 'export reports', 'view analytics dashboard',
        ]);

        // Teacher - Academic focused permissions
        $teacher = Role::create(['name' => 'teacher']);
        $teacher->givePermissionTo([
            'view students',
            'view grades', 'create grades', 'edit grades',
            'view class schedules',
            'view enrollments',
            'view reports',
        ]);

        // Guidance - Student counseling and welfare
        $guidance = Role::create(['name' => 'guidance']);
        $guidance->givePermissionTo([
            'view students', 'edit students',
            'view student counseling records', 'create counseling records', 'edit counseling records',
            'schedule counseling sessions', 'view guidance reports', 'create guidance reports',
            'view enrollments',
            'view reports', 'generate reports',
        ]);

        // Discipline - Student behavior and disciplinary actions
        $discipline = Role::create(['name' => 'discipline']);
        $discipline->givePermissionTo([
            'view students', 'edit students',
            'view disciplinary records', 'create disciplinary records', 'edit disciplinary records',
            'delete disciplinary records', 'issue disciplinary actions', 'view discipline reports',
            'view enrollments',
            'view reports', 'generate reports',
        ]);

        // Student - Limited self-service access
        $student = Role::create(['name' => 'student']);
        $student->givePermissionTo([
            'view grades', // Only their own grades
            'view class schedules', // Only their own schedule
            'view payments', // Only their own payment status
        ]);

        // Create default admin user
        $adminUser = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@nicolites.edu',
            'password' => bcrypt('admin123'),
        ]);
        $adminUser->assignRole('admin');

        // Create sample users for each role
        $teacherUser = User::create([
            'name' => 'Sample Teacher',
            'email' => 'teacher@nicolites.edu',
            'password' => bcrypt('teacher123'),
        ]);
        $teacherUser->assignRole('teacher');

        $guidanceUser = User::create([
            'name' => 'Guidance Counselor',
            'email' => 'guidance@nicolites.edu',
            'password' => bcrypt('guidance123'),
        ]);
        $guidanceUser->assignRole('guidance');

        $disciplineUser = User::create([
            'name' => 'Discipline Officer',
            'email' => 'discipline@nicolites.edu',
            'password' => bcrypt('discipline123'),
        ]);
        $disciplineUser->assignRole('discipline');
    }
}
