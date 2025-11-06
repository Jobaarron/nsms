<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateTestPaymentData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:test-payment-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test payment data for all payment methods';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Creating Test Payment Data');
        $this->newLine();

        // Clean up existing test data
        $this->info('🧹 Cleaning up existing test data...');
        DB::table('students')->where('student_id', 'like', 'TEST-%')->delete();
        DB::table('payments')->where('transaction_id', 'like', 'TXN-TEST-%')->delete();
        $this->info('✅ Test data cleaned up');
        $this->newLine();

        // Create test students and payments using raw SQL to bypass validation
        $this->createFullPaymentStudent();
        $this->createQuarterlyPaymentStudent();
        $this->createMonthlyPaymentStudent();

        $this->info('🎉 Test data created successfully!');
        $this->info('Now you can test the cashier interface with these students:');
        $this->line('- TEST-FULL-001 (Full Payment)');
        $this->line('- TEST-QUARTERLY-001 (Quarterly Payment)');
        $this->line('- TEST-MONTHLY-001 (Monthly Payment)');

        return Command::SUCCESS;
    }

    private function createFullPaymentStudent()
    {
        $this->info('=== Creating Full Payment Test Student ===');

        // Insert student
        $studentId = DB::table('students')->insertGetId([
            'student_id' => 'TEST-FULL-001',
            'first_name' => 'Test',
            'last_name' => 'Full Payment',
            'email' => 'testfull001@test.com',
            'date_of_birth' => '2005-01-01',
            'address' => 'Test Address',
            'contact_number' => '09123456789',
            'gender' => 'Male',
            'guardian_name' => 'Test Guardian',
            'guardian_contact' => '09123456789',
            'grade_level' => 'Grade 11',
            'strand' => 'STEM',
            'enrollment_status' => 'pre_registered',
            'is_paid' => false,
            'total_fees_due' => 21500,
            'academic_year' => '2025-2026',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insert full payment (due tomorrow - should be highlighted as due soon)
        DB::table('payments')->insert([
            'transaction_id' => 'TXN-TEST-FULL-001',
            'payable_type' => 'App\\Models\\Student',
            'payable_id' => $studentId,
            'amount' => 21500,
            'scheduled_date' => now()->addDay()->format('Y-m-d'),
            'period_name' => 'Full Payment',
            'payment_method' => 'full',
            'status' => 'pending',
            'confirmation_status' => 'pending',
            'notes' => 'Test payment schedule',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->line('✅ Created TEST-FULL-001 with 1 payment (₱21,500)');
    }

    private function createQuarterlyPaymentStudent()
    {
        $this->info('=== Creating Quarterly Payment Test Student ===');

        // Insert student
        $studentId = DB::table('students')->insertGetId([
            'student_id' => 'TEST-QUARTERLY-001',
            'first_name' => 'Test',
            'last_name' => 'Quarterly Payment',
            'email' => 'testquarterly001@test.com',
            'date_of_birth' => '2005-01-01',
            'address' => 'Test Address',
            'contact_number' => '09123456789',
            'gender' => 'Male',
            'guardian_name' => 'Test Guardian',
            'guardian_contact' => '09123456789',
            'grade_level' => 'Grade 11',
            'strand' => 'STEM',
            'enrollment_status' => 'pre_registered',
            'is_paid' => false,
            'total_fees_due' => 21500,
            'academic_year' => '2025-2026',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insert quarterly payments
        $quarterlyAmount = 21500 / 4;
        $quarters = ['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'];
        $baseDate = now();

        // Create quarterly payments with mixed due dates
        $dueDates = [
            now()->subDays(10)->format('Y-m-d'), // 1st Quarter - 10 days overdue (Critical)
            now()->subDays(5)->format('Y-m-d'),  // 2nd Quarter - 5 days overdue (High)
            now()->subDays(1)->format('Y-m-d'),  // 3rd Quarter - 1 day overdue (Medium)
            now()->addDays(7)->format('Y-m-d')   // 4th Quarter - Due in 7 days (Scheduled)
        ];

        foreach ($quarters as $index => $quarter) {
            DB::table('payments')->insert([
                'transaction_id' => 'TXN-TEST-QUARTERLY-' . ($index + 1),
                'payable_type' => 'App\\Models\\Student',
                'payable_id' => $studentId,
                'amount' => $quarterlyAmount,
                'scheduled_date' => $dueDates[$index],
                'period_name' => $quarter,
                'payment_method' => 'quarterly',
                'status' => 'pending',
                'confirmation_status' => 'pending',
                'notes' => 'Test payment schedule',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->line('✅ Created TEST-QUARTERLY-001 with 4 payments (₱5,375 each)');
    }

    private function createMonthlyPaymentStudent()
    {
        $this->info('=== Creating Monthly Payment Test Student ===');

        // Insert student
        $studentId = DB::table('students')->insertGetId([
            'student_id' => 'TEST-MONTHLY-001',
            'first_name' => 'Test',
            'last_name' => 'Monthly Payment',
            'email' => 'testmonthly001@test.com',
            'date_of_birth' => '2005-01-01',
            'address' => 'Test Address',
            'contact_number' => '09123456789',
            'gender' => 'Male',
            'guardian_name' => 'Test Guardian',
            'guardian_contact' => '09123456789',
            'grade_level' => 'Grade 11',
            'strand' => 'STEM',
            'enrollment_status' => 'pre_registered',
            'is_paid' => false,
            'total_fees_due' => 21500,
            'academic_year' => '2025-2026',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insert monthly payments
        $monthlyAmount = 21500 / 10;
        $months = ['June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March'];
        $baseDate = now();

        // Create monthly payments with mixed due dates
        $monthlyDueDates = [
            now()->subDays(15)->format('Y-m-d'), // June - 15 days overdue (Critical)
            now()->subDays(8)->format('Y-m-d'),  // July - 8 days overdue (Critical)
            now()->subDays(4)->format('Y-m-d'),  // August - 4 days overdue (High)
            now()->subDays(2)->format('Y-m-d'),  // September - 2 days overdue (Medium)
            now()->format('Y-m-d'),              // October - Due today
            now()->addDays(3)->format('Y-m-d'),  // November - Due in 3 days (Scheduled)
            now()->addDays(10)->format('Y-m-d'), // December - Due in 10 days (Scheduled)
            now()->addDays(17)->format('Y-m-d'), // January - Due in 17 days (Scheduled)
            now()->addDays(24)->format('Y-m-d'), // February - Due in 24 days (Scheduled)
            now()->addDays(31)->format('Y-m-d')  // March - Due in 31 days (Scheduled)
        ];

        foreach ($months as $index => $month) {
            DB::table('payments')->insert([
                'transaction_id' => 'TXN-TEST-MONTHLY-' . ($index + 1),
                'payable_type' => 'App\\Models\\Student',
                'payable_id' => $studentId,
                'amount' => $monthlyAmount,
                'scheduled_date' => $monthlyDueDates[$index],
                'period_name' => $month,
                'payment_method' => 'monthly',
                'status' => 'pending',
                'confirmation_status' => 'pending',
                'notes' => 'Test payment schedule',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->line('✅ Created TEST-MONTHLY-001 with 10 payments (₱2,150 each)');
    }
}
