<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = date('Y');
        $academicYear = $currentYear . '-' . ($currentYear + 1);

        // Grades 1-3 Subjects (1st, 2nd, 3rd, 4th Grading)
        for ($grade = 1; $grade <= 3; $grade++) {
            $subjects = [
                ['subject_code' => "G{$grade}-MTB", 'subject_name' => 'Mother Tongue (MTB-MLE)'],
                ['subject_code' => "G{$grade}-FIL", 'subject_name' => 'Filipino'],
                ['subject_code' => "G{$grade}-ENG", 'subject_name' => 'English'],
                ['subject_code' => "G{$grade}-MATH", 'subject_name' => 'Mathematics'],
                ['subject_code' => "G{$grade}-AP", 'subject_name' => 'Araling Panlipunan (AP)'],
                ['subject_code' => "G{$grade}-ESP", 'subject_name' => 'Edukasyon sa Pagpapakatao (EsP / Values)'],
                ['subject_code' => "G{$grade}-MAPEH", 'subject_name' => 'Music, Arts, Physical Education & Health (MAPEH)'],
                ['subject_code' => "G{$grade}-SCI", 'subject_name' => 'Science'],
            ];

            foreach ($subjects as $subject) {
                Subject::create(array_merge($subject, [
                    'grade_level' => "Grade {$grade}",
                    'academic_year' => $academicYear
                ]));
            }
        }

        // Grades 4-6 Subjects (1st, 2nd, 3rd, 4th Grading)
        for ($grade = 4; $grade <= 6; $grade++) {
            $subjects = [
                ['subject_code' => "G{$grade}-FIL", 'subject_name' => 'Filipino'],
                ['subject_code' => "G{$grade}-ENG", 'subject_name' => 'English'],
                ['subject_code' => "G{$grade}-MATH", 'subject_name' => 'Mathematics'],
                ['subject_code' => "G{$grade}-SCI", 'subject_name' => 'Science'],
                ['subject_code' => "G{$grade}-AP", 'subject_name' => 'Araling Panlipunan (AP)'],
                ['subject_code' => "G{$grade}-ESP", 'subject_name' => 'Edukasyon sa Pagpapakatao (EsP / Values)'],
                ['subject_code' => "G{$grade}-MAPEH", 'subject_name' => 'MAPEH'],
                ['subject_code' => "G{$grade}-TLE", 'subject_name' => 'Technology & Livelihood Education / Edukasyong Pantahanan at Pangkabuhayan'],
            ];

            foreach ($subjects as $subject) {
                Subject::create(array_merge($subject, [
                    'grade_level' => "Grade {$grade}",
                    'academic_year' => $academicYear
                ]));
            }
        }

        // Grades 7-9 Junior High School (1st, 2nd, 3rd, 4th Grading)
        for ($grade = 7; $grade <= 9; $grade++) {
            $subjects = [
                ['subject_code' => "G{$grade}-FIL", 'subject_name' => 'Filipino'],
                ['subject_code' => "G{$grade}-ENG", 'subject_name' => 'English'],
                ['subject_code' => "G{$grade}-MATH", 'subject_name' => 'Mathematics'],
                ['subject_code' => "G{$grade}-SCI", 'subject_name' => 'Science'],
                ['subject_code' => "G{$grade}-AP", 'subject_name' => 'Araling Panlipunan (AP)'],
                ['subject_code' => "G{$grade}-TLE", 'subject_name' => 'Technology & Livelihood Education (TLE)'],
                ['subject_code' => "G{$grade}-VE", 'subject_name' => 'Values Education'],
            ];

            foreach ($subjects as $subject) {
                Subject::create(array_merge($subject, [
                    'grade_level' => "Grade {$grade}",
                    'academic_year' => $academicYear
                ]));
            }
        }

        // Grade 10 (1st, 2nd, 3rd, 4th Grading)
        $grade10Subjects = [
            ['subject_code' => 'G10-FIL', 'subject_name' => 'Filipino'],
            ['subject_code' => 'G10-ENG', 'subject_name' => 'English'],
            ['subject_code' => 'G10-MATH', 'subject_name' => 'Mathematics'],
            ['subject_code' => 'G10-SCI', 'subject_name' => 'Science'],
            ['subject_code' => 'G10-AP', 'subject_name' => 'Araling Panlipunan (AP)'],
            ['subject_code' => 'G10-ESP', 'subject_name' => 'Edukasyon sa Pagpapakatao (EsP)'],
            ['subject_code' => 'G10-TLE', 'subject_name' => 'Technology and Livelihood Education (TLE)'],
            ['subject_code' => 'G10-MAPEH', 'subject_name' => 'MAPEH'],
            ['subject_code' => 'G10-MUSIC', 'subject_name' => 'Music'],
            ['subject_code' => 'G10-ARTS', 'subject_name' => 'Arts'],
            ['subject_code' => 'G10-PE', 'subject_name' => 'Physical Education'],
            ['subject_code' => 'G10-HEALTH', 'subject_name' => 'Health'],
        ];

        foreach ($grade10Subjects as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 10',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 11 ABM First Semester (1st and 2nd Grading)
        $g11AbmFirstSem = [
            // Core Subjects
            ['subject_code' => 'G11-ABM-ORAL', 'subject_name' => 'Oral Communication'],
            ['subject_code' => 'G11-ABM-GENMATH', 'subject_name' => 'General Mathematics'],
            ['subject_code' => 'G11-ABM-EARTH', 'subject_name' => 'Earth and Life Science'],
            ['subject_code' => 'G11-ABM-KOMFIL', 'subject_name' => 'Komunikasiyon at Pananaliksik sa Wika'],
            ['subject_code' => 'G11-ABM-PERSDEV', 'subject_name' => 'Personal Development'],
            ['subject_code' => 'G11-ABM-UCSP', 'subject_name' => 'Understanding Culture, Society, and Politics'],
            ['subject_code' => 'G11-ABM-PE1', 'subject_name' => 'Physical Education and Health 1'],
            // Applied & Specialized Subjects
            ['subject_code' => 'G11-ABM-ORGMGT', 'subject_name' => 'Organization and Management'],
            ['subject_code' => 'G11-ABM-BUSMATH', 'subject_name' => 'Business Mathematics'],
        ];

        foreach ($g11AbmFirstSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 11',
                'strand' => 'ABM',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 11 ABM Second Semester (3rd and 4th Grading)
        $g11AbmSecondSem = [
            // Core Subjects
            ['subject_code' => 'G11-ABM-READ', 'subject_name' => 'Reading and Writing Skills'],
            ['subject_code' => 'G11-ABM-21LIT', 'subject_name' => '21st Century Literature from the Phil and the World'],
            ['subject_code' => 'G11-ABM-STAT', 'subject_name' => 'Statistics and Probability'],
            ['subject_code' => 'G11-ABM-PAGBASA', 'subject_name' => 'Pagbasa at Pagsusuri ng Iba\'t – ibang Teksto'],
            ['subject_code' => 'G11-ABM-PE2', 'subject_name' => 'Physical Education and Health 2'],
            // Applied & Specialized Subjects
            ['subject_code' => 'G11-ABM-RESEARCH1', 'subject_name' => 'Research in Daily Life 1 (Qualitative Research)'],
            ['subject_code' => 'G11-ABM-EMPTECH', 'subject_name' => 'Empowerment Technologies'],
            ['subject_code' => 'G11-ABM-MARKETING', 'subject_name' => 'Principles of Marketing'],
            ['subject_code' => 'G11-ABM-FABM1', 'subject_name' => 'FABM 1'],
        ];

        foreach ($g11AbmSecondSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 11',
                'strand' => 'ABM',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 12 ABM First Semester (1st and 2nd Grading)
        $g12AbmFirstSem = [
            ['subject_code' => 'G12-ABM-PHYS', 'subject_name' => 'Physical Science'],
            ['subject_code' => 'G12-ABM-PHILO', 'subject_name' => 'Intro to Philosophy of the Human Person'],
            ['subject_code' => 'G12-ABM-PE3', 'subject_name' => 'Physical Education and Health 3'],
            ['subject_code' => 'G12-ABM-EAPP', 'subject_name' => 'English for Academic and Professional Purposes'],
            ['subject_code' => 'G12-ABM-RESEARCH2', 'subject_name' => 'Research in Daily Life 2 (Quantitative Research)'],
            ['subject_code' => 'G12-ABM-FILPIL', 'subject_name' => 'Filipino sa Piling Larangan'],
            ['subject_code' => 'G12-ABM-ENTREP', 'subject_name' => 'Entrepreneurship'],
            ['subject_code' => 'G12-ABM-FABM2', 'subject_name' => 'FABM 2'],
            ['subject_code' => 'G12-ABM-BUSFIN', 'subject_name' => 'Business Finance'],
        ];

        foreach ($g12AbmFirstSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 12',
                'strand' => 'ABM',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 12 ABM Second Semester (3rd and 4th Grading)
        $g12AbmSecondSem = [
            ['subject_code' => 'G12-ABM-CPAR', 'subject_name' => 'Contemporary Philippine Arts from the Region'],
            ['subject_code' => 'G12-ABM-MIL', 'subject_name' => 'Media and Information Literacy'],
            ['subject_code' => 'G12-ABM-PE4', 'subject_name' => 'Physical Education and Health 4'],
            ['subject_code' => 'G12-ABM-RESEARCH3', 'subject_name' => 'Research Project (3Is)'],
            ['subject_code' => 'G12-ABM-APPECON', 'subject_name' => 'Applied Economics'],
            ['subject_code' => 'G12-ABM-BUSETH', 'subject_name' => 'Business Ethics and Social Responsibility'],
            ['subject_code' => 'G12-ABM-BUSENT', 'subject_name' => 'Business Enterprise and Simulation'],
        ];

        foreach ($g12AbmSecondSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 12',
                'strand' => 'ABM',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 11 HUMSS First Semester (1st and 2nd Grading)
        $g11HumssFirstSem = [
            ['subject_code' => 'G11-HUMSS-ORAL', 'subject_name' => 'Oral Communication'],
            ['subject_code' => 'G11-HUMSS-GENMATH', 'subject_name' => 'General Mathematics'],
            ['subject_code' => 'G11-HUMSS-EARTH', 'subject_name' => 'Earth and Life Science'],
            ['subject_code' => 'G11-HUMSS-KOMFIL', 'subject_name' => 'Komunikasiyon at Pananaliksik sa Wika'],
            ['subject_code' => 'G11-HUMSS-PERSDEV', 'subject_name' => 'Personal Development'],
            ['subject_code' => 'G11-HUMSS-UCSP', 'subject_name' => 'Understanding Culture, Society, and Politics'],
            ['subject_code' => 'G11-HUMSS-PE1', 'subject_name' => 'Physical Education and Health 1'],
            ['subject_code' => 'G11-HUMSS-WORLD', 'subject_name' => 'Introduction to World Religions and Belief Systems'],
            ['subject_code' => 'G11-HUMSS-DISS', 'subject_name' => 'Disciplines and Ideas in the Social Sciences'],
        ];

        foreach ($g11HumssFirstSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 11',
                'strand' => 'HUMSS',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 11 HUMSS Second Semester (3rd and 4th Grading)
        $g11HumssSecondSem = [
            ['subject_code' => 'G11-HUMSS-READ', 'subject_name' => 'Reading and Writing Skills'],
            ['subject_code' => 'G11-HUMSS-21LIT', 'subject_name' => '21st Century Literature from the Phil and the World'],
            ['subject_code' => 'G11-HUMSS-STAT', 'subject_name' => 'Statistics and Probability'],
            ['subject_code' => 'G11-HUMSS-PAGBASA', 'subject_name' => 'Pagbasa at Pagsusuri ng Iba\'t – ibang Teksto'],
            ['subject_code' => 'G11-HUMSS-PE2', 'subject_name' => 'Physical Education and Health 2'],
            ['subject_code' => 'G11-HUMSS-RESEARCH1', 'subject_name' => 'Research in Daily Life 1 (Qualitative Research)'],
            ['subject_code' => 'G11-HUMSS-EMPTECH', 'subject_name' => 'Empowerment Technologies'],
            ['subject_code' => 'G11-HUMSS-PHILPOL', 'subject_name' => 'Philippine Politics and Governance'],
            ['subject_code' => 'G11-HUMSS-DIASS', 'subject_name' => 'Discipline and Ideas in the Applied Social Sciences'],
        ];

        foreach ($g11HumssSecondSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 11',
                'strand' => 'HUMSS',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 12 HUMSS First Semester (1st and 2nd Grading)
        $g12HumssFirstSem = [
            ['subject_code' => 'G12-HUMSS-PHYS', 'subject_name' => 'Physical Science'],
            ['subject_code' => 'G12-HUMSS-PHILO', 'subject_name' => 'Intro to Philosophy of the Human Person'],
            ['subject_code' => 'G12-HUMSS-PE3', 'subject_name' => 'Physical Education and Health 3'],
            ['subject_code' => 'G12-HUMSS-EAPP', 'subject_name' => 'English for Academic and Professional Purposes'],
            ['subject_code' => 'G12-HUMSS-RESEARCH2', 'subject_name' => 'Research in Daily Life 2 (Quantitative Research)'],
            ['subject_code' => 'G12-HUMSS-FILPIL', 'subject_name' => 'Filipino sa Piling Larangan'],
            ['subject_code' => 'G12-HUMSS-ENTREP', 'subject_name' => 'Entrepreneurship'],
            ['subject_code' => 'G12-HUMSS-CREWRIT', 'subject_name' => 'Creative Writing (Fiction)'],
            ['subject_code' => 'G12-HUMSS-CESC', 'subject_name' => 'Community Engagement, Solidarity and Citizenship'],
        ];

        foreach ($g12HumssFirstSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 12',
                'strand' => 'HUMSS',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 12 HUMSS Second Semester (3rd and 4th Grading)
        $g12HumssSecondSem = [
            ['subject_code' => 'G12-HUMSS-CPAR', 'subject_name' => 'Contemporary Philippine Arts from the Region'],
            ['subject_code' => 'G12-HUMSS-MIL', 'subject_name' => 'Media and Information Literacy'],
            ['subject_code' => 'G12-HUMSS-PE4', 'subject_name' => 'Physical Education and Health 4'],
            ['subject_code' => 'G12-HUMSS-RESEARCH3', 'subject_name' => 'Research Project (3Is)'],
            ['subject_code' => 'G12-HUMSS-CREWRIT2', 'subject_name' => 'Creative Writing (Non-Fiction)'],
            ['subject_code' => 'G12-HUMSS-TRENDS', 'subject_name' => 'Trends and Networks, and Critical Thinking in the 21st Century'],
            ['subject_code' => 'G12-HUMSS-CULM', 'subject_name' => 'Culminating Activity'],
        ];

        foreach ($g12HumssSecondSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 12',
                'strand' => 'HUMSS',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 11 STEM First Semester (1st and 2nd Grading)
        $g11StemFirstSem = [
            ['subject_code' => 'G11-STEM-ORAL', 'subject_name' => 'Oral Communication'],
            ['subject_code' => 'G11-STEM-GENMATH', 'subject_name' => 'General Mathematics'],
            ['subject_code' => 'G11-STEM-EARTH', 'subject_name' => 'Earth and Life Science'],
            ['subject_code' => 'G11-STEM-KOMFIL', 'subject_name' => 'Komunikasiyon at Pananaliksik sa Wika'],
            ['subject_code' => 'G11-STEM-PERSDEV', 'subject_name' => 'Personal Development'],
            ['subject_code' => 'G11-STEM-UCSP', 'subject_name' => 'Understanding Culture, Society, and Politics'],
            ['subject_code' => 'G11-STEM-PE1', 'subject_name' => 'Physical Education and Health 1'],
            ['subject_code' => 'G11-STEM-PRECAL', 'subject_name' => 'Pre – Calculus'],
            ['subject_code' => 'G11-STEM-CHEM1', 'subject_name' => 'General Chemistry 1'],
        ];

        foreach ($g11StemFirstSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 11',
                'strand' => 'STEM',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 11 STEM Second Semester (3rd and 4th Grading)
        $g11StemSecondSem = [
            ['subject_code' => 'G11-STEM-READ', 'subject_name' => 'Reading and Writing Skills'],
            ['subject_code' => 'G11-STEM-21LIT', 'subject_name' => '21st Century Literature from the Phil and the World'],
            ['subject_code' => 'G11-STEM-STAT', 'subject_name' => 'Statistics and Probability'],
            ['subject_code' => 'G11-STEM-PAGBASA', 'subject_name' => 'Pagbasa at Pagsusuri ng Iba\'t – ibang Teksto'],
            ['subject_code' => 'G11-STEM-PE2', 'subject_name' => 'Physical Education and Health 2'],
            ['subject_code' => 'G11-STEM-RESEARCH1', 'subject_name' => 'Research in Daily Life 1 (Qualitative Research)'],
            ['subject_code' => 'G11-STEM-EMPTECH', 'subject_name' => 'Empowerment Technologies'],
            ['subject_code' => 'G11-STEM-BASICCAL', 'subject_name' => 'Basic Calculus'],
            ['subject_code' => 'G11-STEM-CHEM2', 'subject_name' => 'General Chemistry 2'],
        ];

        foreach ($g11StemSecondSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 11',
                'strand' => 'STEM',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 12 STEM First Semester (1st and 2nd Grading)
        $g12StemFirstSem = [
            ['subject_code' => 'G12-STEM-DRRR', 'subject_name' => 'Disaster Readiness and Risk Reduction'],
            ['subject_code' => 'G12-STEM-PHILO', 'subject_name' => 'Intro to Philosophy of the Human Person'],
            ['subject_code' => 'G12-STEM-PE3', 'subject_name' => 'Physical Education and Health 3'],
            ['subject_code' => 'G12-STEM-EAPP', 'subject_name' => 'English for Academic and Professional Purposes'],
            ['subject_code' => 'G12-STEM-RESEARCH2', 'subject_name' => 'Research in Daily Life 2 (Quantitative Research)'],
            ['subject_code' => 'G12-STEM-FILPIL', 'subject_name' => 'Filipino sa Piling Larangan'],
            ['subject_code' => 'G12-STEM-ENTREP', 'subject_name' => 'Entrepreneurship'],
            ['subject_code' => 'G12-STEM-BIO1', 'subject_name' => 'General Biology 1'],
            ['subject_code' => 'G12-STEM-PHYS1', 'subject_name' => 'General Physics 1'],
        ];

        foreach ($g12StemFirstSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 12',
                'strand' => 'STEM',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 12 STEM Second Semester (3rd and 4th Grading)
        $g12StemSecondSem = [
            ['subject_code' => 'G12-STEM-CPAR', 'subject_name' => 'Contemporary Philippine Arts from the Region'],
            ['subject_code' => 'G12-STEM-MIL', 'subject_name' => 'Media and Information Literacy'],
            ['subject_code' => 'G12-STEM-PE4', 'subject_name' => 'Physical Education and Health 4'],
            ['subject_code' => 'G12-STEM-RESEARCH3', 'subject_name' => 'Research Project (3Is)'],
            ['subject_code' => 'G12-STEM-BIO2', 'subject_name' => 'General Biology 2'],
            ['subject_code' => 'G12-STEM-PHYS2', 'subject_name' => 'General Physics 2'],
            ['subject_code' => 'G12-STEM-CAPSTONE', 'subject_name' => 'Capstone Research Project'],
        ];

        foreach ($g12StemSecondSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 12',
                'strand' => 'STEM',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 11 TVL-HE First Semester (1st and 2nd Grading)
        $g11TvlHeFirstSem = [
            ['subject_code' => 'G11-HE-ORAL', 'subject_name' => 'Oral Communication'],
            ['subject_code' => 'G11-HE-GENMATH', 'subject_name' => 'General Mathematics'],
            ['subject_code' => 'G11-HE-EARTH', 'subject_name' => 'Earth and Life Science'],
            ['subject_code' => 'G11-HE-KOMFIL', 'subject_name' => 'Komunikasiyon at Pananaliksik sa Wika'],
            ['subject_code' => 'G11-HE-PERSDEV', 'subject_name' => 'Personal Development'],
            ['subject_code' => 'G11-HE-UCSP', 'subject_name' => 'Understanding Culture, Society, and Politics'],
            ['subject_code' => 'G11-HE-PE1', 'subject_name' => 'Physical Education and Health 1'],
            ['subject_code' => 'G11-HE-FBS', 'subject_name' => 'Food and Beverage Services (FBS) NC II'],
        ];

        foreach ($g11TvlHeFirstSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 11',
                'strand' => 'TVL',
                'track' => 'HE',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 11 TVL-HE Second Semester (3rd and 4th Grading)
        $g11TvlHeSecondSem = [
            ['subject_code' => 'G11-HE-READ', 'subject_name' => 'Reading and Writing Skills'],
            ['subject_code' => 'G11-HE-21LIT', 'subject_name' => '21st Century Literature from the Phil and the World'],
            ['subject_code' => 'G11-HE-STAT', 'subject_name' => 'Statistics and Probability'],
            ['subject_code' => 'G11-HE-PAGBASA', 'subject_name' => 'Pagbasa at Pagsusuri ng Iba\'t – ibang Teksto'],
            ['subject_code' => 'G11-HE-PE2', 'subject_name' => 'Physical Education and Health 2'],
            ['subject_code' => 'G11-HE-RESEARCH1', 'subject_name' => 'Research in Daily Life 1 (Qualitative Research)'],
            ['subject_code' => 'G11-HE-EMPTECH', 'subject_name' => 'Empowerment Technologies'],
            ['subject_code' => 'G11-HE-BREAD', 'subject_name' => 'Bread and Pastry Production NC II'],
        ];

        foreach ($g11TvlHeSecondSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 11',
                'strand' => 'TVL',
                'track' => 'HE',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 12 TVL-HE First Semester (1st and 2nd Grading)
        $g12TvlHeFirstSem = [
            ['subject_code' => 'G12-HE-PHYS', 'subject_name' => 'Physical Science'],
            ['subject_code' => 'G12-HE-PHILO', 'subject_name' => 'Intro to Philosophy of the Human Person'],
            ['subject_code' => 'G12-HE-PE3', 'subject_name' => 'Physical Education and Health 3'],
            ['subject_code' => 'G12-HE-EAPP', 'subject_name' => 'English for Academic and Professional Purposes'],
            ['subject_code' => 'G12-HE-RESEARCH2', 'subject_name' => 'Research in Daily Life 2 (Quantitative Research)'],
            ['subject_code' => 'G12-HE-FILPIL', 'subject_name' => 'Filipino sa Piling Larangan'],
            ['subject_code' => 'G12-HE-ENTREP', 'subject_name' => 'Entrepreneurship'],
            ['subject_code' => 'G12-HE-COOKERY1', 'subject_name' => 'Cookery NC II'],
        ];

        foreach ($g12TvlHeFirstSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 12',
                'strand' => 'TVL',
                'track' => 'HE',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 12 TVL-HE Second Semester (3rd and 4th Grading)
        $g12TvlHeSecondSem = [
            ['subject_code' => 'G12-HE-CPAR', 'subject_name' => 'Contemporary Philippine Arts from the Region'],
            ['subject_code' => 'G12-HE-MIL', 'subject_name' => 'Media and Information Literacy'],
            ['subject_code' => 'G12-HE-PE4', 'subject_name' => 'Physical Education and Health 4'],
            ['subject_code' => 'G12-HE-RESEARCH3', 'subject_name' => 'Research Project (3Is)'],
            ['subject_code' => 'G12-HE-COOKERY2', 'subject_name' => 'Cookery NC II'],
            ['subject_code' => 'G12-HE-WORKIMM', 'subject_name' => 'Work Immersion'],
        ];

        foreach ($g12TvlHeSecondSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 12',
                'strand' => 'TVL',
                'track' => 'HE',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 11 TVL-ICT First Semester (1st and 2nd Grading)
        $g11TvlIctFirstSem = [
            ['subject_code' => 'G11-ICT-ORAL', 'subject_name' => 'Oral Communication'],
            ['subject_code' => 'G11-ICT-GENMATH', 'subject_name' => 'General Mathematics'],
            ['subject_code' => 'G11-ICT-EARTH', 'subject_name' => 'Earth and Life Science'],
            ['subject_code' => 'G11-ICT-KOMFIL', 'subject_name' => 'Komunikasiyon at Pananaliksik sa Wika'],
            ['subject_code' => 'G11-ICT-PERSDEV', 'subject_name' => 'Personal Development'],
            ['subject_code' => 'G11-ICT-UCSP', 'subject_name' => 'Understanding Culture, Society, and Politics'],
            ['subject_code' => 'G11-ICT-PE1', 'subject_name' => 'Physical Education and Health 1'],
            ['subject_code' => 'G11-ICT-CSS1', 'subject_name' => 'Computer Systems Servicing NC II'],
        ];

        foreach ($g11TvlIctFirstSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 11',
                'strand' => 'TVL',
                'track' => 'ICT',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 11 TVL-ICT Second Semester (3rd and 4th Grading)
        $g11TvlIctSecondSem = [
            ['subject_code' => 'G11-ICT-READ', 'subject_name' => 'Reading and Writing Skills'],
            ['subject_code' => 'G11-ICT-21LIT', 'subject_name' => '21st Century Literature from the Phil and the World'],
            ['subject_code' => 'G11-ICT-STAT', 'subject_name' => 'Statistics and Probability'],
            ['subject_code' => 'G11-ICT-PAGBASA', 'subject_name' => 'Pagbasa at Pagsusuri ng Iba\'t – ibang Teksto'],
            ['subject_code' => 'G11-ICT-PE2', 'subject_name' => 'Physical Education and Health 2'],
            ['subject_code' => 'G11-ICT-RESEARCH1', 'subject_name' => 'Research in Daily Life 1 (Qualitative Research)'],
            ['subject_code' => 'G11-ICT-EMPTECH', 'subject_name' => 'Empowerment Technologies'],
            ['subject_code' => 'G11-ICT-CSS2', 'subject_name' => 'Computer Systems Servicing NC II'],
        ];

        foreach ($g11TvlIctSecondSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 11',
                'strand' => 'TVL',
                'track' => 'ICT',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 12 TVL-ICT First Semester (1st and 2nd Grading)
        $g12TvlIctFirstSem = [
            ['subject_code' => 'G12-ICT-PHYS', 'subject_name' => 'Physical Science'],
            ['subject_code' => 'G12-ICT-PHILO', 'subject_name' => 'Intro to Philosophy of the Human Person'],
            ['subject_code' => 'G12-ICT-PE3', 'subject_name' => 'Physical Education and Health 3'],
            ['subject_code' => 'G12-ICT-EAPP', 'subject_name' => 'English for Academic and Professional Purposes'],
            ['subject_code' => 'G12-ICT-RESEARCH2', 'subject_name' => 'Research in Daily Life 2 (Quantitative Research)'],
            ['subject_code' => 'G12-ICT-FILPIL', 'subject_name' => 'Filipino sa Piling Larangan'],
            ['subject_code' => 'G12-ICT-ENTREP', 'subject_name' => 'Entrepreneurship'],
            ['subject_code' => 'G12-ICT-CSS3', 'subject_name' => 'Computer Systems Servicing (CSS) NC II'],
        ];

        foreach ($g12TvlIctFirstSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 12',
                'strand' => 'TVL',
                'track' => 'ICT',
                'academic_year' => $academicYear
            ]));
        }

        // Grade 12 TVL-ICT Second Semester (3rd and 4th Grading)
        $g12TvlIctSecondSem = [
            ['subject_code' => 'G12-ICT-CPAR', 'subject_name' => 'Contemporary Philippine Arts from the Region'],
            ['subject_code' => 'G12-ICT-MIL', 'subject_name' => 'Media and Information Literacy'],
            ['subject_code' => 'G12-ICT-PE4', 'subject_name' => 'Physical Education and Health 4'],
            ['subject_code' => 'G12-ICT-RESEARCH3', 'subject_name' => 'Research Project (3Is)'],
            ['subject_code' => 'G12-ICT-CSS4', 'subject_name' => 'Computer Systems Servicing (CSS) NC II'],
            ['subject_code' => 'G12-ICT-WORKIMM', 'subject_name' => 'Work Immersion'],
        ];

        foreach ($g12TvlIctSecondSem as $subject) {
            Subject::create(array_merge($subject, [
                'grade_level' => 'Grade 12',
                'strand' => 'TVL',
                'track' => 'ICT',
                'academic_year' => $academicYear
            ]));
        }
    }
}
