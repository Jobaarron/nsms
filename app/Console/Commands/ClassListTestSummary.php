<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;

class ClassListTestSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show summary of class list logic test results';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 Class List Logic Test Summary');
        $this->info('===============================');
        
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Count test students
        $testStudents = Student::where('student_id', 'LIKE', 'TEST-%')
                              ->where('academic_year', $currentAcademicYear)
                              ->count();
        
        if ($testStudents === 0) {
            $this->warn('No test data found. Run "php artisan test:setup-class-data" first.');
            return 0;
        }
        
        $this->info("✅ Test Data: {$testStudents} students created");
        
        // Test Results Summary
        $this->info("\n🎯 Test Results Summary:");
        $this->info("========================");
        
        // Academic Structure Coverage
        $elementary = Student::where('student_id', 'LIKE', 'TEST-%')
                            ->whereIn('grade_level', ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'])
                            ->count();
        
        $juniorHigh = Student::where('student_id', 'LIKE', 'TEST-%')
                            ->whereIn('grade_level', ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'])
                            ->count();
        
        $seniorHighNonTVL = Student::where('student_id', 'LIKE', 'TEST-%')
                                  ->whereIn('grade_level', ['Grade 11', 'Grade 12'])
                                  ->whereIn('strand', ['STEM', 'ABM', 'HUMSS'])
                                  ->count();
        
        $seniorHighTVL = Student::where('student_id', 'LIKE', 'TEST-%')
                               ->whereIn('grade_level', ['Grade 11', 'Grade 12'])
                               ->where('strand', 'TVL')
                               ->count();
        
        $this->table([
            'Academic Level',
            'Students',
            'Status'
        ], [
            ['Elementary (Grades 1-6)', $elementary, $elementary > 0 ? '✅ Covered' : '❌ Missing'],
            ['Junior High (Grades 7-10)', $juniorHigh, $juniorHigh > 0 ? '✅ Covered' : '❌ Missing'],
            ['Senior High Non-TVL', $seniorHighNonTVL, $seniorHighNonTVL > 0 ? '✅ Covered' : '❌ Missing'],
            ['Senior High TVL', $seniorHighTVL, $seniorHighTVL > 0 ? '✅ Covered' : '❌ Missing'],
        ]);
        
        // Section Distribution
        $this->info("\n📋 Section Distribution:");
        $this->info("========================");
        
        $sectionA = Student::where('student_id', 'LIKE', 'TEST-%')->where('section', 'A')->count();
        $sectionB = Student::where('student_id', 'LIKE', 'TEST-%')->where('section', 'B')->count();
        $sectionC = Student::where('student_id', 'LIKE', 'TEST-%')->where('section', 'C')->count();
        
        $this->table([
            'Section',
            'Students',
            'Status'
        ], [
            ['Section A', $sectionA, $sectionA > 0 ? '✅ Has Students' : '❌ Empty'],
            ['Section B', $sectionB, $sectionB > 0 ? '✅ Has Students' : '❌ Empty'],
            ['Section C', $sectionC, $sectionC > 0 ? '✅ Has Students' : '❌ Empty'],
        ]);
        
        // Strand Distribution
        $this->info("\n🎓 Strand Distribution:");
        $this->info("=======================");
        
        $stem = Student::where('student_id', 'LIKE', 'TEST-%')->where('strand', 'STEM')->count();
        $abm = Student::where('student_id', 'LIKE', 'TEST-%')->where('strand', 'ABM')->count();
        $humss = Student::where('student_id', 'LIKE', 'TEST-%')->where('strand', 'HUMSS')->count();
        $tvl = Student::where('student_id', 'LIKE', 'TEST-%')->where('strand', 'TVL')->count();
        
        $this->table([
            'Strand',
            'Students',
            'Status'
        ], [
            ['STEM', $stem, $stem > 0 ? '✅ Has Students' : '❌ Empty'],
            ['ABM', $abm, $abm > 0 ? '✅ Has Students' : '❌ Empty'],
            ['HUMSS', $humss, $humss > 0 ? '✅ Has Students' : '❌ Empty'],
            ['TVL', $tvl, $tvl > 0 ? '✅ Has Students' : '❌ Empty'],
        ]);
        
        // Track Distribution (TVL only)
        $this->info("\n🔧 Track Distribution (TVL):");
        $this->info("============================");
        
        $ict = Student::where('student_id', 'LIKE', 'TEST-%')->where('track', 'ICT')->count();
        $he = Student::where('student_id', 'LIKE', 'TEST-%')->where('track', 'H.E')->count();
        
        $this->table([
            'Track',
            'Students',
            'Status'
        ], [
            ['ICT', $ict, $ict > 0 ? '✅ Has Students' : '❌ Empty'],
            ['H.E', $he, $he > 0 ? '✅ Has Students' : '❌ Empty'],
        ]);
        
        // Class Format Examples
        $this->info("\n📝 Class Format Examples:");
        $this->info("=========================");
        
        $examples = Student::where('student_id', 'LIKE', 'TEST-%')
                          ->select('grade_level', 'section', 'strand', 'track')
                          ->distinct()
                          ->limit(10)
                          ->get();
        
        $exampleData = [];
        foreach ($examples as $example) {
            $classFormat = $example->grade_level . ' - ' . $example->section;
            if ($example->strand) {
                $classFormat .= ' - ' . $example->strand;
                if ($example->track) {
                    $classFormat .= ' - ' . $example->track;
                }
            }
            $exampleData[] = [$classFormat];
        }
        
        $this->table(['Class Format'], $exampleData);
        
        // Next Steps
        $this->info("\n🚀 Next Steps:");
        $this->info("==============");
        $this->info("1. Test Faculty Head modals in browser:");
        $this->info("   - Go to Faculty Head Dashboard");
        $this->info("   - Click section badges to open class list modals");
        $this->info("   - Verify modal titles show full class structure");
        $this->info("");
        $this->info("2. Test Registrar class lists:");
        $this->info("   - Go to Registrar > Class Lists");
        $this->info("   - Filter by different combinations");
        $this->info("   - Verify page headers show correct format");
        $this->info("");
        $this->info("3. Test Student views:");
        $this->info("   - Login as any test student (password: password123)");
        $this->info("   - Check Dashboard, Subjects, Schedule, Grades");
        $this->info("   - Verify class information displays correctly");
        $this->info("");
        $this->info("4. Clean up when done:");
        $this->info("   php artisan test:cleanup");
        
        return 0;
    }
}
