<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cashier;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CashierSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create cashier permissions first
        $cashierPermissions = [
            'Cashier Dashboard',
            'Pending Payments',
            'Due Payments', 
            'Completed Payments',
            'Payment History',
            'Payment Reports',
            'Confirm Payments',
            'Reject Payments',
            'View Payment Details',
            'Process Payments',
            'Generate Payment Reports',
            'Export Payment Data',
            'View Reports',
        ];

        foreach ($cashierPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'cashier'
            ]);
        }

        // Create cashier role if it doesn't exist
        $cashierRole = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'cashier']);
        $cashierRole->syncPermissions($cashierPermissions);

        // Create main cashier
        $cashier = Cashier::firstOrCreate(
            ['email' => 'cashier@nicolites.edu'],
            [
                'employee_id' => 'CASH001',
                'first_name' => 'Maria',
                'middle_name' => 'Santos',
                'last_name' => 'Dela Cruz',
                'suffix' => null,
                'email' => 'cashier@nicolites.edu',
                'password' => Hash::make('cashier123'),
                'phone_number' => '09123456789',
                'address' => '123 Finance Street, Quezon City',
                'city' => 'Quezon City',
                'province' => 'Metro Manila',
                'zip_code' => '1100',
                'date_of_birth' => '1988-04-15',
                'gender' => 'female',
                'position' => 'Senior Cashier',
                'department' => 'Finance',
                'hire_date' => '2021-02-01',
                'salary' => 25000.00,
                'employment_status' => 'active',
                'emergency_contact_name' => 'Juan Dela Cruz',
                'emergency_contact_phone' => '09987654321',
                'emergency_contact_relationship' => 'spouse',
                'qualifications' => 'Bachelor of Science in Accounting, Certified Bookkeeper',
                'notes' => 'Senior cashier with 3+ years experience in school finance',
                'is_active' => true,
            ]
        );

        // Assign cashier role (using web guard)
        $cashier->assignRole('cashier');

        // Create assistant cashier
        $assistantCashier = Cashier::firstOrCreate(
            ['email' => 'assistant.cashier@nicolites.edu'],
            [
                'employee_id' => 'CASH002',
                'first_name' => 'Ana',
                'middle_name' => 'Reyes',
                'last_name' => 'Garcia',
                'suffix' => null,
                'email' => 'assistant.cashier@nicolites.edu',
                'password' => Hash::make('assistant123'),
                'phone_number' => '09234567890',
                'address' => '456 Payment Avenue, Manila City',
                'city' => 'Manila',
                'province' => 'Metro Manila',
                'zip_code' => '1000',
                'date_of_birth' => '1992-08-20',
                'gender' => 'female',
                'position' => 'Assistant Cashier',
                'department' => 'Finance',
                'hire_date' => '2023-06-15',
                'salary' => 20000.00,
                'employment_status' => 'active',
                'emergency_contact_name' => 'Pedro Garcia',
                'emergency_contact_phone' => '09876543210',
                'emergency_contact_relationship' => 'father',
                'qualifications' => 'Bachelor of Science in Business Administration',
                'notes' => 'Assistant cashier handling daily transactions',
                'is_active' => true,
            ]
        );

        // Assign cashier role (using web guard)
        $assistantCashier->assignRole('cashier');

        $this->command->info('Cashier users created successfully!');
        $this->command->info('cashier@nicolites.edu / cashier123');
        $this->command->info('assistant.cashier@nicolites.edu / assistant123');
    }
}
