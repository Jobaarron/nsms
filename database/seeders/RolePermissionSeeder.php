<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Admin;
use App\Models\Teacher;
use App\Models\GuidanceCounsellor;
use App\Models\DisciplineOfficer;

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

        // CREATE SAMPLE USERS WITH PROPER MODELS
        
        // 1. Create Admin User with Admin model
        $adminUser = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@nicolites.edu',
            'password' => bcrypt('admin123'),
        ]);
        
        // Create Admin record
        Admin::create([
            'user_id' => $adminUser->id,
            'employee_id' => 'ADM001',
            'department' => 'Administration',
            'position' => 'System Administrator',
            'admin_level' => 'super_admin',
            'is_active' => true,
        ]);
        
        $adminUser->assignRole('admin');

        // 2. Create Teacher User with Teacher model
        $teacherUser = User::create([
            'name' => 'Sample Teacher',
            'email' => 'teacher@nicolites.edu',
            'password' => bcrypt('teacher123'),
        ]);
        
        // Create Teacher record
        Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'TCH001',
            'department' => 'Mathematics',
            'subject_specialization' => 'Algebra',
            'employment_type' => 'full_time',
            'teacher_level' => 'senior',
            'hire_date' => now()->subYears(3),
            'qualification' => 'Bachelor of Science in Mathematics',
            'years_experience' => 5,
            'subjects_taught' => ['Mathematics', 'Statistics'],
            'class_assignments' => ['Grade 10-A', 'Grade 11-B'],
            'is_active' => true,
        ]);
        
        $teacherUser->assignRole('teacher');

        // 3. Create Guidance Counsellor User with GuidanceCounsellor model
        $guidanceUser = User::create([
            'name' => 'Guidance Counselor',
            'email' => 'guidance@nicolites.edu',
            'password' => bcrypt('guidance123'),
        ]);
        
        // Create Guidance Counsellor record
        GuidanceCounsellor::create([
            'user_id' => $guidanceUser->id,
            'employee_id' => 'GC001',
            'license_number' => 'GC-2024-001',
            'counsellor_level' => 'senior',
            'specializations' => ['academic', 'career', 'personal'],
            'grade_levels_assigned' => ['Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'],
            'office_location' => 'Guidance Office - Building A',
            'available_hours' => [
                'monday' => '08:00-17:00',
                'tuesday' => '08:00-17:00',
                'wednesday' => '08:00-17:00',
                'thursday' => '08:00-17:00',
                'friday' => '08:00-17:00'
            ],
            'max_students_per_day' => 15,
            'hire_date' => now()->subYears(2),
            'qualification' => 'Master of Arts in Guidance and Counseling',
            'certifications' => ['Crisis Intervention', 'Career Counseling'],
            'is_active' => true,
        ]);
        
        $guidanceUser->assignRole('guidance');

        // 4. Create Discipline Officer User with DisciplineOfficer model
        $disciplineUser = User::create([
            'name' => 'Discipline Officer',
            'email' => 'discipline@nicolites.edu',
            'password' => bcrypt('discipline123'),
        ]);
        
        // Create Discipline Officer record
        DisciplineOfficer::create([
            'user_id' => $disciplineUser->id,
            'employee_id' => 'DO001',
            'officer_level' => 'senior_officer',
            'areas_of_responsibility' => ['attendance', 'behavior', 'uniform', 'safety'],
            'grade_levels_assigned' => ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'],
            'office_location' => 'Discipline Office - Building B',
            'patrol_schedule' => [
                'morning' => '07:00-08:00',
                'break' => '10:00-10:30',
                'lunch' => '12:00-13:00',
                'afternoon' => '15:00-17:00'
            ],
            'can_suspend' => true,
            'can_expel' => false,
            'hire_date' => now()->subYears(4),
            'qualification' => 'Bachelor of Arts in Psychology',
            'training_certifications' => ['Conflict Resolution', 'Student Safety Management'],
            'is_active' => true,
        ]);
        
        $disciplineUser->assignRole('discipline');
    }
}
