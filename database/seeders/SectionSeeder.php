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
            'Grade 1' => 50,
            'Grade 2' => 50,
            'Grade 3' => 50,
            'Grade 4' => 50,
            'Grade 5' => 50,
            'Grade 6' => 50,
            'Grade 7' => 50,
            'Grade 8' => 50,
            'Grade 9' => 50,
            'Grade 10' => 50,
            'Grade 11' => 50,
            'Grade 12' => 50,
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
