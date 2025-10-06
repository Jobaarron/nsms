<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ViolationListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $violations = [
            // Minor offenses
            ['title' => 'Not wearing of prescribed uniform and Improper wearing of school ID', 'severity' => 'minor', 'category' => null],
            ['title' => 'Unauthorized use of cellphones and other electronic gadgets inside the classroom', 'severity' => 'minor', 'category' => null],
            ['title' => 'Wearing earrings (for male students) and multiple earrings (for female students)', 'severity' => 'minor', 'category' => null],
            ['title' => 'Not sporting the prescribed haircut', 'severity' => 'minor', 'category' => null],
            ['title' => 'Unauthorized use of electronic gadgets inside the classroom', 'severity' => 'minor', 'category' => null],
            ['title' => 'Loitering inside the school', 'severity' => 'minor', 'category' => null],

            // Major Category 1
            ['title' => 'Borrowing, lending, and tampering of school ID', 'severity' => 'major', 'category' => '1'],
            ['title' => 'Disrespect to school logo', 'severity' => 'major', 'category' => '1'],
            ['title' => 'Unauthorized use of school forms', 'severity' => 'major', 'category' => '1'],
            ['title' => 'Loitering inside the campus', 'severity' => 'major', 'category' => '1'],
            ['title' => 'Littering inside the campus', 'severity' => 'major', 'category' => '1'],
            ['title' => 'Eating outside the classroom during class hours', 'severity' => 'major', 'category' => '1'],
            ['title' => 'Non-observance of Clean As You Go policy', 'severity' => 'major', 'category' => '1'],
            ['title' => 'Using profane and indecent language', 'severity' => 'major', 'category' => '1'],
            ['title' => 'Bringing pornographic materials and browsing pornographic sites', 'severity' => 'major', 'category' => '1'],
            ['title' => 'Smoking, e-cigarettes and similar acts', 'severity' => 'major', 'category' => '1'],
            ['title' => 'Participating in any form of gambling', 'severity' => 'major', 'category' => '1'],
            ['title' => 'Threatening fellow students', 'severity' => 'major', 'category' => '1'],
            ['title' => 'Leaving the school without a valid gate pass', 'severity' => 'major', 'category' => '1'],
            ['title' => 'Making an alarming fake bomb or fire threat or joke', 'severity' => 'major', 'category' => '1'],
            ['title' => 'Any offense analogous to the above', 'severity' => 'major', 'category' => '1'],

            // Major Category 2
            ['title' => 'Disrespecting the Philippine flag and other national / institutional symbols', 'severity' => 'major', 'category' => '2'],
            ['title' => 'Vandalism inside the campus', 'severity' => 'major', 'category' => '2'],
            ['title' => 'Engaging in immodest act such as public display of affection', 'severity' => 'major', 'category' => '2'],
            ['title' => 'Bringing intoxicating drinks or alcoholic beverages', 'severity' => 'major', 'category' => '2'],
            ['title' => 'Cheating during examination / acting as accomplice', 'severity' => 'major', 'category' => '2'],
            ['title' => 'Tampering with test scores', 'severity' => 'major', 'category' => '2'],
            ['title' => 'Cutting classes', 'severity' => 'major', 'category' => '2'],
            ['title' => 'Gross scandalous behavior inside/outside the campus', 'severity' => 'major', 'category' => '2'],
            ['title' => 'Act that malign the good name and reputation of the school', 'severity' => 'major', 'category' => '2'],
            ['title' => 'Withholding information during formal investigation', 'severity' => 'major', 'category' => '2'],
            ['title' => 'Habitual disregard to school policies', 'severity' => 'major', 'category' => '2'],
            ['title' => 'Any offense analogous to the above', 'severity' => 'major', 'category' => '2'],

            // Major Category 3
            ['title' => 'Bullying including physical, emotional and cyberbullying', 'severity' => 'major', 'category' => '3'],
            ['title' => 'Forging the signature of parents/guardian in school documents', 'severity' => 'major', 'category' => '3'],
            ['title' => 'Forging the signature of teachers or persons in authority', 'severity' => 'major', 'category' => '3'],
            ['title' => 'Assaulting or showing disrespect to teachers or persons in authority', 'severity' => 'major', 'category' => '3'],
            ['title' => 'Disrespectful or abusive behavior towards any faculty member', 'severity' => 'major', 'category' => '3'],
            ['title' => 'Possession, pushing, use of dangerous drugs, deadly weapons or explosives', 'severity' => 'major', 'category' => '3'],
            ['title' => 'Recruiting or engaging in pseudo fraternities / gangs', 'severity' => 'major', 'category' => '3'],
            ['title' => 'Engaging in fight and assaulting fellow students', 'severity' => 'major', 'category' => '3'],
            ['title' => 'Hazing, extortion and engaging in pre-marital sex', 'severity' => 'major', 'category' => '3'],
            ['title' => 'Deception of school authorities', 'severity' => 'major', 'category' => '3'],
            ['title' => 'Stealing school or others\' personal property', 'severity' => 'major', 'category' => '3'],
            ['title' => 'Any offense analogous to the above', 'severity' => 'major', 'category' => '3'],
        ];

        foreach ($violations as $violation) {
            \DB::table('violation_lists')->insert($violation);
        }
    }
}
