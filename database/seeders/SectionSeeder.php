<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Section;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = date('Y');
        $academicYear = $currentYear . '-' . ($currentYear + 1);

        // Define all grade levels in the school
        $gradeLevels = [
            'Nursery',
            'Junior Casa',
            'Senior Casa', 
            'Kinder',
            'Grade 1',
            'Grade 2',
            'Grade 3',
            'Grade 4',
            'Grade 5',
            'Grade 6',
            'Grade 7',
            'Grade 8',
            'Grade 9',
            'Grade 10',
            'Grade 11',
            'Grade 12'
        ];

        // Section names to create for each grade level
        $sectionNames = ['A', 'B', 'C'];

        // Different max students based on grade level
        $maxStudentsConfig = [
            'Nursery' => 20,
            'Junior Casa' => 20,
            'Senior Casa' => 25,
            'Kinder' => 25,
            'Grade 1' => 40,
            'Grade 2' => 40,
            'Grade 3' => 40,
            'Grade 4' => 40,
            'Grade 5' => 40,
            'Grade 6' => 40,
            'Grade 7' => 40,
            'Grade 8' => 40,
            'Grade 9' => 40,
            'Grade 10' => 40,
            'Grade 11' => 40,
            'Grade 12' => 40,
        ];

        // Create sections for each grade level
        foreach ($gradeLevels as $gradeLevel) {
            foreach ($sectionNames as $sectionName) {
                Section::create([
                    'section_name' => $sectionName,
                    'grade_level' => $gradeLevel,
                    'academic_year' => $academicYear,
                    'max_students' => $maxStudentsConfig[$gradeLevel] ?? 35,
                    'current_students' => 0,
                    'is_active' => true,
                    'description' => "Section {$sectionName} for {$gradeLevel} - Academic Year {$academicYear}"
                ]);
            }
        }
    }
}
