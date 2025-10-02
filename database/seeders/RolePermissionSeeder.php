<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Admin;
use App\Models\Teacher;
use App\Models\GuidanceDiscipline;

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
            
            // REGISTRAR LAYOUT PERMISSIONS (registrar-layout.blade.php)
            'Registrar Dashboard',
            'Applications',
            'Approved',
            'Reports',
            
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

        // GUIDANCE ROLE - REMOVED (duplicate of guidance_counselor)
        // Generic guidance role removed in favor of specific guidance_counselor role

        // GUIDANCE COUNSELOR ROLE - Specialized guidance role
        $guidanceCounselor = Role::firstOrCreate(['name' => 'guidance_counselor', 'guard_name' => 'web']);
        $guidanceCounselor->syncPermissions([
            'Guidance Dashboard',
            'Student Profiles',
            'Counseling Services',
            'Career Advice',
            'Guidance Analytics',
            'Guidance Settings',
        ]);

        // DISCIPLINE HEAD ROLE - Senior discipline management
        $disciplineHead = Role::firstOrCreate(['name' => 'discipline_head', 'guard_name' => 'web']);
        $disciplineHead->syncPermissions([
            'Guidance Dashboard',
            'Student Profiles',
            'Violations Management',
            'Facial Recognition',
            'Guidance Analytics',
            'Guidance Settings',
        ]);

        // DISCIPLINE OFFICER ROLE - Basic discipline functions
        $disciplineOfficer = Role::firstOrCreate(['name' => 'discipline_officer', 'guard_name' => 'web']);
        $disciplineOfficer->syncPermissions([
            'Guidance Dashboard',
            'Student Profiles',
            'Violations Management',
            'Facial Recognition',
        ]);

        // CASHIER ROLE - Financial transactions
        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashier->syncPermissions([
            'Dashboard',
            'Student Payments',
            'Manage Enrollments',
        ]);

        // FACULTY HEAD ROLE - Academic leadership
        $facultyHead = Role::firstOrCreate(['name' => 'faculty_head', 'guard_name' => 'web']);
        $facultyHead->syncPermissions([
            'Teacher Dashboard',
            'My Classes',
            'View Students',
            'Grade Book',
            'Attendance Management',
            'Teacher Messages',
            'Guidance Analytics',
        ]);

        // REGISTRAR ROLE - Based on registrar-layout.blade.php sidebar navigation
        $registrar = Role::firstOrCreate(['name' => 'registrar', 'guard_name' => 'web']);
        $registrar->syncPermissions([
            'Dashboard',
            'Applications',
            'Approved',
            'Reports',
            'View Reports',
            'Manage Enrollments',
        ]);

        // DISCIPLINE ROLE - Keep for backward compatibility
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
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@nicolites.edu'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('admin123'),
            ]
        );
        
        // Create Admin record
        Admin::firstOrCreate(
            ['user_id' => $adminUser->id],
            [
                'employee_id' => 'ADM001',
                'department' => 'Administration',
                'position' => 'System Administrator',
                'admin_level' => 'super_admin',
                'is_active' => true,
            ]
        );
        
        // Assign both admin and super_admin roles
        $adminUser->assignRole(['super_admin', 'admin']);

        // 2. Create Guidance Counselor User
        $guidanceUser = User::firstOrCreate(
            ['email' => 'guidance@nicolites.edu'],
            [
                'name' => 'Maria Santos',
                'password' => bcrypt('guidance123'),
            ]
        );
        
        // Create GuidanceDiscipline record for Guidance Counselor
        GuidanceDiscipline::firstOrCreate(
            ['user_id' => $guidanceUser->id],
            [
                'employee_id' => 'GDC001',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'phone_number' => '09123456789',
                'address' => '123 Guidance Street, Quezon City',
                'position' => 'Guidance Counselor',
                'specialization' => 'Educational Psychology',
                'type' => 'guidance',
                'hire_date' => '2023-01-15',
                'qualifications' => 'Master of Arts in Guidance and Counseling',
                'emergency_contact_name' => 'Juan Santos',
                'emergency_contact_phone' => '09987654321',
                'emergency_contact_relationship' => 'spouse',
                'notes' => 'Specializes in academic and career counseling',
                'department' => 'guidance',
            ]
        );
        
        // Assign guidance counselor role
        $guidanceUser->assignRole('guidance_counselor');

        // 3. Create Discipline Head User
        $disciplineHeadUser = User::firstOrCreate(
            ['email' => 'discipline.head@nicolites.edu'],
            [
                'name' => 'Roberto Cruz',
                'password' => bcrypt('discipline123'),
            ]
        );
        
        // Create GuidanceDiscipline record for Discipline Head
        GuidanceDiscipline::firstOrCreate(
            ['user_id' => $disciplineHeadUser->id],
            [
                'employee_id' => 'DH001',
                'first_name' => 'Roberto',
                'last_name' => 'Cruz',
                'phone_number' => '09234567890',
                'address' => '456 Discipline Avenue, Manila',
                'position' => 'Discipline Head',
                'specialization' => 'Student Discipline Management',
                'type' => 'discipline',
                'hire_date' => '2022-06-01',
                'qualifications' => 'Bachelor of Science in Education, Discipline Management Certificate',
                'emergency_contact_name' => 'Ana Cruz',
                'emergency_contact_phone' => '09876543210',
                'emergency_contact_relationship' => 'spouse',
                'notes' => 'Head of Student Discipline Department',
                'department' => 'discipline',
            ]
        );
        
        // Assign discipline head role
        $disciplineHeadUser->assignRole('discipline_head');

        // 4. Create Discipline Officer User
        $disciplineOfficerUser = User::firstOrCreate(
            ['email' => 'discipline.officer@nicolites.edu'],
            [
                'name' => 'Carlos Mendoza',
                'password' => bcrypt('officer123'),
            ]
        );
        
        // Create GuidanceDiscipline record for Discipline Officer
        GuidanceDiscipline::firstOrCreate(
            ['user_id' => $disciplineOfficerUser->id],
            [
                'employee_id' => 'DO001',
                'first_name' => 'Carlos',
                'last_name' => 'Mendoza',
                'phone_number' => '09345678901',
                'address' => '789 Officer Lane, Pasig City',
                'position' => 'Discipline Officer',
                'specialization' => 'Student Behavior Management',
                'type' => 'discipline',
                'hire_date' => '2023-08-15',
                'qualifications' => 'Bachelor of Arts in Psychology',
                'emergency_contact_name' => 'Lisa Mendoza',
                'emergency_contact_phone' => '09765432109',
                'emergency_contact_relationship' => 'sibling',
                'notes' => 'Handles student violations and disciplinary actions',
                'department' => 'discipline',
            ]
        );
        
        // Assign discipline officer role
        $disciplineOfficerUser->assignRole('discipline_officer');

        // 5. Create Teacher User
        $teacherUser = User::firstOrCreate(
            ['email' => 'teacher@nicolites.edu'],
            [
                'name' => 'Jennifer Reyes',
                'password' => bcrypt('teacher123'),
            ]
        );
        
        // Create Teacher record
        Teacher::firstOrCreate(
            ['user_id' => $teacherUser->id],
            [
                'employee_id' => 'TCH001',
                'department' => 'Mathematics Department',
                'position' => 'Senior High School Teacher',
                'specialization' => 'Mathematics and Statistics',
                'hire_date' => '2021-03-10',
                'phone_number' => '09456789012',
                'address' => '321 Teacher Street, Makati City',
                'qualifications' => 'Bachelor of Science in Mathematics Education, Master of Arts in Teaching Mathematics',
                'is_active' => true,
            ]
        );
        
        // Assign teacher role
        $teacherUser->assignRole('teacher');

        // 6. Create Registrar User for Testing
        $registrarUser = \App\Models\Registrar::firstOrCreate(
            ['email' => 'registrar@nicolites.edu'],
            [
                'employee_id' => 'REG-25001',
                'first_name' => 'Maria',
                'middle_name' => 'Santos',
                'last_name' => 'Cruz',
                'suffix' => null,
                'email' => 'registrar@nicolites.edu',
                'password' => bcrypt('registrar123'),
                'contact_number' => '09123456789',
                'date_of_birth' => '1985-06-15',
                'gender' => 'female',
                'address' => '123 Registrar Avenue, Quezon City',
                'city' => 'Quezon City',
                'province' => 'Metro Manila',
                'zip_code' => '1100',
                'position' => 'Senior Registrar',
                'department' => 'Registrar Office',
                'hire_date' => '2020-01-15',
                'employment_status' => 'active',
                'qualifications' => 'Bachelor of Science in Education, Master in Educational Management',
                'emergency_contact_name' => 'Juan Cruz',
                'emergency_contact_phone' => '09987654321',
                'emergency_contact_relationship' => 'Spouse',
                'notes' => 'Senior registrar with 5+ years experience',
            ]
        );
        
        // Assign registrar role (using web guard since Registrar model defaults to web guard)
        $registrarUser->assignRole('registrar');

        // Create Assistant Registrar for additional testing
        $assistantRegistrarUser = \App\Models\Registrar::firstOrCreate(
            ['email' => 'assistant.registrar@nicolites.edu'],
            [
                'employee_id' => 'REG-25002',
                'first_name' => 'Ana',
                'middle_name' => 'Dela',
                'last_name' => 'Rosa',
                'suffix' => null,
                'email' => 'assistant.registrar@nicolites.edu',
                'password' => bcrypt('assistant123'),
                'contact_number' => '09234567890',
                'date_of_birth' => '1990-03-20',
                'gender' => 'female',
                'address' => '456 Assistant Street, Manila City',
                'city' => 'Manila',
                'province' => 'Metro Manila',
                'zip_code' => '1000',
                'position' => 'Assistant Registrar',
                'department' => 'Registrar Office',
                'hire_date' => '2022-08-01',
                'employment_status' => 'active',
                'qualifications' => 'Bachelor of Science in Information Systems',
                'emergency_contact_name' => 'Pedro Dela Rosa',
                'emergency_contact_phone' => '09876543210',
                'emergency_contact_relationship' => 'Father',
                'notes' => 'Assistant registrar handling document processing',
            ]
        );
        
        // Assign registrar role (using web guard since Registrar model defaults to web guard)
        $assistantRegistrarUser->assignRole('registrar');
        
    }
}
