<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Student;

class TestStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test student with the format used by pre-registration
        Student::create([
            'student_id' => 'NS-25001',
            'password' => Hash::make('25-001'), // Password format: 25-001
            'first_name' => 'John',
            'middle_name' => 'Doe',
            'last_name' => 'Smith',
            'suffix' => null,
            'full_name' => 'John Doe Smith',
            'date_of_birth' => '2005-01-15',
            'place_of_birth' => 'Manila',
            'gender' => 'male',
            'nationality' => 'Filipino',
            'religion' => 'Catholic',
            'contact_number' => '09123456789',
            'email' => 'john.smith@example.com',
            'address' => '123 Test Street, Test City',
            'city' => 'Test City',
            'province' => 'Test Province',
            'zip_code' => '1234',
            'grade_level' => 'Grade 11',
            'strand' => 'STEM',
            'track' => 'Academic',
            'section' => 'A',
            'student_type' => 'new',
            'enrollment_status' => 'pre_registered',
            'academic_year' => '2024-2025',
            'father_name' => 'Robert Smith',
            'father_contact' => '09111111111',
            'mother_name' => 'Mary Smith',
            'mother_contact' => '09222222222',
            'guardian_name' => 'Robert Smith',
            'guardian_contact' => '09111111111',
            'last_school_type' => 'public',
            'last_school_name' => 'Test Elementary School',
            'pre_registered_at' => now(),
            'is_active' => true
        ]);

        // Create another test student
        Student::create([
            'student_id' => 'NS-25002',
            'password' => Hash::make('25-002'), // Password format: 25-002
            'first_name' => 'Jane',
            'middle_name' => 'Marie',
            'last_name' => 'Doe',
            'suffix' => null,
            'full_name' => 'Jane Marie Doe',
            'date_of_birth' => '2005-03-20',
            'place_of_birth' => 'Quezon City',
            'gender' => 'female',
            'nationality' => 'Filipino',
            'religion' => 'Catholic',
            'contact_number' => '09987654321',
            'email' => 'jane.doe@example.com',
            'address' => '456 Sample Avenue, Sample City',
            'city' => 'Sample City',
            'province' => 'Sample Province',
            'zip_code' => '5678',
            'grade_level' => 'Grade 12',
            'strand' => 'ABM',
            'track' => 'Academic',
            'section' => 'B',
            'student_type' => 'new',
            'enrollment_status' => 'pre_registered',
            'academic_year' => '2024-2025',
            'father_name' => 'Michael Doe',
            'father_contact' => '09333333333',
            'mother_name' => 'Sarah Doe',
            'mother_contact' => '09444444444',
            'guardian_name' => 'Michael Doe',
            'guardian_contact' => '09333333333',
            'last_school_type' => 'private',
            'last_school_name' => 'Sample High School',
            'pre_registered_at' => now(),
            'is_active' => true
        ]);
    }
}
