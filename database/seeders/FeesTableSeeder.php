<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Fee;

class FeesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYear = date('Y') . '-' . (date('Y') + 1);

        $fees = [
            // PRESCHOOL FEES (Nursery, Junior Casa, Senior Casa)
            [
                'name' => 'Entrance Fee',
                'description' => 'First payment / Upon Enrollment Payment',
                'amount' => 4500.00,
                'academic_year' => $academicYear,
                'applicable_grades' => ['Nursery', 'Junior Casa', 'Senior Casa'],
                'educational_level' => 'preschool',
                'fee_category' => 'entrance',
                'payment_schedule' => 'full_payment',
                'is_required' => true,
                'payment_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Miscellaneous Fee',
                'description' => 'Books, Notebooks & School Activities etc',
                'amount' => 7000.00,
                'academic_year' => $academicYear,
                'applicable_grades' => ['Nursery', 'Junior Casa', 'Senior Casa'],
                'educational_level' => 'preschool',
                'fee_category' => 'miscellaneous',
                'payment_schedule' => 'pay_separate',
                'is_required' => true,
                'payment_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Tuition Fee',
                'description' => 'Standard tuition fee',
                'amount' => 10000.00,
                'academic_year' => $academicYear,
                'applicable_grades' => ['Nursery', 'Junior Casa', 'Senior Casa'],
                'educational_level' => 'preschool',
                'fee_category' => 'tuition',
                'payment_schedule' => 'pay_before_exam',
                'is_required' => true,
                'payment_order' => 3,
                'is_active' => true,
            ],

            // ELEMENTARY FEES (Grade 1-6)
            [
                'name' => 'Entrance Fee',
                'description' => 'First payment / Upon Enrollment Payment',
                'amount' => 4500.00,
                'academic_year' => $academicYear,
                'applicable_grades' => ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
                'educational_level' => 'elementary',
                'fee_category' => 'entrance',
                'payment_schedule' => 'full_payment',
                'is_required' => true,
                'payment_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Miscellaneous Fee',
                'description' => 'Books, Notebooks & School Activities etc',
                'amount' => 7000.00,
                'academic_year' => $academicYear,
                'applicable_grades' => ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
                'educational_level' => 'elementary',
                'fee_category' => 'miscellaneous',
                'payment_schedule' => 'pay_separate',
                'is_required' => true,
                'payment_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Tuition Fee',
                'description' => 'Standard tuition fee.',
                'amount' => 10000.00,
                'academic_year' => $academicYear,
                'applicable_grades' => ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
                'educational_level' => 'elementary',
                'fee_category' => 'tuition',
                'payment_schedule' => 'pay_before_exam',
                'is_required' => true,
                'payment_order' => 3,
                'is_active' => true,
            ],

            // JUNIOR HIGH FEES (Grade 7-10)
            [
                'name' => 'Entrance Fee',
                'description' => 'First payment / Upon Enrollment Payment',
                'amount' => 4500.00,
                'academic_year' => $academicYear,
                'applicable_grades' => ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'],
                'educational_level' => 'junior_high',
                'fee_category' => 'entrance',
                'payment_schedule' => 'full_payment',
                'is_required' => true,
                'payment_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Miscellaneous Fee',
                'description' => 'Books, Notebooks & School Activities etc',
                'amount' => 7000.00,
                'academic_year' => $academicYear,
                'applicable_grades' => ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'],
                'educational_level' => 'junior_high',
                'fee_category' => 'miscellaneous',
                'payment_schedule' => 'pay_separate',
                'is_required' => true,
                'payment_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Tuition Fee',
                'description' => 'Standard tuition fee.',
                'amount' => 10000.00,
                'academic_year' => $academicYear,
                'applicable_grades' => ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'],
                'educational_level' => 'junior_high',
                'fee_category' => 'tuition',
                'payment_schedule' => 'pay_before_exam',
                'is_required' => true,
                'payment_order' => 3,
                'is_active' => true,
            ],

            // SENIOR HIGH FEES (Grade 11-12) - Different fee structure
            [
                'name' => 'Entrance Fee',
                'description' => 'First payment / Upon Enrollment Payment',
                'amount' => 4500.00,
                'academic_year' => $academicYear,
                'applicable_grades' => ['Grade 11', 'Grade 12'],
                'educational_level' => 'senior_high',
                'fee_category' => 'entrance',
                'payment_schedule' => 'full_payment',
                'is_required' => true,
                'payment_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Miscellaneous Fee',
                'description' => 'Books, Notebooks & School Activities etc.',
                'amount' => 7000.00,
                'academic_year' => $academicYear,
                'applicable_grades' => ['Grade 11', 'Grade 12'],
                'educational_level' => 'senior_high',
                'fee_category' => 'miscellaneous',
                'payment_schedule' => 'pay_separate',
                'is_required' => true,
                'payment_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Tuition Fee',
                'description' => 'Standard tuition fee.',
                'amount' => 10000.00,
                'academic_year' => $academicYear,
                'applicable_grades' => ['Grade 11', 'Grade 12'],
                'educational_level' => 'senior_high',
                'fee_category' => 'tuition',
                'payment_schedule' => 'pay_before_exam',
                'is_required' => true,
                'payment_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($fees as $fee) {
            Fee::create($fee);
        }
    }
}
