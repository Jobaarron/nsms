<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Admin;
// use App\Models\Teacher;
// use App\Models\GuidanceCounsellor;
// use App\Models\DisciplineOfficer;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions based on actual views/routes accessible in each layout sidebar
        $permissions = [
            // ADMIN LAYOUT PERMISSIONS (admin-layout.blade.php)
            'Dashboard',
            'Roles & Access',
            'Manage Users',
            'Manage Enrollments',
            'Contact Messages',
            
            // TEACHER LAYOUT PERMISSIONS (teacher-layout.blade.php)
            'Teacher Dashboard',
            'My Classes',
            'View Students',
            'Grade Book',
            'Attendance Management',
            'Teacher Messages',
            
            // GUIDANCE LAYOUT PERMISSIONS (guidance-layout.blade.php)
            'Guidance Dashboard',
            'Student Profiles',
            'Violations Management',
            'Facial Recognition',
            'Counseling Services',
            'Career Advice',
            'Guidance Analytics',
            'Guidance Settings',
            
            // STUDENT LAYOUT PERMISSIONS (student-layout.blade.php)
            'Student Dashboard',
            'View Violations',
            'Student Payments',
            'My Subjects',
            'Guidance Notes',
            'Student Profile',
            
            // ENROLLEE LAYOUT PERMISSIONS (enrollee-layout.blade.php)
            'Enrollee Dashboard',
            'My Application',
            'Documents Management',
            'Payment Portal',
            'Schedule View',
            
            // CORE SYSTEM PERMISSIONS (for AdminController compatibility)
            'View Reports',
            'View Analytics',
            'System Settings',
            'Manage Admins',
            'Database Management',
            'Backup & Restore',
            'Manage Roles',
        ];

        // Create permissions first
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Create roles and assign permissions
        
        // ADMIN ROLE - Based on admin-layout.blade.php sidebar navigation
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'Dashboard',
            'Roles & Access',
            'Manage Users',
            'Manage Enrollments',
            'Contact Messages',
            'View Reports',
            'View Analytics',
            'System Settings',
            'Manage Admins',
            'Database Management',
            'Backup & Restore',
            'Manage Roles',
        ]);

        // TEACHER ROLE - Based on teacher-layout.blade.php sidebar navigation  
        $teacher = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $teacher->syncPermissions([
            'Teacher Dashboard',
            'My Classes',
            'View Students',
            'Grade Book',
            'Attendance Management',
            'Teacher Messages',
        ]);

        // GUIDANCE ROLE - Based on guidance-layout.blade.php sidebar navigation
        $guidance = Role::firstOrCreate(['name' => 'guidance', 'guard_name' => 'web']);
        $guidance->syncPermissions([
            'Guidance Dashboard',
            'Student Profiles',
            'Violations Management',
            'Facial Recognition',
            'Counseling Services',
            'Career Advice',
            'Guidance Analytics',
            'Guidance Settings',
        ]);

        // DISCIPLINE ROLE - Based on guidance-layout.blade.php (shares same layout with guidance)
        $discipline = Role::firstOrCreate(['name' => 'discipline', 'guard_name' => 'web']);
        $discipline->syncPermissions([
            'Guidance Dashboard',
            'Student Profiles',
            'Violations Management',
            'Facial Recognition',
        ]);

        // Super Admin - Full system access including role management
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all()); // Give all permissions

        // STUDENT ROLE - Based on student-layout.blade.php sidebar navigation
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $student->syncPermissions([
            'Student Dashboard',
            'View Violations',
            'Student Payments',
            'My Subjects',
            'Guidance Notes',
            'Student Profile',
        ]);

        // ENROLLEE/APPLICANT ROLE - Based on enrollee-layout.blade.php sidebar navigation
        // $enrollee = Role::firstOrCreate(['name' => 'enrollee', 'guard_name' => 'web']);
        // $enrollee->syncPermissions([
        //     'Enrollee Dashboard',
        //     'My Application',
        //     'Documents Management',
        //     'Payment Portal',
        //     'Schedule View',
        // ]);

        // APPLICANT ROLE - Uniform naming for enrollees/applicants
        $applicant = Role::firstOrCreate(['name' => 'applicant', 'guard_name' => 'web']);
        $applicant->syncPermissions([
            'Enrollee Dashboard',
            'My Application',
            'Documents Management',
            'Payment Portal',
            'Schedule View',
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
        
        // Assign both admin and super_admin roles
        $adminUser->assignRole(['super_admin', 'admin']);

        // 2. Create Teacher User with Teacher model
        // $teacherUser = User::create([
        //     'name' => 'Sample Teacher',
        //     'email' => 'teacher@nicolites.edu',
        //     'password' => bcrypt('teacher123'),
        // ]);
        
        // Create Teacher record
        // Teacher::create([
        //     'user_id' => $teacherUser->id,
        //     'employee_id' => 'TCH001',
        //     'department' => 'Mathematics',
        //     'subject_specialization' => 'Algebra',
        //     'employment_type' => 'full_time',
        //     'teacher_level' => 'senior',
        //     'hire_date' => now()->subYears(3),
        //     'qualification' => 'Bachelor of Science in Mathematics',
        //     'years_experience' => 5,
        //     'subjects_taught' => ['Mathematics', 'Statistics'],
        //     'class_assignments' => ['Grade 10-A', 'Grade 11-B'],
        //     'is_active' => true,
        // ]);
        
        // $teacherUser->assignRole('teacher');

        // 3. Create Guidance Counsellor User with GuidanceCounsellor model
        // $guidanceUser = User::create([
        //     'name' => 'Guidance Counselor',
        //     'email' => 'guidance@nicolites.edu',
        //     'password' => bcrypt('guidance123'),
        // ]);
        
        // Create Guidance Counsellor record
        // GuidanceCounsellor::create([
        //     'user_id' => $guidanceUser->id,
        //     'employee_id' => 'GC001',
        //     'license_number' => 'GC-2024-001',
        //     'counsellor_level' => 'senior',
        //     'specializations' => ['academic', 'career', 'personal'],
        //     'grade_levels_assigned' => ['Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'],
        //     'office_location' => 'Guidance Office - Building A',
        //     'available_hours' => [
        //         'monday' => '08:00-17:00',
        //         'tuesday' => '08:00-17:00',
        //         'wednesday' => '08:00-17:00',
        //         'thursday' => '08:00-17:00',
        //         'friday' => '08:00-17:00'
        //     ],
        //     'max_students_per_day' => 15,
        //     'hire_date' => now()->subYears(2),
        //     'qualification' => 'Master of Arts in Guidance and Counseling',
        //     'certifications' => ['Crisis Intervention', 'Career Counseling'],
        //     'is_active' => true,
        // ]);
        
        // $guidanceUser->assignRole('guidance');

        // 4. Create Discipline Officer User with DisciplineOfficer model
        // $disciplineUser = User::create([
        //     'name' => 'Discipline Officer',
        //     'email' => 'discipline@nicolites.edu',
        //     'password' => bcrypt('discipline123'),
        // ]);
        
        // Create Discipline Officer record
        // DisciplineOfficer::create([
        //     'user_id' => $disciplineUser->id,
        //     'employee_id' => 'DO001',
        //     'officer_level' => 'senior_officer',
        //     'areas_of_responsibility' => ['attendance', 'behavior', 'uniform', 'safety'],
        //     'grade_levels_assigned' => ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'],
        //     'office_location' => 'Discipline Office - Building B',
        //     'patrol_schedule' => [
        //         'morning' => '07:00-08:00',
        //         'break' => '10:00-10:30',
        //         'lunch' => '12:00-13:00',
        //         'afternoon' => '15:00-17:00'
        //     ],
        //     'can_suspend' => true,
        //     'can_expel' => false,
        //     'hire_date' => now()->subYears(4),
        //     'qualification' => 'Bachelor of Arts in Psychology',
        //     'training_certifications' => ['Conflict Resolution', 'Student Safety Management'],
        //     'is_active' => true,
        // ]);
        
        // $disciplineUser->assignRole('discipline');
    }
}
