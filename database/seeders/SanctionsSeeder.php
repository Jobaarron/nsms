<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SanctionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sanctions = [
            // Minor Offenses
            [
                'severity' => 'minor',
                'category' => null,
                'major_category' => null,
                'offense_number' => 1,
                'sanction' => 'Verbal reprimand / warning',
                'deportment_grade_action' => 'No change',
                'suspension' => 'None',
                'notes' => 'First offense for minor violations.',
                'is_automatic' => true,
                'is_approved' => true,
            ],
            [
                'severity' => 'minor',
                'category' => null,
                'major_category' => null,
                'offense_number' => 2,
                'sanction' => 'Written warning',
                'deportment_grade_action' => 'No change',
                'suspension' => 'None',
                'notes' => 'Second offense for minor violations.',
                'is_automatic' => true,
                'is_approved' => true,
            ],
            [
                'severity' => 'minor',
                'category' => null,
                'major_category' => null,
                'offense_number' => 3,
                'sanction' => 'One step lower in the Deportment Grade',
                'deportment_grade_action' => 'Lowered by one step',
                'suspension' => 'None',
                'notes' => 'Third offense for minor violations. Grades are lowered particularly in Character/Value subjects.',
                'is_automatic' => true,
                'is_approved' => true,
            ],

            // Major Offenses (applies to all categories 1, 2, 3)
            [
                'severity' => 'major',
                'category' => null,
                'major_category' => '1',
                'offense_number' => 1,
                'sanction' => 'One step lower in the Deportment Grade, Community Service',
                'deportment_grade_action' => 'Lowered by one step',
                'suspension' => 'None',
                'notes' => 'First offense for major violations. Community Service (3-5 days depending on gravity of the case). Grades are lowered particularly in Character/Value subjects.',
                'is_automatic' => true,
                'is_approved' => true,
            ],
            [
                'severity' => 'major',
                'category' => null,
                'major_category' => '1',
                'offense_number' => 2,
                'sanction' => 'Needs Improvement in Deportment, 3-5 days suspension, Community Service',
                'deportment_grade_action' => 'Needs Improvement (NI)',
                'suspension' => '3-5 days',
                'notes' => 'Second offense for major violations. NI means no grade is placed on the card, marked as Needs Improvement.',
                'is_automatic' => true,
                'is_approved' => true,
            ],
            [
                'severity' => 'major',
                'category' => null,
                'major_category' => '1',
                'offense_number' => 3,
                'sanction' => 'Needs Improvement in Deportment, Dismissal or Expulsion',
                'deportment_grade_action' => 'Needs Improvement (NI)',
                'suspension' => 'Dismissal/Expulsion',
                'notes' => 'Third offense for major violations. NI means no grade is placed on the card, marked as Needs Improvement. Subject for transferring to other school or expulsion.',
                'is_automatic' => true,
                'is_approved' => true,
            ],
        ];

        foreach ($sanctions as $sanction) {
            \DB::table('sanctions')->insert($sanction);
        }
    }
}
