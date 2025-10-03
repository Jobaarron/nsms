<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Registrar;
use Illuminate\Support\Facades\Hash;

class RegistrarSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create sample registrar
        $registrar = Registrar::create([
            'employee_id' => 'REG-25001',
            'first_name' => 'Maria',
            'middle_name' => 'Santos',
            'last_name' => 'Cruz',
            'email' => 'registrar@nicolites.edu',
            'password' => Hash::make('registrar123'),
            'contact_number' => '09123456789',
            'date_of_birth' => '1985-06-15',
            'gender' => 'female',
            'address' => '123 Main Street, Barangay Centro',
            'city' => 'Quezon City',
            'province' => 'Metro Manila',
            'zip_code' => '1100',
            'position' => 'Senior Registrar',
            'department' => 'Registrar Office',
            'hire_date' => '2020-08-01',
            'employment_status' => 'active',
            'qualifications' => 'Bachelor of Science in Education, Master in Educational Management',
            'emergency_contact_name' => 'Juan Cruz',
            'emergency_contact_phone' => '09987654321',
            'emergency_contact_relationship' => 'Spouse',
            'notes' => 'Experienced in student records management and enrollment processes.',
        ]);

        // Assign registrar role
        $registrar->assignRole('registrar');

        // Create additional registrar
        $assistantRegistrar = Registrar::create([
            'employee_id' => 'REG-25002',
            'first_name' => 'Ana',
            'middle_name' => 'Reyes',
            'last_name' => 'Garcia',
            'email' => 'assistant.registrar@nicolites.edu',
            'password' => Hash::make('assistant123'),
            'contact_number' => '09234567890',
            'date_of_birth' => '1990-03-22',
            'gender' => 'female',
            'address' => '456 Oak Avenue, Barangay San Antonio',
            'city' => 'Marikina City',
            'province' => 'Metro Manila',
            'zip_code' => '1800',
            'position' => 'Assistant Registrar',
            'department' => 'Registrar Office',
            'hire_date' => '2022-01-15',
            'employment_status' => 'active',
            'qualifications' => 'Bachelor of Arts in Psychology, Certificate in Records Management',
            'emergency_contact_name' => 'Rosa Garcia',
            'emergency_contact_phone' => '09876543210',
            'emergency_contact_relationship' => 'Mother',
            'notes' => 'Specializes in document verification and student credential processing.',
        ]);

        // Assign registrar role
        $assistantRegistrar->assignRole('registrar');
    }
}
