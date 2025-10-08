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
                'Mother Tongue (MTB-MLE)',
                'Filipino',
                'English',
                'Mathematics',
                'Araling Panlipunan (AP)',
                'Edukasyon sa Pagpapakatao (EsP / Values)',
                'Music, Arts, Physical Education & Health (MAPEH)',
                'Science',
            ];

            foreach ($subjects as $subjectName) {
                Subject::create([
                    'subject_name' => $subjectName,
                    'grade_level' => "Grade {$grade}",
                    'academic_year' => $academicYear,
                    'category' => 'core'
                ]);
            }
        }

        // Grades 4-6 Subjects (1st, 2nd, 3rd, 4th Grading)
        for ($grade = 4; $grade <= 6; $grade++) {
            $subjects = [
                'Filipino',
                'English',
                'Mathematics',
                'Science',
                'Araling Panlipunan (AP)',
                'Edukasyon sa Pagpapakatao (EsP / Values)',
                'MAPEH',
                'Technology & Livelihood Education / Edukasyong Pantahanan at Pangkabuhayan',
            ];

            foreach ($subjects as $subjectName) {
                Subject::create([
                    'subject_name' => $subjectName,
                    'grade_level' => "Grade {$grade}",
                    'academic_year' => $academicYear,
                    'category' => 'core'
                ]);
            }
        }

        // Grades 7-9 Junior High School (1st, 2nd, 3rd, 4th Grading)
        for ($grade = 7; $grade <= 9; $grade++) {
            $subjects = [
                'Filipino',
                'English',
                'Mathematics',
                'Science',
                'Araling Panlipunan (AP)',
                'Technology & Livelihood Education (TLE)',
                'Values Education',
            ];

            foreach ($subjects as $subjectName) {
                Subject::create([
                    'subject_name' => $subjectName,
                    'grade_level' => "Grade {$grade}",
                    'academic_year' => $academicYear,
                    'category' => 'core'
                ]);
            }
        }

        // Grade 10 (1st, 2nd, 3rd, 4th Grading)
        $grade10Subjects = [
            'Filipino',
            'English',
            'Mathematics',
            'Science',
            'Araling Panlipunan (AP)',
            'Edukasyon sa Pagpapakatao (EsP)',
            'Technology and Livelihood Education (TLE)',
            'MAPEH',
            'Music',
            'Arts',
            'Physical Education',
            'Health',
        ];

        foreach ($grade10Subjects as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 10',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 11 ABM First Semester (1st and 2nd Grading)
        // Core Subjects
        $g11AbmFirstSemCore = [
            'Oral Communication',
            'General Mathematics',
            'Earth and Life Science',
            'Komunikasiyon at Pananaliksik sa Wika',
            'Personal Development',
            'Understanding Culture, Society, and Politics',
            'Physical Education and Health 1',
        ];

        foreach ($g11AbmFirstSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'ABM',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Applied & Specialized Subjects
        $g11AbmFirstSemSpecialized = [
            'Organization and Management',
            'Business Mathematics',
        ];

        foreach ($g11AbmFirstSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'ABM',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 11 ABM Second Semester (3rd and 4th Grading)
        // Core Subjects
        $g11AbmSecondSemCore = [
            'Reading and Writing Skills',
            '21st Century Literature from the Phil and the World',
            'Statistics and Probability',
            'Pagbasa at Pagsusuri ng Iba\'t – ibang Teksto',
            'Physical Education and Health 2',
        ];

        foreach ($g11AbmSecondSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'ABM',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Applied & Specialized Subjects
        $g11AbmSecondSemSpecialized = [
            'Research in Daily Life 1 (Qualitative Research)',
            'Empowerment Technologies',
            'Principles of Marketing',
            'FABM 1',
        ];

        foreach ($g11AbmSecondSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'ABM',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 12 ABM First Semester (1st and 2nd Grading)
        // Core Subjects
        $g12AbmFirstSemCore = [
            'Physical Science',
            'Intro to Philosophy of the Human Person',
            'Physical Education and Health 3',
            'English for Academic and Professional Purposes',
            'Filipino sa Piling Larangan',
        ];

        foreach ($g12AbmFirstSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'ABM',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Specialized Subjects
        $g12AbmFirstSemSpecialized = [
            'Research in Daily Life 2 (Quantitative Research)',
            'Entrepreneurship',
            'FABM 2',
            'Business Finance',
        ];

        foreach ($g12AbmFirstSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'ABM',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 12 ABM Second Semester - Core Subjects
        $g12AbmSecondSemCore = [
            'Contemporary Philippine Arts from the Region',
            'Media and Information Literacy',
            'Physical Education and Health 4',
        ];

        foreach ($g12AbmSecondSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'ABM',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 12 ABM Second Semester - Specialized Subjects
        $g12AbmSecondSemSpecialized = [
            'Research Project (3Is)',
            'Applied Economics',
            'Business Ethics and Social Responsibility',
            'Business Enterprise and Simulation',
        ];

        foreach ($g12AbmSecondSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'ABM',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 11 HUMSS First Semester - Core Subjects
        $g11HumssFirstSemCore = [
            'Oral Communication',
            'General Mathematics',
            'Earth and Life Science',
            'Komunikasiyon at Pananaliksik sa Wika',
            'Personal Development',
            'Understanding Culture, Society, and Politics',
            'Physical Education and Health 1',
        ];

        foreach ($g11HumssFirstSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'HUMSS',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 11 HUMSS First Semester - Specialized Subjects
        $g11HumssFirstSemSpecialized = [
            'Introduction to World Religions and Belief Systems',
            'Disciplines and Ideas in the Social Sciences',
        ];

        foreach ($g11HumssFirstSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'HUMSS',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 11 HUMSS Second Semester - Core Subjects
        $g11HumssSecondSemCore = [
            'Reading and Writing Skills',
            '21st Century Literature from the Phil and the World',
            'Statistics and Probability',
            'Pagbasa at Pagsusuri ng Iba\'t – ibang Teksto',
            'Physical Education and Health 2',
        ];

        foreach ($g11HumssSecondSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'HUMSS',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 11 HUMSS Second Semester - Specialized Subjects
        $g11HumssSecondSemSpecialized = [
            'Research in Daily Life 1 (Qualitative Research)',
            'Empowerment Technologies',
            'Philippine Politics and Governance',
            'Discipline and Ideas in the Applied Social Sciences',
        ];

        foreach ($g11HumssSecondSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'HUMSS',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }


        // Grade 12 HUMSS First Semester - Core Subjects
        $g12HumssFirstSemCore = [
            'Physical Science',
            'Intro to Philosophy of the Human Person',
            'Physical Education and Health 3',
        ];

        foreach ($g12HumssFirstSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'HUMSS',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 12 HUMSS First Semester - Specialized Subjects
        $g12HumssFirstSemSpecialized = [
            'English for Academic and Professional Purposes',
            'Research in Daily Life 2 (Quantitative Research)',
            'Filipino sa Piling Larangan',
            'Entrepreneurship',
            'Creative Writing (Fiction)',
            'Community Engagement, Solidarity and Citizenship',
        ];

        foreach ($g12HumssFirstSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'HUMSS',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 12 HUMSS Second Semester - Core Subjects
        $g12HumssSecondSemCore = [
            'Contemporary Philippine Arts from the Region',
            'Media and Information Literacy',
            'Physical Education and Health 4',
        ];

        foreach ($g12HumssSecondSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'HUMSS',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 12 HUMSS Second Semester - Specialized Subjects
        $g12HumssSecondSemSpecialized = [
            'Research Project (3Is)',
            'Creative Writing (Non-Fiction)',
            'Trends and Networks, and Critical Thinking in the 21st Century',
            'Culminating Activity',
        ];

        foreach ($g12HumssSecondSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'HUMSS',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 11 STEM First Semester - Core Subjects
        $g11StemFirstSemCore = [
            'Oral Communication',
            'General Mathematics',
            'Earth and Life Science',
            'Komunikasiyon at Pananaliksik sa Wika',
            'Personal Development',
            'Understanding Culture, Society, and Politics',
            'Physical Education and Health 1',
        ];

        foreach ($g11StemFirstSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'STEM',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 11 STEM First Semester - Specialized Subjects
        $g11StemFirstSemSpecialized = [
            'Pre – Calculus',
            'General Chemistry 1',
        ];

        foreach ($g11StemFirstSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'STEM',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 11 STEM Second Semester - Core Subjects
        $g11StemSecondSemCore = [
            'Reading and Writing Skills',
            '21st Century Literature from the Phil and the World',
            'Statistics and Probability',
            'Pagbasa at Pagsusuri ng Iba\'t – ibang Teksto',
            'Physical Education and Health 2',
        ];

        foreach ($g11StemSecondSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'STEM',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 11 STEM Second Semester - Specialized Subjects
        $g11StemSecondSemSpecialized = [
            'Research in Daily Life 1 (Qualitative Research)',
            'Empowerment Technologies',
            'Basic Calculus',
            'General Chemistry 2',
        ];

        foreach ($g11StemSecondSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'STEM',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 12 STEM First Semester - Core Subjects
        $g12StemFirstSemCore = [
            'Disaster Readiness and Risk Reduction',
            'Intro to Philosophy of the Human Person',
            'Physical Education and Health 3',
        ];

        foreach ($g12StemFirstSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'STEM',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 12 STEM First Semester - Specialized Subjects
        $g12StemFirstSemSpecialized = [
            'English for Academic and Professional Purposes',
            'Research in Daily Life 2 (Quantitative Research)',
            'Filipino sa Piling Larangan',
            'Entrepreneurship',
            'General Biology 1',
            'General Physics 1',
        ];

        foreach ($g12StemFirstSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'STEM',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 12 STEM Second Semester - Core Subjects
        $g12StemSecondSemCore = [
            'Contemporary Philippine Arts from the Region',
            'Media and Information Literacy',
            'Physical Education and Health 4',
        ];

        foreach ($g12StemSecondSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'STEM',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 12 STEM Second Semester - Specialized Subjects
        $g12StemSecondSemSpecialized = [
            'Research Project (3Is)',
            'General Biology 2',
            'General Physics 2',
            'Capstone Research Project',
        ];

        foreach ($g12StemSecondSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'STEM',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 11 TVL-HE First Semester - Core Subjects
        $g11TvlHeFirstSemCore = [
            'Oral Communication',
            'General Mathematics',
            'Earth and Life Science',
            'Komunikasiyon at Pananaliksik sa Wika',
            'Personal Development',
            'Understanding Culture, Society, and Politics',
            'Physical Education and Health 1',
        ];

        foreach ($g11TvlHeFirstSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'TVL',
                'track' => 'HE',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 11 TVL-HE First Semester - Specialized Subjects
        $g11TvlHeFirstSemSpecialized = [
            'Food and Beverage Services (FBS) NC II',
        ];

        foreach ($g11TvlHeFirstSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'TVL',
                'track' => 'HE',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 11 TVL-HE Second Semester - Core Subjects
        $g11TvlHeSecondSemCore = [
            'Reading and Writing Skills',
            '21st Century Literature from the Phil and the World',
            'Statistics and Probability',
            'Pagbasa at Pagsusuri ng Iba\'t – ibang Teksto',
            'Physical Education and Health 2',
        ];

        foreach ($g11TvlHeSecondSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'TVL',
                'track' => 'HE',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 11 TVL-HE Second Semester - Specialized Subjects
        $g11TvlHeSecondSemSpecialized = [
            'Research in Daily Life 1 (Qualitative Research)',
            'Empowerment Technologies',
            'Bread and Pastry Production NC II',
        ];

        foreach ($g11TvlHeSecondSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'TVL',
                'track' => 'HE',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 12 TVL-HE First Semester - Core Subjects
        $g12TvlHeFirstSemCore = [
            'Physical Science',
            'Intro to Philosophy of the Human Person',
            'Physical Education and Health 3',
        ];

        foreach ($g12TvlHeFirstSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'TVL',
                'track' => 'HE',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 12 TVL-HE First Semester - Specialized Subjects
        $g12TvlHeFirstSemSpecialized = [
            'English for Academic and Professional Purposes',
            'Research in Daily Life 2 (Quantitative Research)',
            'Filipino sa Piling Larangan',
            'Entrepreneurship',
            'Cookery NC II',
        ];

        foreach ($g12TvlHeFirstSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'TVL',
                'track' => 'HE',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 12 TVL-HE Second Semester - Core Subjects
        $g12TvlHeSecondSemCore = [
            'Contemporary Philippine Arts from the Region',
            'Media and Information Literacy',
            'Physical Education and Health 4',
        ];

        foreach ($g12TvlHeSecondSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'TVL',
                'track' => 'HE',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 12 TVL-HE Second Semester - Specialized Subjects
        $g12TvlHeSecondSemSpecialized = [
            'Research Project (3Is)',
            'Cookery NC II',
            'Work Immersion',
        ];

        foreach ($g12TvlHeSecondSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'TVL',
                'track' => 'HE',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 11 TVL-ICT First Semester - Core Subjects
        $g11TvlIctFirstSemCore = [
            'Oral Communication',
            'General Mathematics',
            'Earth and Life Science',
            'Komunikasiyon at Pananaliksik sa Wika',
            'Personal Development',
            'Understanding Culture, Society, and Politics',
            'Physical Education and Health 1',
        ];

        foreach ($g11TvlIctFirstSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'TVL',
                'track' => 'ICT',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 11 TVL-ICT First Semester - Specialized Subjects
        $g11TvlIctFirstSemSpecialized = [
            'Computer Systems Servicing NC II',
        ];

        foreach ($g11TvlIctFirstSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'TVL',
                'track' => 'ICT',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 11 TVL-ICT Second Semester - Core Subjects
        $g11TvlIctSecondSemCore = [
            'Reading and Writing Skills',
            '21st Century Literature from the Phil and the World',
            'Statistics and Probability',
            'Pagbasa at Pagsusuri ng Iba\'t – ibang Teksto',
            'Physical Education and Health 2',
        ];

        foreach ($g11TvlIctSecondSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'TVL',
                'track' => 'ICT',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 11 TVL-ICT Second Semester - Specialized Subjects
        $g11TvlIctSecondSemSpecialized = [
            'Research in Daily Life 1 (Qualitative Research)',
            'Empowerment Technologies',
            'Computer Systems Servicing NC II',
        ];

        foreach ($g11TvlIctSecondSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 11',
                'strand' => 'TVL',
                'track' => 'ICT',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 12 TVL-ICT First Semester - Core Subjects
        $g12TvlIctFirstSemCore = [
            'Physical Science',
            'Intro to Philosophy of the Human Person',
            'Physical Education and Health 3',
        ];

        foreach ($g12TvlIctFirstSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'TVL',
                'track' => 'ICT',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 12 TVL-ICT First Semester - Specialized Subjects
        $g12TvlIctFirstSemSpecialized = [
            'English for Academic and Professional Purposes',
            'Research in Daily Life 2 (Quantitative Research)',
            'Filipino sa Piling Larangan',
            'Entrepreneurship',
            'Computer Systems Servicing (CSS) NC II',
        ];

        foreach ($g12TvlIctFirstSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'TVL',
                'track' => 'ICT',
                'semester' => 'First Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }

        // Grade 12 TVL-ICT Second Semester - Core Subjects
        $g12TvlIctSecondSemCore = [
            'Contemporary Philippine Arts from the Region',
            'Media and Information Literacy',
            'Physical Education and Health 4',
        ];

        foreach ($g12TvlIctSecondSemCore as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'TVL',
                'track' => 'ICT',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'core'
            ]);
        }

        // Grade 12 TVL-ICT Second Semester - Specialized Subjects
        $g12TvlIctSecondSemSpecialized = [
            'Research Project (3Is)',
            'Computer Systems Servicing (CSS) NC II',
            'Work Immersion',
        ];

        foreach ($g12TvlIctSecondSemSpecialized as $subjectName) {
            Subject::create([
                'subject_name' => $subjectName,
                'grade_level' => 'Grade 12',
                'strand' => 'TVL',
                'track' => 'ICT',
                'semester' => 'Second Semester',
                'academic_year' => $academicYear,
                'category' => 'specialized'
            ]);
        }
    }
}
